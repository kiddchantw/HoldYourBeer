# HoldYourBeer API 使用指南

> **版本**: v1.0 (Current Stable)
> **最後更新**: 2025-11-05
> **基礎 URL**: `https://your-domain.com/api/v1`

---

## 📖 目錄

1. [快速開始](#快速開始)
2. [認證機制](#認證機制)
3. [業務邏輯說明](#業務邏輯說明)
4. [完整使用範例](#完整使用範例)
5. [錯誤處理](#錯誤處理)
6. [最佳實踐](#最佳實踐)
7. [常見問題](#常見問題)

---

## 🚀 快速開始

### 基本流程

```
註冊用戶 → 登入獲取 Token → 查看品牌列表 → 添加啤酒 → 記錄品飲 → 查看統計
```

### 5分鐘快速上手

```bash
# 1. 註冊新用戶
curl -X POST https://your-domain.com/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response: 會獲得 token
# { "user": {...}, "token": "1|abc123..." }

# 2. 使用 token 添加啤酒
curl -X POST https://your-domain.com/api/v1/beers \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Guinness Draught",
    "brand_id": 1,
    "style": "Dry Stout"
  }'

# 3. 記錄品飲
curl -X POST https://your-domain.com/api/v1/beers/1/count_actions \
  -H "Authorization: Bearer 1|abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "action": "increment",
    "note": "Enjoyed at the pub!"
  }'
```

---

## 🔐 認證機制

### Bearer Token 認證

HoldYourBeer API 使用 Laravel Sanctum 的 Bearer Token 認證。

#### 獲取 Token

**方式一：註冊時獲取**
```javascript
const response = await fetch('/api/v1/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'John Doe',
    email: 'john@example.com',
    password: 'password123',
    password_confirmation: 'password123'
  })
});

const { user, token } = await response.json();
// 儲存 token 以供後續使用
localStorage.setItem('api_token', token);
```

**方式二：登入獲取**
```javascript
const response = await fetch('/api/v1/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'password123'
  })
});

const { user, token } = await response.json();
localStorage.setItem('api_token', token);
```

#### 使用 Token

所有需要認證的請求都必須在 `Authorization` header 中包含 token：

```javascript
const token = localStorage.getItem('api_token');

fetch('/api/v1/beers', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

#### 登出

```javascript
await fetch('/api/v1/logout', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

// 清除本地 token
localStorage.removeItem('api_token');
```

---

## 🧠 業務邏輯說明

### 核心概念

#### 1. **用戶 (User)**
- 每個用戶擁有獨立的啤酒追蹤列表
- 郵箱自動轉換為小寫以避免重複註冊
- 使用 Sanctum Token 進行身份驗證

#### 2. **品牌 (Brand)**
- 啤酒品牌（如 Guinness, Heineken）
- 全局共享，所有用戶可見
- 按名稱字母順序排列

#### 3. **啤酒 (Beer)**
- 特定的啤酒產品（如 Guinness Draught）
- 屬於某個品牌
- 包含風格資訊（如 Dry Stout, IPA）

#### 4. **用戶啤酒計數 (UserBeerCount)**
- **關鍵概念**：追蹤特定用戶對特定啤酒的品飲次數
- 每個用戶-啤酒組合有一條記錄
- 儲存：
  - `count`: 當前品飲總次數
  - `last_tasted_at`: 最後一次品飲時間
- 使用專用表格避免聚合查詢，提升效能

#### 5. **品飲日誌 (TastingLog)**
- 記錄每一次品飲動作的**審計追蹤**
- 包含：
  - 動作類型（increment/decrement）
  - 時間戳記
  - 可選的品飲筆記
- 永久保存，不可刪除（用於歷史回溯）

### 資料關聯圖

```
User (用戶)
  ↓ 1:N
UserBeerCount (用戶啤酒計數)
  ↓ N:1
Beer (啤酒) ← N:1 → Brand (品牌)
  ↓ 1:N
TastingLog (品飲日誌)
```

### 業務規則

#### 添加啤酒到追蹤列表
```
1. 檢查品牌是否存在
2. 創建或查找啤酒記錄
3. 為用戶創建 UserBeerCount 記錄，初始 count = 1
4. 創建第一條 TastingLog（action = 'increment'）
5. 事務性操作，確保資料一致性
```

#### 品飲計數操作
```
Increment（增加）:
1. 鎖定 UserBeerCount 記錄（防止併發問題）
2. count + 1
3. 更新 last_tasted_at
4. 創建 TastingLog 記錄
5. 在事務中完成所有操作

Decrement（減少）:
1. 鎖定 UserBeerCount 記錄
2. 檢查 count > 0（不能為負數）
3. count - 1
4. 更新 last_tasted_at（即使減少也更新時間）
5. 創建 TastingLog 記錄
```

#### 並發安全
所有計數操作使用：
```php
DB::transaction(function () {
    $userBeerCount = UserBeerCount::lockForUpdate()->find($id);
    // 安全地更新計數
});
```

這確保了多個請求同時操作時不會出現數據不一致。

---

## 💡 完整使用範例

### 範例 1: 完整的用戶註冊與品飲流程

```javascript
// 完整的 API 使用流程範例
class BeerTracker {
  constructor() {
    this.baseURL = 'https://your-domain.com/api/v1';
    this.token = null;
  }

  // 1. 註冊新用戶
  async register(name, email, password) {
    const response = await fetch(`${this.baseURL}/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name,
        email,
        password,
        password_confirmation: password
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Registration failed');
    }

    const { user, token } = await response.json();
    this.token = token;
    return user;
  }

  // 2. 獲取品牌列表
  async getBrands() {
    const response = await fetch(`${this.baseURL}/brands`, {
      headers: { 'Authorization': `Bearer ${this.token}` }
    });

    const { data } = await response.json();
    return data;
  }

  // 3. 添加新啤酒
  async addBeer(name, brandId, style) {
    const response = await fetch(`${this.baseURL}/beers`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name,
        brand_id: brandId,
        style
      })
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to add beer');
    }

    const { data } = await response.json();
    return data;
  }

  // 4. 獲取我的啤酒列表（支援分頁和排序）
  async getMyBeers(options = {}) {
    const params = new URLSearchParams({
      per_page: options.perPage || 20,
      page: options.page || 1,
      sort: options.sort || '-tasted_at' // 最新的排前面
    });

    if (options.brandId) {
      params.append('brand_id', options.brandId);
    }

    const response = await fetch(
      `${this.baseURL}/beers?${params}`,
      {
        headers: { 'Authorization': `Bearer ${this.token}` }
      }
    );

    const result = await response.json();
    return {
      beers: result.data,
      meta: result.meta,
      links: result.links
    };
  }

  // 5. 記錄品飲（增加計數）
  async recordTasting(beerId, note = null) {
    const response = await fetch(
      `${this.baseURL}/beers/${beerId}/count_actions`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'increment',
          note
        })
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to record tasting');
    }

    const { data } = await response.json();
    return data;
  }

  // 6. 撤銷品飲（減少計數）
  async undoTasting(beerId, note = null) {
    const response = await fetch(
      `${this.baseURL}/beers/${beerId}/count_actions`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          action: 'decrement',
          note
        })
      }
    );

    if (!response.ok) {
      const error = await response.json();
      if (error.error_code === 'BIZ_001') {
        throw new Error('Cannot decrement below zero');
      }
      throw new Error(error.message || 'Failed to undo tasting');
    }

    const { data } = await response.json();
    return data;
  }

  // 7. 獲取品飲歷史
  async getTastingHistory(beerId) {
    const response = await fetch(
      `${this.baseURL}/beers/${beerId}/tasting_logs`,
      {
        headers: { 'Authorization': `Bearer ${this.token}` }
      }
    );

    const { data } = await response.json();
    return data;
  }
}

// 使用範例
async function example() {
  const tracker = new BeerTracker();

  try {
    // 註冊
    const user = await tracker.register(
      'John Doe',
      'john@example.com',
      'securePassword123'
    );
    console.log('Registered:', user);

    // 獲取品牌
    const brands = await tracker.getBrands();
    console.log('Available brands:', brands);

    // 添加啤酒
    const beer = await tracker.addBeer(
      'Guinness Draught',
      brands[0].id,
      'Dry Stout'
    );
    console.log('Added beer:', beer);

    // 記錄品飲
    const updated = await tracker.recordTasting(
      beer.id,
      'Enjoyed at the pub with friends!'
    );
    console.log('Recorded tasting:', updated);

    // 查看歷史
    const history = await tracker.getTastingHistory(beer.id);
    console.log('Tasting history:', history);

  } catch (error) {
    console.error('Error:', error.message);
  }
}
```

### 範例 2: 分頁處理

```javascript
// 完整的分頁處理範例
async function loadAllMyBeers(tracker) {
  let allBeers = [];
  let currentPage = 1;
  let hasMorePages = true;

  while (hasMorePages) {
    const result = await tracker.getMyBeers({
      page: currentPage,
      perPage: 50,
      sort: '-tasted_at' // 最近品飲的排前面
    });

    allBeers = allBeers.concat(result.beers);

    // 檢查是否還有更多頁
    hasMorePages = currentPage < result.meta.last_page;
    currentPage++;
  }

  return allBeers;
}

// 無限滾動載入
async function infiniteScrollBeers(tracker, page = 1) {
  const result = await tracker.getMyBeers({
    page,
    perPage: 20,
    sort: '-tasted_at'
  });

  // 渲染啤酒列表
  renderBeerList(result.beers);

  // 如果還有下一頁，返回下一頁的載入函數
  if (result.links.next) {
    return () => infiniteScrollBeers(tracker, page + 1);
  }

  return null;
}
```

### 範例 3: 錯誤處理與重試

```javascript
// 帶有重試機制的 API 呼叫
async function apiCallWithRetry(apiFunction, maxRetries = 3) {
  let lastError;

  for (let attempt = 1; attempt <= maxRetries; attempt++) {
    try {
      return await apiFunction();
    } catch (error) {
      lastError = error;

      // 不重試客戶端錯誤（4xx）
      if (error.status >= 400 && error.status < 500) {
        throw error;
      }

      // 最後一次嘗試失敗
      if (attempt === maxRetries) {
        throw error;
      }

      // 指數退避
      const delay = Math.pow(2, attempt) * 1000;
      await new Promise(resolve => setTimeout(resolve, delay));

      console.log(`Retry attempt ${attempt + 1}/${maxRetries}...`);
    }
  }

  throw lastError;
}

// 使用範例
async function recordTastingWithRetry(tracker, beerId, note) {
  return apiCallWithRetry(() =>
    tracker.recordTasting(beerId, note)
  );
}
```

### 範例 4: React Hook 整合

```javascript
// React 自訂 Hook 範例
import { useState, useEffect } from 'react';

function useBeerList(token, options = {}) {
  const [beers, setBeers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [meta, setMeta] = useState(null);

  useEffect(() => {
    let cancelled = false;

    async function fetchBeers() {
      try {
        setLoading(true);
        setError(null);

        const params = new URLSearchParams({
          per_page: options.perPage || 20,
          page: options.page || 1,
          sort: options.sort || '-tasted_at'
        });

        if (options.brandId) {
          params.append('brand_id', options.brandId);
        }

        const response = await fetch(
          `/api/v1/beers?${params}`,
          {
            headers: { 'Authorization': `Bearer ${token}` }
          }
        );

        if (!response.ok) {
          throw new Error('Failed to fetch beers');
        }

        const result = await response.json();

        if (!cancelled) {
          setBeers(result.data);
          setMeta(result.meta);
        }
      } catch (err) {
        if (!cancelled) {
          setError(err.message);
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    fetchBeers();

    return () => {
      cancelled = true;
    };
  }, [token, options.page, options.perPage, options.sort, options.brandId]);

  return { beers, loading, error, meta };
}

// 使用範例
function BeerListComponent({ token }) {
  const [page, setPage] = useState(1);
  const { beers, loading, error, meta } = useBeerList(token, { page });

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div>
      {beers.map(beer => (
        <div key={beer.id}>
          <h3>{beer.name}</h3>
          <p>Brand: {beer.brand.name}</p>
          <p>Tasted: {beer.tasting_count} times</p>
        </div>
      ))}

      <Pagination
        currentPage={meta.current_page}
        lastPage={meta.last_page}
        onPageChange={setPage}
      />
    </div>
  );
}
```

---

## ❌ 錯誤處理

### 標準錯誤格式

所有錯誤回應遵循統一格式：

```json
{
  "error_code": "ERR_CODE",
  "message": "Human-readable error message"
}
```

### 錯誤碼對照表

| HTTP 狀態 | 錯誤碼 | 說明 | 解決方法 |
|----------|--------|------|---------|
| 400 | BIZ_001 | 業務邏輯錯誤 | 檢查請求參數和業務規則 |
| 401 | AUTH_001 | 未認證 | 提供有效的 Bearer token |
| 401 | AUTH_002 | Token 無效或過期 | 重新登入獲取新 token |
| 404 | RES_001 | 資源不存在 | 檢查資源 ID 是否正確 |
| 404 | RES_002 | 啤酒不在追蹤列表中 | 先添加啤酒到追蹤列表 |
| 422 | VAL_001 | 驗證失敗 | 檢查請求參數格式和必填欄位 |
| 429 | RATE_001 | 超過速率限制 | 等待後重試 |
| 500 | SYS_001 | 系統錯誤 | 聯繫技術支援 |

### 常見錯誤場景

#### 1. 無法減少計數到負數

```json
// Request: POST /api/v1/beers/1/count_actions
// Body: { "action": "decrement" }

// Response: 400
{
  "error_code": "BIZ_001",
  "message": "Cannot decrement count below zero."
}
```

**解決方法**: 確認當前計數 > 0 再執行 decrement

#### 2. 啤酒不在追蹤列表

```json
// Request: GET /api/v1/beers/999/tasting_logs

// Response: 404
{
  "error_code": "RES_002",
  "message": "Beer not found in your tracked list."
}
```

**解決方法**: 先使用 POST /api/v1/beers 添加啤酒

#### 3. 驗證錯誤

```json
// Request: POST /api/v1/beers
// Body: { "name": "" }  // 缺少必填欄位

// Response: 422
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "brand_id": ["The brand id field is required."]
  }
}
```

#### 4. 速率限制

```json
// Request: 連續快速呼叫 POST /api/v1/beers/1/count_actions

// Response: 429
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

**解決方法**: 等待 `retry_after` 秒後重試

### 錯誤處理最佳實踐

```javascript
async function handleApiCall(apiFunction) {
  try {
    return await apiFunction();
  } catch (error) {
    // 根據錯誤碼處理
    switch (error.error_code) {
      case 'AUTH_001':
      case 'AUTH_002':
        // 重新導向到登入頁
        redirectToLogin();
        break;

      case 'BIZ_001':
        // 顯示業務邏輯錯誤訊息
        showUserMessage(error.message);
        break;

      case 'RES_002':
        // 提示用戶先添加啤酒
        showMessage('Please add this beer to your list first');
        break;

      case 'RATE_001':
        // 顯示速率限制提示
        showMessage(`Please wait ${error.retry_after} seconds`);
        break;

      default:
        // 未知錯誤
        console.error('API Error:', error);
        showMessage('An unexpected error occurred');
    }
  }
}
```

---

## 🎯 最佳實踐

### 1. Token 管理

```javascript
// ✅ 好的做法：安全儲存 token
class TokenManager {
  static setToken(token) {
    // 使用 httpOnly cookie（最安全）
    // 或加密後存入 localStorage
    localStorage.setItem('api_token', token);
  }

  static getToken() {
    return localStorage.getItem('api_token');
  }

  static clearToken() {
    localStorage.removeItem('api_token');
  }

  static isAuthenticated() {
    return !!this.getToken();
  }
}

// ❌ 不好的做法：明文存入全域變數
window.apiToken = 'your_token_here';
```

### 2. 請求優化

```javascript
// ✅ 好的做法：使用分頁和篩選
const result = await tracker.getMyBeers({
  page: 1,
  perPage: 20,
  brandId: 1,  // 只取特定品牌
  sort: '-tasted_at'
});

// ❌ 不好的做法：一次取得所有資料
const allBeers = await tracker.getMyBeers({
  perPage: 9999  // 可能造成效能問題
});
```

### 3. 快取策略

```javascript
// ✅ 好的做法：快取不常變動的資料
class CachedBeerTracker extends BeerTracker {
  constructor() {
    super();
    this.brandCache = null;
    this.brandCacheTime = null;
    this.CACHE_DURATION = 5 * 60 * 1000; // 5 分鐘
  }

  async getBrands() {
    const now = Date.now();

    // 如果快取有效，直接返回
    if (
      this.brandCache &&
      this.brandCacheTime &&
      now - this.brandCacheTime < this.CACHE_DURATION
    ) {
      return this.brandCache;
    }

    // 重新取得並快取
    const brands = await super.getBrands();
    this.brandCache = brands;
    this.brandCacheTime = now;

    return brands;
  }

  clearCache() {
    this.brandCache = null;
    this.brandCacheTime = null;
  }
}
```

### 4. 並發控制

```javascript
// ✅ 好的做法：防止重複提交
class ThrottledTracker {
  constructor(tracker) {
    this.tracker = tracker;
    this.pendingRequests = new Map();
  }

  async recordTasting(beerId, note) {
    // 如果已有相同請求在進行中，等待該請求完成
    const key = `tasting-${beerId}`;

    if (this.pendingRequests.has(key)) {
      return this.pendingRequests.get(key);
    }

    // 發起新請求
    const promise = this.tracker.recordTasting(beerId, note)
      .finally(() => {
        this.pendingRequests.delete(key);
      });

    this.pendingRequests.set(key, promise);
    return promise;
  }
}
```

### 5. 資料驗證

```javascript
// ✅ 好的做法：客戶端預先驗證
function validateBeerData(name, brandId, style) {
  const errors = {};

  if (!name || name.trim().length === 0) {
    errors.name = 'Beer name is required';
  }

  if (!brandId || brandId <= 0) {
    errors.brand_id = 'Valid brand ID is required';
  }

  if (style && style.length > 50) {
    errors.style = 'Style must be less than 50 characters';
  }

  return Object.keys(errors).length > 0 ? errors : null;
}

// 使用
const errors = validateBeerData(name, brandId, style);
if (errors) {
  showValidationErrors(errors);
  return;
}

await tracker.addBeer(name, brandId, style);
```

---

## ❓ 常見問題

### Q1: 為什麼我的 token 失效了？

**A**: Sanctum token 預設不會過期，但可能因為以下原因失效：
- 用戶執行了登出操作
- Token 被管理員撤銷
- 資料庫 `personal_access_tokens` 表被清空

**解決方法**: 檢測到 401 錯誤時，引導用戶重新登入。

### Q2: 如何處理併發的計數操作？

**A**: API 已實作資料庫層級的鎖定機制（`lockForUpdate()`），確保併發安全。客戶端無需特別處理，但建議：
- 避免短時間內重複提交相同請求
- 實作請求節流（throttling）
- 使用樂觀 UI 更新後再同步伺服器狀態

### Q3: 分頁時如何知道總共有多少頁？

**A**: 查看回應中的 `meta` 欄位：

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,      // 總頁數
    "per_page": 20,
    "total": 95          // 總記錄數
  }
}
```

### Q4: 可以一次添加多個啤酒嗎？

**A**: 目前 API 不支援批次操作。建議：
- 逐一添加啤酒
- 在客戶端實作批次隊列
- 使用 Promise.all() 並發執行（注意速率限制）

```javascript
// 並發添加多個啤酒
const beers = [
  { name: 'Beer 1', brand_id: 1, style: 'IPA' },
  { name: 'Beer 2', brand_id: 1, style: 'Lager' }
];

const results = await Promise.all(
  beers.map(beer => tracker.addBeer(beer.name, beer.brand_id, beer.style))
);
```

### Q5: 如何實作離線支援？

**A**: 建議使用 Service Worker 和 IndexedDB：

```javascript
// 簡化範例
class OfflineBeerTracker {
  constructor(tracker) {
    this.tracker = tracker;
    this.db = null; // IndexedDB 實例
  }

  async recordTasting(beerId, note) {
    if (navigator.onLine) {
      // 線上：直接呼叫 API
      return this.tracker.recordTasting(beerId, note);
    } else {
      // 離線：儲存到本地隊列
      await this.savePendingAction({
        type: 'tasting',
        beerId,
        note,
        timestamp: Date.now()
      });

      // 返回樂觀更新
      return { success: true, offline: true };
    }
  }

  async syncPendingActions() {
    // 網路恢復時，同步所有待處理動作
    const pending = await this.getPendingActions();

    for (const action of pending) {
      try {
        await this.tracker.recordTasting(action.beerId, action.note);
        await this.removePendingAction(action.id);
      } catch (error) {
        console.error('Failed to sync action:', error);
      }
    }
  }
}
```

### Q6: API 有速率限制嗎？

**A**: 是的，不同端點有不同的速率限制：

| 端點類型 | 限制 |
|---------|------|
| 認證端點（/register, /login） | 5次/分鐘, 20次/小時 |
| 計數操作（/count_actions） | 30次/分鐘 |
| 一般 API | 60次/分鐘 |

超過限制會收到 429 錯誤，需要等待後重試。

### Q7: 如何取得用戶的統計數據？

**A**: 目前 API 提供：
1. 個別啤酒的品飲計數（`tasting_count` 欄位）
2. 品飲歷史日誌（`/beers/{id}/tasting_logs`）

完整統計功能建議在客戶端計算：

```javascript
async function getUserStats(tracker) {
  const allBeers = await loadAllMyBeers(tracker);

  return {
    totalBeers: allBeers.length,
    totalTastings: allBeers.reduce((sum, beer) => sum + beer.tasting_count, 0),
    mostTasted: allBeers.sort((a, b) => b.tasting_count - a.tasting_count)[0],
    recentlyTasted: allBeers.sort((a, b) =>
      new Date(b.last_tasted_at) - new Date(a.last_tasted_at)
    ).slice(0, 10)
  };
}
```

---

## 📚 延伸閱讀

- [API 版本控制指南](./api-versioning.md)
- [Laravel Scribe 文件](./api-documentation.md)
- [專案優化建議](./project-optimization-recommendations.md)

---

**文件版本**: v1.0
**維護者**: Development Team
**最後更新**: 2025-11-05
