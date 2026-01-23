<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Notification;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Force locale for all URL generations in tests
        $this->app['url']->defaults(['locale' => 'en']);

        // Fake notifications to prevent Slack API calls during testing
        Notification::fake();
    }
}
