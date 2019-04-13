<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

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

    protected function enable_recaptcha($valid = true): void
    {
        Config::set('formgate.recaptcha.enabled', $valid ? 'true' : 'false');
        Config::set('formgate.recaptcha.site_key', $valid ? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI' : 'invalid');
        Config::set('formgate.recaptcha.secret_key', $valid ? '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe' : 'invalid');
    }
}
