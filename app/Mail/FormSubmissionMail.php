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
        $from_name = $this->processor->getSenderName() ?: config('mail.from.name');
        $this->from(config('mail.from.address'), $from_name);
        $this->subject($this->processor->getSubject());

        if ($this->processor->getSenderEmail()) {
            $this->replyTo($this->processor->getSenderEmail());
        }

        // If there is a file in the FormProcessor object then we fetch it and attach it
        if ($this->processor->getFile()) {
            $this->attachFromStorage($this->processor->getFile());
        }

        return $this->text('emails.form_submission');
    }
}
