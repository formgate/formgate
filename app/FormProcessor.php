<?php

namespace App;

use Illuminate\Mail\Mailer;

class FormProcessor
{
    /**
     * @var Mailer
     */
    private $mailer;

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
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
        $this->recipient_allow_list = $this->getAllowList();
    }

    /**
     * @return array
     */
    private function getAllowList(): array
    {
        $list = getenv('RECIPIENT_ALLOW_LIST');
        return empty($list) ? [] : explode(',', $list);
    }

    /**
     * @param string $recipient
     */
    public function setRecipient(string $recipient): void
    {
        if (!in_array($recipient, $this->recipient_allow_list)) {
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
        if (!filter_var($sender_email, FILTER_VALIDATE_EMAIL)) {
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
     * @return string
     */
    private function buildMessage(): string
    {
        // Add the sender name and email address to the fields providing they're not empty.
        $fields = array_merge(array_filter([
            'Sender Name' => $this->sender_name,
            'Sender Email' => $this->sender_email,
        ]), $this->fields);

        $message_fields = array_map(function ($value, $name) {
            return $name . ':' . PHP_EOL . $value;
        }, $fields, array_keys($fields));

        return implode(PHP_EOL . PHP_EOL, $message_fields);
    }

    /**
     * @return void
     */
    public function send(): void
    {
        $from_name = $this->sender_name ?: config('mail.from.name');

        $this->mailer->raw($this->buildMessage(), function ($mail) use ($from_name) {
            /** @var \Illuminate\Mail\Message $mail */
            $mail->to($this->recipient);
            $mail->from(config('mail.from.address'), $from_name);
            $mail->subject($this->subject);

            if ($this->sender_email) {
                $mail->addReplyTo($this->sender_email, $this->sender_name);
            }
        });
    }
}
