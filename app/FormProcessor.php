<?php

namespace App;

use App\Mail\FormSubmissionMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class FormProcessor
{
    /**
     * @var array
     */
    private $recipient_allow_list;

    /**
     * @var string
     */
    private $recipient;

    /**
     * @var string|null
     */
    private $sender_name;

    /**
     * @var string|null
     */
    private $sender_email;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string
     */
    private $file = '';

    /**
     * FormProcessor constructor.
     */
    public function __construct()
    {
        $this->recipient_allow_list = $this->getAllowList();
    }

    /**
     * @return array
     */
    private function getAllowList(): array
    {
        $list = config('formgate.allowed_recipients');

        return empty($list) ? [] : explode(',', $list);
    }

    /**
     * @param string $recipient
     */
    public function setRecipient(string $recipient): void
    {
        if (! in_array($recipient, $this->recipient_allow_list)) {
            throw new \InvalidArgumentException('The $recipient is not on the recipient allow list.');
        }

        $this->recipient = $recipient;
    }

    /**
     * @param string|null $sender_name
     */
    public function setSenderName(?string $sender_name): void
    {
        $this->sender_name = $sender_name;
    }

    /**
     * @param string|null $sender_email
     */
    public function setSenderEmail(?string $sender_email): void
    {
        if (! filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The $sender_email must be a valid email address.');
        }

        $this->sender_email = $sender_email;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param string $path
     */
    public function setFile(string $path)
    {
        $this->file = $path;
    }

    /**
     * @return string|null
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @return string|null
     */
    public function getSenderName(): ?string
    {
        return $this->sender_name ?: config('mail.from.name');
    }

    /**
     * @return string|null
     */
    public function getSenderEmail(): ?string
    {
        return $this->sender_email;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function buildMessage(): string
    {
        $message_fields = array_map(function ($value, $name) {
            return $name.':'.PHP_EOL.$value;
        }, $this->fields, array_keys($this->fields));

        return implode(PHP_EOL.PHP_EOL, $message_fields);
    }

    /**
     * @return void
     */
    public function send(): void
    {
        Mail::to($this->recipient)->send(new FormSubmissionMail($this));

        // If there is a file in the processor and the email
        // has sent we delete the file from storage
        if (! empty($this->getFile())) {
            Storage::disk('local')->delete($this->getFile());
        }
    }
}
