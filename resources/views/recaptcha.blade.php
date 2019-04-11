@extends('layout')

@section('title', 'Are you a robot?')

@section('body')
    <h1>Are you a robot?</h1>

    @if($failed)
        <p>
            <strong style="color: red;">You failed the robot check. Please try again.</strong>
        </p>
    @endif

    <form action="{{ url('send') }}" method="post" enctype="multipart/form-data">
        @csrf
        @foreach($request as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
        <div class="g-recaptcha" id="feedback-recaptcha"
             data-sitekey="{{ config('formgate.recaptcha.site_key') }}"></div>
        <button type="submit">Submit</button>
    </form>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection('body')
