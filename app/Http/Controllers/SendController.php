<?php

namespace App\Http\Controllers;

class SendController extends Controller
{
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle()
    {
        // Handle form submission.
        return redirect('thanks');
    }
}
