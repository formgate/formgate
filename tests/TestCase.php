<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function assertNoMailSent(): void
    {
        $emails = Application::getInstance()->make('swift.transport')->driver()->messages();
        $this->assertCount(0, $emails);
    }

    protected function assertMailSent(): void
    {
        $emails = Application::getInstance()->make('swift.transport')->driver()->messages();
        $this->assertCount(1, $emails);
    }

    protected function getLastEmail(): \Swift_Message
    {
        return Application::getInstance()->make('swift.transport')->driver()->messages()->last();
    }
}
