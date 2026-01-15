<?php

namespace App\Notifications\Slack;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;
use Illuminate\Support\Facades\DB;

/**
 * 用戶註冊 Slack 通知
 *
 * 當新用戶註冊時，發送通知到 Slack #holdyourbeer-users 頻道
 * 包含用戶基本資訊、註冊方式、Email Domain 和統計資訊
 */
class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * 註冊的用戶
     */
    private User $user;

    /**
     * 建立新的通知實例
     *
     * @param User $user 註冊的用戶
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param mixed $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable): SlackMessage
    {
        $registrationMethod = $this->getRegistrationMethod();
        $emailDomain = $this->getEmailDomain();
        $stats = $this->getUserStatistics();
        $timestamp = $this->getFormattedTimestamp();
        $env = strtoupper(app()->environment());

        return (new SlackMessage)
            ->headerBlock("[{$env}] 🎉 新用戶註冊")
            ->sectionBlock(function (SectionBlock $block) use ($registrationMethod, $emailDomain, $timestamp) {
                $block->field("*用戶名稱:*\n{$this->user->name}")->markdown();
                $block->field("*註冊方式:*\n{$registrationMethod}")->markdown();
                $block->field("*Email Domain:*\n{$emailDomain}")->markdown();
                $block->field("*時間:*\n{$timestamp}")->markdown();
            })
            ->sectionBlock(function (SectionBlock $block) use ($stats) {
                $block->text("📊 *統計*: 總用戶 {$stats['total']} | 今日新增 {$stats['today']}");
            });
    }

    /**
     * 取得註冊方式
     *
     * @return string 註冊方式（Email 或 OAuth）
     */
    private function getRegistrationMethod(): string
    {
        // 如果有密碼，表示是本地註冊（Email）
        // 如果沒有密碼，表示是 OAuth 註冊
        return $this->user->password ? '📧 Email' : '🔗 OAuth';
    }

    /**
     * 取得 Email Domain（不包含完整 email，保護隱私）
     *
     * @return string Email Domain（如：@example.com）
     */
    private function getEmailDomain(): string
    {
        $parts = explode('@', $this->user->email);
        return '@' . ($parts[1] ?? 'unknown');
    }

    /**
     * 取得用戶統計資訊（優化為單一查詢）
     *
     * @return array{total: int, today: int}
     */
    private function getUserStatistics(): array
    {
        // 使用單一查詢取得總數和今日新增數，提升效能
        $result = DB::table('users')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today
            ', [today()->toDateString()])
            ->first();

        return [
            'total' => (int) $result->total,
            'today' => (int) $result->today,
        ];
    }

    /**
     * 取得格式化的時間戳記
     *
     * @return string 格式化的時間（如：2026-01-12 15:30）
     */
    private function getFormattedTimestamp(): string
    {
        return now()->format('Y-m-d H:i');
    }
}
