<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function assertNoEmailSent(): void
    {
        $emails = Application::getInstance()->make('swift.transport')->driver()->messages();
        $this->assertCount(0, $emails);
    }

    protected function assertEmailSent(): void
    {
        /** @var \Illuminate\Foundation\Application $this ->app */
        $emails = Application::getInstance()->make('swift.transport')->driver()->messages();
        $this->assertCount(1, $emails);
    }
}
