<?php

namespace App\Mail;

use App\FormProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var FormProcessor */
    public $processor;

    /**
     * Create a new message instance.
     *
     * @param FormProcessor $processor
     */
    public function __construct(FormProcessor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->from(config('mail.from.address'), $this->processor->getSenderName());
        $this->subject($this->processor->getSubject());

        if ($this->processor->getSenderEmail()) {
            $this->replyTo($this->processor->getSenderEmail());
        }

        // If there is a file in the FormProcessor object then we fetch it and attach it
        if (!empty($this->processor->getFile())) {
            $this->attachFromStorage($this->processor->getFile());
        }

        return $this->text('emails.form_submission');
    }
}
