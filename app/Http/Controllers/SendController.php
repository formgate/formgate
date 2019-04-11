<?php

namespace App\Http\Controllers;

use App\FormProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
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
        ]);
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function handle()
    {
        abort_if(!empty(request('_hp_email')), 422); // Abort the request if there is a filled in _hp_email field

        // If recaptcha is disabled we can just offload to the submit method
        if (!config('formgate.recaptcha.enabled')) {
            return $this->submit();
        }

        // If there is a recaptcha response in the request verify it and if correct process the submission
        if (request()->has('g-recaptcha-response')) {
            $captcha_error = true;
            $recaptcha = new ReCaptcha(config('formgate.recaptcha.secret_key'));
            $response = $recaptcha->verify(request('g-recaptcha-response'), request()->getClientIp());
            if ($response->isSuccess()) {
                return $this->submit();
            }
        }

        // If the recaptcha is enabled and it is not already in the request or it is invalid we show the recaptcha page
        return view('recaptcha', [
            'request' => request()->all(),
            'captcha_error' => $captcha_error ?? false,
        ]);
    }

    /**
     * @return RedirectResponse|Redirector
     */
    private function submit()
    {
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
        $this->processor->send();

        return redirect(request('_redirect_success', 'thanks'));
    }
}
