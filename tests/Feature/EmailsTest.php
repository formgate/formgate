<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class EmailsTest extends TestCase
{
    /**
     * Test that a simple email is sent when a post request is made
     * to the /send url.
     *
     */
    public function test_simple_email_is_sent()
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
     */
    public function test_email_not_on_allowed_list_fails()
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
     */
    public function test_invalid_sender_email_gets_overwritten()
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
     */
    public function test_honeypot_field_filled_in_rejects_submission()
    {
        $this->post('/send', ['_hp_email' => 'test@formgate.dev'])
            ->assertStatus(422);
        $this->assertNoMailSent();
    }

    /**
     * Test that a valid recaptcha response submits the form
     */
    public function test_valid_captcha_submits_form()
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
     * Test recaptcha form is presented when enabled without email being sent
     */
    public function test_captcha_form_is_presented()
    {
        Config::set('formgate.recaptcha.enabled', 'true');

        $data = [
            '_recipient' => 'test@formgate.dev',
            'Message' => 'Hello world!',
        ];

        $this->post('/send', $data)->assertViewIs('recaptcha');
        $this->assertNoMailSent();
    }

    /**
     * Test invalid recaptcha submission informs user without email being sent
     */
    public function test_invalid_captcha_shows_error()
    {
        // Setup test google recaptcha keys (these keys will always fail)
        Config::set('formgate.recaptcha.enabled', 'true');
        Config::set('formgate.recaptcha.site_key', 'invalid');
        Config::set('formgate.recaptcha.secret_key', 'invalid');

        $data = [
            'g-recaptcha-response' => 'invalid',
            '_recipient' => 'test@formgate.dev',
            'Message' => 'Hello world!',
        ];

        $this->post('/send', $data)->assertViewIs('recaptcha');
        $this->post('/send', $data)->assertSee('You failed the robot check.');
        $this->assertNoMailSent();
    }

    /**
     * Test that file uploads work with captcha enabled
     */
    public function test_file_upload_works_with_captcha_enabled()
    {
        Config::set('formgate.recaptcha.enabled', 'true');
        Config::set('formgate.recaptcha.site_key', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
        Config::set('formgate.recaptcha.secret_key', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
        Storage::fake();

        $file = UploadedFile::fake()->image('image.jpg');
        $data = [
            '_recipient' => 'test@formgate.dev',
            'file' => $file
        ];

        $this->post('/send', $data)
            ->assertViewIs('recaptcha');

        $data['g-recaptcha-response'] = 'valid-code';

        $this->followingRedirects()
            ->post('/send', $data)
            ->assertViewIs('thanks');

        $emailEntity = $this->getLastEmail()->getChildren()[0];

        $header = $emailEntity
            ->getHeaders()
            ->get('content-disposition')
            ->getFieldBody();
        $filename = str_replace('attachment; filename=', '', $header);
        $this->assertEquals($filename, $file->hashName());

        $this->assertEquals($emailEntity->getBody(), $file->get());
    }

    /**
     * Test that file uploads work when recaptcha is disabled.
     */
    public function test_file_upload_with_no_recaptcha()
    {
        Storage::fake();
        $file = UploadedFile::fake()->image('image.jpg');

        $this->followingRedirects()
            ->post('/send', [
                '_recipient' => 'test@formgate.dev',
                'file' => $file
            ])
            ->assertViewIs('thanks');

        $emailEntity = $this->getLastEmail()->getChildren()[0];

        $header = $emailEntity
            ->getHeaders()
            ->get('content-disposition')
            ->getFieldBody();
        $filename = str_replace('attachment; filename=', '', $header);
        $this->assertEquals($filename, $file->hashName());

        $this->assertEquals($emailEntity->getBody(), $file->get());
    }
}
