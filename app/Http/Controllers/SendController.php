<?php

namespace App\Http\Controllers;

use App\FormProcessor;
use InvalidArgumentException;
use ReCaptcha\ReCaptcha;

class SendController extends Controller
{
    /**
     * @var FormProcessor
     */
    private $processor;

    /**
     * @param FormProcessor $processor
     */
    public function __construct(FormProcessor $processor)
    {
        $this->processor = $processor;
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
            '_token',
            'g-recaptcha-response',
            'file',
        ]);
    }

    /**
     * Determines if the Recaptcha page should be presented to the user.
     * @return bool
     */
    private function showRecaptchaPage(): bool
    {
        if (! config('formgate.recaptcha.enabled')) {
            return false;
        }

        if (! request('g-recaptcha-response')) {
            return true;
        }

        $recaptcha = new ReCaptcha(config('formgate.recaptcha.secret_key'));
        $response = $recaptcha->verify(request('g-recaptcha-response'), request()->getClientIp());
        return ! $response->isSuccess();
    }

    /**
     * @return \Illuminate\View\View
     */
    private function getRecaptchaResponse()
    {
        $data = request()->except(['_token', 'g-recaptcha-response']);
        $data['file'] = $this->getFilePath();

        return view('recaptcha', [
            'request' => $data,
            'failed' => request()->has('g-recaptcha-response'),
        ]);
    }

    /**
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        // Abort the request if there is a filled in _hp_email field
        abort_if(!empty(request('_hp_email')), 422);

        if ($this->showRecaptchaPage()) {
            return $this->getRecaptchaResponse();
        }

        try {
            $this->processor->setSenderName(request('_sender_name'));
            $this->processor->setSenderEmail(request('_sender_email'));
        } catch (InvalidArgumentException $e) {
            // For now, there's no way to handle end user errors but any data should still be submitted.
            // To ensure a valid sender email is set, use <input type="email"> in your form.
        }

        $this->processor->setRecipient(request('_recipient'));
        $this->processor->setSubject(request('_subject', 'Contact form submission'));
        $this->processor->setFields($this->getFields());
        $this->processor->setFile($this->getFilePath());
        $this->processor->send();

        return redirect(request('_redirect_success', 'thanks'));
    }

    /**
     * Get the path to a user uploaded file. This is either a file uploaded during
     * the current request, or the path to a previously uploaded file.
     * @return string|null
     */
    private function getFilePath(): ?string
    {
        if (request()->hasFile('file')) {
            return request()->file('file')->store('');
        }

        return request('file');
    }
}
