<?php

namespace App\Http\Controllers;

use App\FormProcessor;

class SendController extends Controller
{
    /**
     * @var FormProcessor
     */
    private $mailer;

    /**
     * @param FormProcessor $mailer
     */
    public function __construct(FormProcessor $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @return array
     */
    private function getFields(): array
    {
        return request()->except([
            '_recipient',
            '_sender_name',
            '_sender_email',
            '_subject',
            '_redirect_success',
            '_hp_email',
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        abort_if(!empty(request('_hp_email')), 422); // Abort the request if there is a filled in _hp_email field

        try {
            $this->mailer->setSenderName(request('_sender_name'));
            $this->mailer->setSenderEmail(request('_sender_email'));
        } catch (\InvalidArgumentException $e) {
            // For now, there's no way to handle end user errors but any data should still be submitted.
            // To ensure a valid sender email is set, use <input type="email"> in your form.
        }

        $this->mailer->setRecipient(request('_recipient'));
        $this->mailer->setSubject(request('_subject', 'Contact form submission'));
        $this->mailer->setFields($this->getFields());
        $this->mailer->send();

        return redirect(request('_redirect_success', 'thanks'));
    }
}
