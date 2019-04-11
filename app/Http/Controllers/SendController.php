<?php

namespace App\Http\Controllers;

use App\FormProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Validator;
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
     * @return RedirectResponse|Redirector
     */
    public function handle()
    {
        // Abort the request if there is a filled in _hp_email field
        abort_if(!empty(request('_hp_email')), 422);

        $data = request()->except('file');

        // If the request has a field called file we assume it is
        // the path to a stored file
        if (request()->has('file')) {
            $data['file'] = request('file');
            $this->processor->setFile(request('file'));
        }

        // However if they have uploaded a file then we will store
        // the file, restore the path & append it to the $data variable
        // which is used in the recaptcha form
        if (request()->hasFile('file')) {
            $path = $this->fileUpload(request('file'));
            $this->processor->setFile($path);
            $data['file'] = $path;
        }

        if ($this->showRecaptchaPage()) {
            return view('recaptcha', [
                'request' => $data,
                'failed' => request()->has('g-recaptcha-response'),
            ]);
        }

        return $this->submit();
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

    private function fileUpload($file)
    {
        $data = [$file];
        $validator = Validator::make($data, [
            'file' => 'file',
        ]);

        if (!$validator->fails()) {
            $file = request()->file('file');
            $path = $file->store('');
        }

        return $path ?? null;
    }
}
