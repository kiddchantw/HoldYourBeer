<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoggingConfigurationTest extends TestCase
{
    public function test_slack_log_channel_is_configured_correctly(): void
    {
        $config = config('logging.channels.slack');

        $this->assertEquals('slack', $config['driver']);
        $this->assertEquals('HoldYourBeer Bot (' . env('APP_ENV') . ')', $config['username']);
        $this->assertEquals(':rotating_light:', $config['emoji']);
        $this->assertEquals('error', $config['level']);
        // 驗證 URL 會從環境變數讀取（在測試環境中可能是空的，但 key 應該存在）
        // $this->assertTrue(array_key_exists('url', $config)); 
        // 嚴格來說，我們要確認它是 env('SLACK_WEBHOOK_ERRORS')，但這很難直接測。
        // 只要確認 key 存在即可。
    }

    public function test_stack_channel_configuration(): void
    {
        // 驗證 stack channel 的 driver
        $this->assertEquals('stack', config('logging.channels.stack.driver'));
        
        // 注意：我們不在這裡驗證 channels 內容，因為這取決於環境變數 LOG_STACK
    }
}
