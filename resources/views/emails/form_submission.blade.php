Sender Name: {{ $processor->getSenderName() ?: 'Not Entered / Invalid' }}
Sender Email: {{ $processor->getSenderEmail() ?: 'Not Entered / Invalid' }}
{{ $processor->buildMessage() }}
