<?php

return [
    'title' => 'Feedback',
    'submit_title' => 'Give Feedback',
    'submit_description' => 'Your feedback helps us improve. Please let us know your thoughts or any issues you encountered.',
    'admin_list_title' => 'Feedback Management',
    
    'fields' => [
        'type' => 'Type',
        'description' => 'Description',
        'priority' => 'Priority',
        'status' => 'Status',
        'user' => 'User',
        'date' => 'Date',
        'actions' => 'Actions',
        'description_placeholder' => 'Please describe your issue or suggestion in detail...',
        'admin_notes' => 'Admin Notes',
    ],

    'types' => [
        'feedback' => 'General Feedback',
        'bug_report' => 'Bug Report',
        'feature_request' => 'Feature Request',
    ],

    'statuses' => [
        'new' => 'New',
        'in_review' => 'In Review',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
        'rejected' => 'Rejected',
    ],
    
    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'critical' => 'Critical',
    ],

    'messages' => [
        'submit_success' => 'Thank you for your feedback! We will handle it shortly.',
        'update_success' => 'Feedback status updated.',
        'delete_success' => 'Feedback deleted.',
    ],
    
    'actions' => [
        'submit' => 'Submit Feedback',
        'update' => 'Update',
        'delete' => 'Delete',
        'view' => 'View',
        'cancel' => 'Cancel',
        'save' => 'Save',
    ]
];
