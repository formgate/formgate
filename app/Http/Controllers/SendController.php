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
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        $this->mailer->setRecipient(request('_recipient'));
        $this->mailer->setSenderName(request('_sender_name'));
        $this->mailer->setSenderEmail(request('_sender_email'));
        $this->mailer->setSubject(request('_subject', 'Contact form submission'));
        $this->mailer->setFields($this->getFields());
        $this->mailer->send();

        return redirect(request('_redirect_success', 'thanks'));
    }
}
