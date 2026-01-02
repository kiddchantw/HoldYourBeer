<?php

return [
    // 欄位名稱
    'attributes' => [
        'name' => '品牌名稱',
    ],

    // 驗證訊息
    'validation' => [
        'name_required' => ':attribute 為必填欄位',
        'name_unique' => '此 :attribute 已存在',
        'name_max' => ':attribute 不可超過 :max 個字元',
    ],

    // 操作訊息
    'messages' => [
        'created' => '品牌已成功建立',
        'updated' => '品牌已成功更新',
        'deleted' => '品牌已成功刪除',
        'restored' => '品牌已成功恢復',
        'force_deleted' => '品牌已永久刪除',
        'cannot_delete_has_beers' => '此品牌下還有 :count 個啤酒，無法刪除。請先刪除或轉移這些啤酒。',
        'error' => '操作失敗，請稍後再試',
    ],

    // 導航選單
    'menu' => '品牌',

    // 頁面標題
    'titles' => [
        'index' => '品牌管理',
        'create' => '新增品牌',
        'edit' => '編輯品牌',
    ],

    // 按鈕
    'buttons' => [
        'create' => '新增品牌',
        'edit' => '編輯',
        'delete' => '刪除',
        'restore' => '恢復',
        'force_delete' => '永久刪除',
        'search' => '搜尋',
        'clear' => '清除',
        'submit' => '儲存',
        'submitting' => '處理中...',
        'cancel' => '取消',
    ],

    // 表格欄位
    'table' => [
        'id' => 'ID',
        'name' => '品牌名稱',
        'beers_count' => '啤酒數量',
        'created_at' => '建立時間',
        'updated_at' => '更新時間',
        'deleted_at' => '刪除時間',
        'actions' => '操作',
    ],

    // 搜尋相關
    'search' => [
        'placeholder' => '搜尋品牌名稱...',
        'results' => '找到 :count 個符合「:keyword」的品牌',
        'no_results' => '目前沒有任何品牌',
    ],

    // 確認訊息
    'confirm' => [
        'delete' => '確定要刪除此品牌嗎？',
        'force_delete' => '確定要永久刪除此品牌嗎？此操作無法復原！',
    ],
];
