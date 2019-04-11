Sender Name: {{ $processor->getSenderName() }}
Sender Email: {{ $processor->getSenderEmail() ?: 'Not Entered / Invalid' }}
{{ $processor->buildMessage() }}
