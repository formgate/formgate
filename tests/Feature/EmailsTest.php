<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Tests\TestCase;

class EmailsTest extends TestCase
{
    /**
     * Test that a simple email is sent when a post request is made
     * to the /send url.
     *
     * @return void
     */
    public function test_simple_email_is_sent(): void
    {
        $this->post('/send', ['_recipient' => 'test@formgate.dev', 'Message' => 'Hello world!'])
            ->assertRedirect('/thanks');
        $this->assertMailSent();
        $this->assertContains('Message:' . PHP_EOL . 'Hello world!', $this->getLastEmail()->getBody());
    }

    /**
     * Test that an InvalidArgumentException is thrown and no email is sent
     * when an email not in the allowed env list is used.
     *
     * @return void
     */
    public function test_email_not_on_allowed_list_fails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->withoutExceptionHandling()
            ->post('/send', ['_recipient' => 'not_allowed@formgate.dev']);
        $this->assertNoMailSent();
    }

    /**
     * Test that when an invalid sender email is passed through then it is
     * overwritten with the config mail from address value and the email
     * sends successfully.
     *
     * @return void
     */
    public function test_invalid_sender_email_gets_overwritten(): void
    {
        $this->post('/send', ['_recipient' => 'test@formgate.dev', '_sender_email' => 'invalid email']);
        $this->assertMailSent();
        $lastEmail = $this->getLastEmail();
        $this->assertArrayHasKey(config('mail.from.address'), $lastEmail->getFrom());
        $this->assertArrayNotHasKey('invalid email', $lastEmail->getFrom());
    }

    /**
     * Test that if a field name with `_hp_email` is filled in that the
     * form submission is discarded as spam.
     *
     * @return void
     */
    public function test_honeypot_field_filled_in_rejects_submission(): void
    {
        $this->post('/send', ['_hp_email' => 'test@formgate.dev'])
            ->assertStatus(422);
        $this->assertNoMailSent();
    }

    /**
     * Test that a valid recaptcha response submits the form
     */
    public function test_valid_captcha_submits_form(): void
    {
        $data = ['_recipient' => 'test@formgate.dev', 'Message' => 'Hello world!'];

        // Setup test google recaptcha keys (these keys will always pass)
        Config::set('formgate.recaptcha.enabled', 'true');
        Config::set('formgate.recaptcha.site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
        Config::set('formgate.recaptcha.secret_key', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

        $this->post('/send', $data)
            ->assertViewIs('recaptcha');

        $data['g-recaptcha-response'] = 'valid-code';
        $this->post('/send', $data)
            ->assertRedirect('/thanks');

        $this->assertMailSent();
    }

    /**
     * Test invalid captcha doesn't submit the form
     */
    public function test_invalid_captcha_doesnt_submit_form()
    {
        $data = ['_recipient' => 'test@formgate.dev', 'Message' => 'Hello world!'];

        // Setup test google recaptcha keys (these keys will always fail)
        Config::set('formgate.recaptcha.enabled', 'true');
        Config::set('formgate.recaptcha.site_key', 'invalid');
        Config::set('formgate.recaptcha.secret_key', 'invalid');

        $this->post('/send', $data)
            ->assertViewIs('recaptcha');

        $this->post('/send', $data)
            ->assertSee('You failed the robot check.');

        $this->assertNoMailSent();
    }
}
