<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Exception;

class TestMailgunConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun:test {email? : 測試郵件接收者信箱}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '測試 Mailgun 郵件服務連線';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('開始測試 Mailgun 連線...');
        $this->newLine();

        // 顯示當前設定
        $this->info('當前 Mailgun 設定:');
        $this->line('  Domain: ' . config('services.mailgun.domain'));
        $this->line('  Endpoint: ' . config('services.mailgun.endpoint'));
        $this->line('  From Address: ' . config('mail.from.address'));
        $this->line('  From Name: ' . config('mail.from.name'));
        $this->newLine();

        // 取得測試郵件接收者
        $recipient = $this->argument('email') ?? $this->ask('請輸入測試郵件接收者信箱');

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error('無效的電子郵件地址！');
            return Command::FAILURE;
        }

        try {
            $this->info("正在發送測試郵件至 {$recipient}...");

            Mail::raw('這是一封來自 HoldYourBeer 的 Mailgun 測試郵件。如果您收到這封信，表示 Mailgun 設定成功！', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('[HoldYourBeer] Mailgun 連線測試');
            });

            $this->newLine();
            $this->info('✅ 測試郵件已成功發送！');
            $this->line("請檢查 {$recipient} 的收件匣（或垃圾郵件資料夾）");
            $this->newLine();

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->newLine();
            $this->error('❌ 發送測試郵件失敗！');
            $this->error('錯誤訊息: ' . $e->getMessage());
            $this->newLine();
            $this->warn('請檢查:');
            $this->line('  1. Mailgun API Key 是否正確');
            $this->line('  2. Mailgun Domain 是否已驗證');
            $this->line('  3. 網路連線是否正常');
            $this->newLine();

            return Command::FAILURE;
        }
    }
}
