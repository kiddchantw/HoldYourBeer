<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Password Reset Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | outcome such as failure due to an invalid password / reset token.
    |
    */

    'reset' => '您的密碼已重設。',
    'sent' => '我們已將密碼重設連結寄送至您的信箱。',
    'throttled' => '密碼重設嘗試次數過多，請稍後再試。',
    'token' => '此密碼重設令牌無效或已過期。',
    'user' => '找不到使用此信箱的用戶。',

    // Custom error messages
    'mail_error' => '無法發送密碼重設郵件，請稍後再試或聯繫客服。',
    'reset_error' => '無法重設密碼，請重新嘗試或申請新的重設連結。',

    // OAuth user hint
    'oauth_hint' => '如果此信箱已註冊，您將收到重設密碼郵件。若您是使用第三方登入（如 Google），請直接使用該方式登入。',

];
