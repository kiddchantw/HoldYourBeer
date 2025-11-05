# HoldYourBeer API 遷移指南

> **目標讀者**: 已使用舊版（非版本化）API 的開發者
> **最後更新**: 2025-11-05
> **遷移截止日期**: 2026-12-31

---

## 📋 概述

HoldYourBeer API 現已實作 URL 版本控制。所有非版本化端點（如 `/api/beers`）已被標記為**已棄用**，並將於 **2026-12-31** 移除。

本指南將協助您將應用程式遷移至新的版本化 API（`/api/v1/*`）。

---

## 🚨 重要時程

| 日期 | 事件 |
|------|------|
| 2025-11-05 | 版本化 API 正式發布 |
| 2025-11-05 | 舊版 API 標記為已棄用 |
| **2026-12-31** | 舊版 API 停止服務 |

---

## 🔍 如何確認是否受影響

### 檢查棄用警告標頭

如果您的應用程式仍在使用舊版 API，回應會包含以下標頭：

```http
X-API-Deprecation: true
X-API-Deprecation-Info: Non-versioned API endpoints are deprecated. Please use /api/v1/* endpoints.
X-API-Sunset-Date: 2026-12-31
X-API-Current-Version: v1
Link: <https://your-domain.com/docs>; rel="deprecation"
```

### 檢測程式碼範例

```javascript
// 檢查回應標頭
fetch('/api/beers')
  .then(response => {
    if (response.headers.get('X-API-Deprecation') === 'true') {
      console.warn('⚠️ Using deprecated API!');
      console.warn('Sunset date:', response.headers.get('X-API-Sunset-Date'));
      console.warn('Please migrate to:', response.headers.get('X-API-Current-Version'));
    }
    return response.json();
  });
```

---

## 🔄 端點對照表

### 完整對照表

| 舊版（已棄用） | 新版（v1） | 狀態 | 變更說明 |
|--------------|-----------|------|----------|
| `POST /api/register` | `POST /api/v1/register` | ✅ 相容 | 無變更 |
| `POST /api/login` | `POST /api/v1/login` | ✅ 相容 | 無變更 |
| `POST /api/logout` | `POST /api/v1/logout` | ✅ 相容 | 無變更 |
| `GET /api/beers` | `GET /api/v1/beers` | ✅ 相容 | 無變更 |
| `POST /api/beers` | `POST /api/v1/beers` | ✅ 相容 | 無變更 |
| `POST /api/beers/{id}/count_actions` | `POST /api/v1/beers/{id}/count_actions` | ✅ 相容 | 無變更 |
| `GET /api/beers/{id}/tasting_logs` | `GET /api/v1/beers/{id}/tasting_logs` | ✅ 相容 | 無變更 |
| `GET /api/brands` | `GET /api/v1/brands` | ✅ 相容 | 無變更 |

### 關鍵要點

1. **端點行為完全相同**: v1 版本功能與舊版一致
2. **只需更新 URL**: 在所有 `/api/` 後面加上 `v1/`
3. **無需更改請求/回應格式**: 資料結構完全相同
4. **認證機制不變**: 繼續使用 Bearer token

---

## 🛠️ 遷移步驟

### 步驟 1: 評估影響範圍

搜尋專案中所有 API 呼叫：

```bash
# 搜尋所有 API 端點引用
grep -r "/api/" --include="*.js" --include="*.ts" --include="*.jsx" --include="*.tsx"

# 或使用更精確的搜尋
grep -rE "(fetch|axios|http).*['\"].*\/api\/" src/
```

### 步驟 2: 選擇遷移策略

#### 策略 A: 一次性遷移（推薦用於小型專案）

**優點**:
- 乾淨俐落
- 無技術債務
- 測試一次即可

**缺點**:
- 需要完整測試
- 部署風險較高

**適用於**: 端點數量少（< 20 個呼叫點）

#### 策略 B: 漸進式遷移（推薦用於大型專案）

**優點**:
- 風險分散
- 可逐步測試
- 出問題易回滾

**缺點**:
- 需要同時維護新舊版本
- 遷移期較長

**適用於**: 端點數量多或複雜專案

#### 策略 C: 配置切換（推薦用於多環境部署）

**優點**:
- 可按環境分階段部署
- 易於 A/B 測試
- 快速回滾

**缺點**:
- 需要額外配置管理
- 程式碼複雜度稍增

**適用於**: 多環境、需要漸進式發布

### 步驟 3: 實作遷移

#### 方法 1: 全域替換（最簡單）

如果您的專案使用統一的 API 基礎 URL：

```javascript
// Before（舊版）
const API_BASE_URL = 'https://your-domain.com/api';

// After（新版）
const API_BASE_URL = 'https://your-domain.com/api/v1';
```

#### 方法 2: API 客戶端封裝（推薦）

```javascript
// api-client.js
class ApiClient {
  constructor() {
    // 使用環境變數控制版本
    this.baseURL = process.env.REACT_APP_API_VERSION === 'v1'
      ? 'https://your-domain.com/api/v1'
      : 'https://your-domain.com/api';

    this.token = null;
  }

  setToken(token) {
    this.token = token;
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    const response = await fetch(url, {
      ...options,
      headers
    });

    // 檢查棄用警告
    if (response.headers.get('X-API-Deprecation')) {
      console.warn('⚠️ API Deprecation Warning:', {
        info: response.headers.get('X-API-Deprecation-Info'),
        sunsetDate: response.headers.get('X-API-Sunset-Date')
      });
    }

    if (!response.ok) {
      const error = await response.json();
      throw error;
    }

    return response.json();
  }

  // 便捷方法
  async get(endpoint) {
    return this.request(endpoint, { method: 'GET' });
  }

  async post(endpoint, data) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  }
}

// 使用範例
const api = new ApiClient();

// 設定使用 v1
process.env.REACT_APP_API_VERSION = 'v1';

// 所有呼叫自動使用正確的版本
const beers = await api.get('/beers');
const newBeer = await api.post('/beers', {
  name: 'Guinness Draught',
  brand_id: 1,
  style: 'Dry Stout'
});
```

#### 方法 3: 使用 Axios 攔截器

```javascript
import axios from 'axios';

// 創建 axios 實例
const api = axios.create({
  baseURL: process.env.REACT_APP_API_BASE_URL || 'https://your-domain.com/api/v1'
});

// 請求攔截器：自動加入 token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('api_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// 回應攔截器：檢查棄用警告
api.interceptors.response.use(
  response => {
    // 檢查棄用標頭
    if (response.headers['x-api-deprecation']) {
      console.warn('⚠️ API Deprecation:', {
        info: response.headers['x-api-deprecation-info'],
        sunsetDate: response.headers['x-api-sunset-date']
      });

      // 可選：發送警告到監控系統
      trackDeprecationWarning({
        endpoint: response.config.url,
        sunsetDate: response.headers['x-api-sunset-date']
      });
    }

    return response;
  },
  error => {
    // 錯誤處理
    if (error.response?.status === 401) {
      // Token 失效，重新導向到登入
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// 使用範例
export default api;

// 在其他檔案中
import api from './api-client';

// 所有呼叫自動使用 v1
const response = await api.get('/beers');
const beers = response.data;
```

#### 方法 4: 漸進式遷移（同時支援新舊版本）

```javascript
// api-config.js
export const API_ENDPOINTS = {
  // 已遷移的端點
  register: {
    url: '/v1/register',
    version: 'v1',
    migrated: true
  },
  login: {
    url: '/v1/login',
    version: 'v1',
    migrated: true
  },
  getBeers: {
    url: '/v1/beers',
    version: 'v1',
    migrated: true
  },

  // 待遷移的端點（逐步更新）
  getBrands: {
    url: '/brands',  // 舊版
    version: 'legacy',
    migrated: false
  }
};

// api-client.js
class MigrationApiClient {
  constructor() {
    this.baseURL = 'https://your-domain.com/api';
  }

  async call(endpointKey, options = {}) {
    const endpoint = API_ENDPOINTS[endpointKey];

    if (!endpoint) {
      throw new Error(`Unknown endpoint: ${endpointKey}`);
    }

    // 記錄未遷移的端點
    if (!endpoint.migrated) {
      console.warn(`⚠️ Endpoint not migrated: ${endpointKey}`);
      // 可選：發送到監控系統
      trackUnmigratedEndpoint(endpointKey);
    }

    const url = `${this.baseURL}${endpoint.url}`;
    return fetch(url, options);
  }
}

// 使用範例
const api = new MigrationApiClient();

// 已遷移的端點
await api.call('getBeers');  // 使用 /api/v1/beers

// 待遷移的端點（仍使用舊版）
await api.call('getBrands');  // 使用 /api/brands（會有警告）
```

### 步驟 4: 測試

#### 單元測試更新

```javascript
// beers.test.js
describe('Beer API', () => {
  it('should fetch beers from v1 endpoint', async () => {
    // Mock v1 endpoint
    fetchMock.mockResponseOnce(JSON.stringify({
      data: [{ id: 1, name: 'Test Beer' }]
    }));

    const beers = await api.getBeers();

    // 驗證呼叫的是 v1 端點
    expect(fetchMock).toHaveBeenCalledWith(
      'https://your-domain.com/api/v1/beers',
      expect.any(Object)
    );

    expect(beers).toHaveLength(1);
  });
});
```

#### 整合測試

```javascript
// integration.test.js
describe('API Integration', () => {
  it('should work with v1 endpoints', async () => {
    // 註冊
    const { token } = await api.register({
      name: 'Test User',
      email: 'test@example.com',
      password: 'password123'
    });

    // 設定 token
    api.setToken(token);

    // 獲取品牌
    const brands = await api.getBrands();
    expect(brands).toBeDefined();

    // 添加啤酒
    const beer = await api.addBeer({
      name: 'Test Beer',
      brand_id: brands[0].id,
      style: 'IPA'
    });
    expect(beer.id).toBeDefined();

    // 記錄品飲
    const updated = await api.recordTasting(beer.id, 'Test note');
    expect(updated.tasting_count).toBe(1);
  });
});
```

### 步驟 5: 監控與驗證

#### 設定監控

```javascript
// monitoring.js
export function setupApiMonitoring() {
  // 監控所有 fetch 請求
  const originalFetch = window.fetch;

  window.fetch = async function(...args) {
    const [url] = args;

    // 檢測舊版 API 呼叫
    if (typeof url === 'string' && /\/api\/(?!v\d+\/)/.test(url)) {
      console.error('🚨 Legacy API call detected:', url);

      // 發送到監控系統（如 Sentry, DataDog）
      trackEvent('legacy_api_call', {
        url,
        stack: new Error().stack
      });
    }

    const response = await originalFetch.apply(this, args);

    // 檢查棄用標頭
    if (response.headers.get('X-API-Deprecation')) {
      trackEvent('api_deprecation_warning', {
        url,
        sunsetDate: response.headers.get('X-API-Sunset-Date')
      });
    }

    return response;
  };
}

// 在應用程式啟動時呼叫
setupApiMonitoring();
```

#### 產生遷移報告

```javascript
// migration-report.js
class MigrationReporter {
  constructor() {
    this.legacyCalls = new Set();
    this.v1Calls = new Set();
  }

  trackCall(url) {
    if (url.includes('/api/v1/')) {
      this.v1Calls.add(url);
    } else if (url.includes('/api/')) {
      this.legacyCalls.add(url);
    }
  }

  generateReport() {
    const total = this.legacyCalls.size + this.v1Calls.size;
    const migrated = this.v1Calls.size;
    const percentage = ((migrated / total) * 100).toFixed(2);

    return {
      total,
      migrated,
      pending: this.legacyCalls.size,
      percentage,
      legacyEndpoints: Array.from(this.legacyCalls),
      v1Endpoints: Array.from(this.v1Calls)
    };
  }

  printReport() {
    const report = this.generateReport();

    console.log('📊 API Migration Report');
    console.log('=======================');
    console.log(`Total endpoints: ${report.total}`);
    console.log(`Migrated: ${report.migrated} (${report.percentage}%)`);
    console.log(`Pending: ${report.pending}`);
    console.log('\n⚠️ Legacy endpoints still in use:');
    report.legacyEndpoints.forEach(url => console.log(`  - ${url}`));
  }
}

// 使用
const reporter = new MigrationReporter();

// 在每次 API 呼叫後追蹤
fetch(url).then(response => {
  reporter.trackCall(url);
  return response;
});

// 隨時查看進度
reporter.printReport();
```

---

## ✅ 遷移檢查清單

### 開發階段

- [ ] 搜尋並列出所有 API 呼叫
- [ ] 選擇遷移策略
- [ ] 更新 API 基礎 URL 或配置
- [ ] 更新所有 API 端點路徑
- [ ] 更新 API 客戶端程式碼
- [ ] 更新環境變數

### 測試階段

- [ ] 更新單元測試
- [ ] 執行整合測試
- [ ] 測試錯誤處理流程
- [ ] 測試認證流程
- [ ] 驗證回應格式
- [ ] 效能測試

### 部署階段

- [ ] 在開發環境測試
- [ ] 在測試環境測試
- [ ] 在預生產環境測試
- [ ] 監控棄用警告
- [ ] 準備回滾計畫
- [ ] 部署到生產環境

### 驗證階段

- [ ] 檢查錯誤日誌
- [ ] 驗證功能正常
- [ ] 檢查效能指標
- [ ] 確認無棄用警告
- [ ] 更新文件
- [ ] 通知團隊完成遷移

---

## 🆘 疑難排解

### 問題 1: 遷移後收到 404 錯誤

**原因**: URL 路徑錯誤

**解決方法**:
```javascript
// ❌ 錯誤：多了一個斜線
fetch('/api//v1/beers')

// ✅ 正確
fetch('/api/v1/beers')
```

### 問題 2: 認證失敗

**原因**: Token 格式錯誤

**解決方法**:
```javascript
// ❌ 錯誤：缺少 Bearer 前綴
headers: { 'Authorization': token }

// ✅ 正確
headers: { 'Authorization': `Bearer ${token}` }
```

### 問題 3: CORS 錯誤

**原因**: 新的 v1 端點可能需要更新 CORS 配置

**解決方法**:
檢查後端 CORS 配置是否包含 `/api/v1/*`

### 問題 4: 測試環境與生產環境不一致

**原因**: 環境變數配置不同

**解決方法**:
```javascript
// 使用環境變數
const API_BASE = process.env.REACT_APP_API_BASE_URL || 'https://your-domain.com/api/v1';

// .env.development
REACT_APP_API_BASE_URL=http://localhost/api/v1

// .env.production
REACT_APP_API_BASE_URL=https://your-domain.com/api/v1
```

---

## 📞 支援與協助

### 獲取幫助

- **文件**: https://your-domain.com/docs
- **Issue Tracker**: [GitHub Issues](https://github.com/your-repo/issues)
- **Email**: support@your-domain.com

### 報告問題

請提供以下資訊：
1. 舊版端點 URL
2. 新版端點 URL
3. 錯誤訊息或截圖
4. 請求/回應範例
5. 相關程式碼片段

---

## 📚 延伸資源

- [API 使用指南](./api-usage-guide.md)
- [API 版本控制文件](./api-versioning.md)
- [Scribe API 文件](./api-documentation.md)

---

**文件版本**: v1.0
**維護者**: Development Team
**最後更新**: 2025-11-05
