<?php

return [
    'title' => '意見與回饋',
    'submit_title' => '提供我們意見',
    'submit_description' => '您的意見是我們進步的動力。請不吝告訴我們您的想法，或是您遇到的問題。',
    'admin_list_title' => '意見回饋管理',
    
    'fields' => [
        'type' => '類型',
        'description' => '描述',
        'priority' => '優先級',
        'status' => '狀態',
        'user' => '使用者',
        'date' => '日期',
        'actions' => '操作',
        'description_placeholder' => '請詳細描述您的問題或建議...',
        'admin_notes' => '管理員備註',
    ],

    'types' => [
        'feedback' => '一般意見',
        'bug_report' => '錯誤回報',
        'feature_request' => '功能許願',
    ],

    'statuses' => [
        'new' => '新進',
        'in_review' => '審核中',
        'in_progress' => '處理中',
        'resolved' => '已解決',
        'closed' => '已關閉',
        'rejected' => '已駁回',
    ],
    
    'priorities' => [
        'low' => '低',
        'medium' => '中',
        'high' => '高',
        'critical' => '緊急',
    ],

    'messages' => [
        'submit_success' => '感謝您的意見，我們會盡快處理！',
        'update_success' => '回饋狀態已更新。',
        'delete_success' => '回饋已刪除。',
    ],
    
    'actions' => [
        'submit' => '送出意見',
        'update' => '更新',
        'delete' => '刪除',
        'view' => '查看',
        'cancel' => '取消',
        'save' => '儲存',
    ]
];
