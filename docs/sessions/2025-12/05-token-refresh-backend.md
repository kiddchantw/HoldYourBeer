# Session: Token Refresh 機制後端實作

**Date**: 2025-12-05
**Status**: ✅ Completed (Phase 1-3)
**Tags**: #security, #auth, #sanctum, #api
**Related**: `HoldYourBeer-Flutter/docs/sessions/2025-12/05-token-refresh-implementation-plan.md` (完整架構與前端計畫)

---

## 📋 Overview

本 Session 專注於 **Laravel 後端** 的 Token Refresh 機制實作。
目標是支援 Access Token (短效期) + Refresh Token (長效期) 的雙 Token 機制，以提升安全性並支援「記住我」功能。

## 🎯 Architecture Design

### 1. Token 策略 (Option B)
- **Access Token**: 效期 180 分鐘 / 3 小時 (Sanctum)
- **Refresh Token**: 效期 30 天 (資料庫儲存)
- **儲存方式**: 新增 `refresh_tokens` 資料表
- **多裝置**: 支援多裝置獨立登入 (每個裝置一組 Tokens)

### 2. Database Schema
新增 `refresh_tokens` table:
- `id`: PK
- `user_id`: FK to users
- `token`: SHA-256 Hash (64 chars)
- `device_name`: String (nullable)
- `user_agent`: Text (nullable)
- `expires_at`: Timestamp
- `last_used_at`: Timestamp (nullable)

### 3. API Changes
- **POST /api/v1/refresh** (New):
  - Input: `{ "refresh_token": "..." }`
  - Output: `{ "access_token": "...", "token_type": "Bearer", "expires_in": 10800 }`
- **POST /api/v1/login** (Update):
  - Input: `email`, `password`, `device_name` (optional)
  - Response 新增 `refresh_token`, `token_type`, `expires_in`
- **POST /api/v1/register** (Update):
  - Input: `name`, `email`, `password`, `password_confirmation`, `device_name` (optional)
  - Response 新增 `refresh_token`, `token_type`, `expires_in`
- **POST /api/v1/logout** (Update):
  - Input: `refresh_token` (optional)
  - 刪除 Access Token，並撤銷 Refresh Token（若提供特定 token 則只撤銷該 token，否則撤銷所有）

---

## ✅ Implementation Checklist

### Phase 1: Configuration & Migration ✅
- [x] 更新 `config/sanctum.php`: 設定 `expiration` 為 180 分鐘
- [x] 新增 `.env` 變數: `SANCTUM_EXPIRATION=180`, `REFRESH_TOKEN_EXPIRATION=43200` (30天)
- [x] 建立 Migration: `database/migrations/2025_12_05_060925_create_refresh_tokens_table.php`

### Phase 2: Core Logic ✅
- [x] 建立 Model: `App\Models\RefreshToken`
  - [x] 實作 `generate()`: 產生 64 字元 plain text token 並儲存 SHA-256 hash
  - [x] 實作 `validate()`: 驗證 hash 與效期
  - [x] 實作 `pruneExpired()`: 清理過期資料
  - [x] 實作 `markAsUsed()`: 更新最後使用時間
  - [x] 實作 `revoke()`: 撤銷 token
- [x] 更新 Controller: `Api\V1\AuthController`
  - [x] 實作 `refresh()` 方法
  - [x] 更新 `token()` (Login) 邏輯 - 回傳 refresh_token, token_type, expires_in
  - [x] 更新 `register()` 邏輯 - 回傳 refresh_token, token_type, expires_in
  - [x] 更新 `logout()` 邏輯 - 支援撤銷特定或全部 refresh tokens
- [x] 註冊 Route: `routes/api.php` - POST /api/v1/refresh

### Phase 3: Maintenance ✅
- [x] 建立 Console Command: `app/Console/Commands/PruneRefreshTokens.php` (`tokens:prune-refresh`)
- [x] 設定排程: `bootstrap/app.php` - 每日執行清理

### Phase 4: Testing (未完成)
- [ ] Feature Test: `tests/Feature/Api/V1/Auth/RefreshTokenTest.php`
  - [ ] 測試正常刷新
  - [ ] 測試 Token 過期/無效
  - [ ] 測試 Logout 撤銷
  - [ ] 測試 Login/Register 回傳格式

---

## 📝 Implementation Summary

### 核心檔案
1. **Model**: `app/Models/RefreshToken.php`
2. **Controller**: `app/Http/Controllers/Api/V1/AuthController.php`
3. **Migration**: `database/migrations/2025_12_05_060925_create_refresh_tokens_table.php`
4. **Command**: `app/Console/Commands/PruneRefreshTokens.php`
5. **Routes**: `routes/api.php`
6. **Config**: `config/sanctum.php`, `.env`, `bootstrap/app.php`

### API 端點
- `POST /api/v1/login` - 登入並獲取 access + refresh tokens
- `POST /api/v1/register` - 註冊並獲取 access + refresh tokens
- `POST /api/v1/refresh` - 使用 refresh token 換取新 access token
- `POST /api/v1/logout` - 登出並撤銷 tokens

### Response 格式範例
```json
{
  "user": { "id": 1, "name": "...", "email": "..." },
  "token": "1|abc123...",
  "refresh_token": "xyz789abc456def123...",
  "token_type": "Bearer",
  "expires_in": 10800
}
```

### 維護指令
```bash
# 手動清理過期 refresh tokens
php artisan tokens:prune-refresh

# 排程已設定：每日自動執行
```

## 🔌 Remember Me Support (Future Phase)

To support "Remember Me" functionality where the token expiration is extended:

### API Changes
- **POST /api/v1/login** (Update):
  - Input: Add optional `remember_me` (boolean)
  - Logic: If `true`, set Refresh Token expiration to 30 days. If `false`, set to standard duration (e.g., 7 days or same as access token depending on policy).

### Code Reference (AuthController)

```php
public function login(Request $request): JsonResponse
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'remember_me' => 'boolean',
    ]);

    // ... verification ...

    // Determine expiration based on remember_me
    // Default: 1 day? Or use env config.
    // Remember Me: 30 days.
    $expirationMinutes = $request->remember_me 
        ? config('sanctum.refresh_token_expiration_long', 43200) // 30 days
        : config('sanctum.refresh_token_expiration_short', 1440); // 1 day

    // Generate Refresh Token with specific expiration
    // ...
}
```
