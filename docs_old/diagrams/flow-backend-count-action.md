### 流程圖三：後端核心邏輯 - 計數動作 (Transaction)

這張圖專門描繪後端在收到「+1」或「-1」請求時，內部執行的、包含「資料庫交易」的關鍵邏輯，以確保資料一致性。

備註：Web 端（`TastingController@increment/decrement`）與 API 端共用相同一致性策略，皆使用 `DB::transaction(...)` 並在讀取 `user_beer_counts` 時以 `lockForUpdate()` 取得列級鎖，避免競態條件造成的計數異常或日誌遺失。

```mermaid
graph TD
    A[API 接收到 POST /.../count_actions 請求] --> B[DB::beginTransaction，開始資料庫交易]
    B --> C[UPDATE user_beer_counts，將對應的 total_count +/- 1]
    C --> D[INSERT INTO tasting_logs，新增一筆 increment/decrement 的日誌]
    D --> E{操作是否都成功?}
    E -- 是 --> F[DB::commit，提交交易，讓變更生效]
    E -- 否 --> G[DB::rollBack，撤銷所有變更，回到操作前狀態]
    F --> H[回傳 200 OK，與更新後的啤酒資料給前端]
    G --> I[回傳 500 Server Error，或其他錯誤訊息給前端]
    H --> J[結束]
    I --> J
```
