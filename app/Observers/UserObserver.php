<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\Slack\UserRegisteredNotification;

/**
 * 用戶模型觀察者
 *
 * 監聽 User Model 的事件並執行相應的操作
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * 當新用戶建立時，發送 Slack 通知
     *
     * @param User $user 新建立的用戶
     * @return void
     */
    public function created(User $user): void
    {
        // 發送 Slack 通知
        $user->notify(new UserRegisteredNotification($user));
    }
}
