Formgate is a self-hosted solution for handling contact forms on your static websites. You just need to host a single instance of Formgate to handle the contact forms for all of your static websites.

Master: [![Build Status](https://travis-ci.org/formgate/formgate.svg?branch=master)](https://travis-ci.org/formgate/formgate)

Develop: [![Build Status](https://api.travis-ci.org/formgate/formgate.svg?branch=develop)](https://travis-ci.org/formgate/formgate)

# Requirements

* PHP 7.2
* Web server supporting PHP
* Composer

# Installation

1. Host this repo on your web server (e.g. forms.yourdomain.com), ensuring the `public` directory is configured as your document root.
2. Run `composer install` to download dependencies.
3. Copy `.env.example` to `.env` and configure.

# Usage

Send a POST request to `https://forms.yourdomain.com/send` with the following parameters:

| Name              | Value                                                                             |
| ------------------| ----------------------------------------------------------------------------------|
| _recipient        | The recipient email address for this contact form.                                |
| _redirect_success | The URL to redirect to after a successful form submission. (optional)             |
| _sender_name      | The sender name for this contact form. (optional)                                 |
| _sender_email     | The sender email address for this contact form. (optional)                        |
| _subject          | The subject line for this contact form. (optional)                                |
| _hp_email         | If this field is filled in then the a 422 error will be returned. (optional)      |

**Important:** The `_recipient` must be added to the allow list in your `.env` file to be valid.

The `_hp_email` field acts as a honeypot field to prevent spam submissions.

You can include any other parameters to be included in the generated email.

Example form:

```
<form action="https://forms.yourdomain.com/send" method="POST">
  <input type="hidden" name="_recipient" value="someone@clientwebsite.com">
  <input type="hidden" name="_redirect_success" value="https://clientwebsite.com/success/">
  <input type="text" name="_sender_name">
  <input type="email" name="_sender_email">
  <input type="text" name="_subject">
  <textarea name="Message"></textarea>
  <input type="submit" value="Send">
</form>
```

# Roadmap

It's early days for the project but in future we would like to add the following functionality:

* Support for file attachments.
* A UI to configure forms and email settings.
* The option to store forms in a database and view the submissions later.
* Support for granting third party access to log in and view submissions for certain forms.
* An API to submit forms using JavaScript with inline captcha.

# Why use Formgate?

With various hosted solutions available, you might be wondering what the purpose of Formgate is. The project has the following benefits:

* Free to use for any number of contact forms or submissions.
* Complete control over your data and what jurisdiction it's processed in.
* Can be installed quickly on a low cost shared hosting account to handle the contact forms for _all_ of your static websites.

## License

This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
