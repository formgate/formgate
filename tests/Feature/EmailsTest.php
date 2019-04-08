<?php

namespace Tests\Feature;

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
        $this->assertEmailSent();
    }

    /**
     * Test that an InvalidArgumentException is thrown and no email is sent
     * when an email not in the allowed env list is used.
     *
     * @return void
     */
    public function test_email_not_on_allowed_list_fails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->withoutExceptionHandling()
            ->post('/send', ['_recipient' => 'not_allowed@formgate.dev', 'message' => 'Hello world!']);
        $this->assertNoEmailSent();
    }
}
