<h2>[{{ $ticket->uid }}] {{ $ticket->subject }}</h2>

<p>Halo,</p>

<p><strong>{{ $author }}</strong> membalas tiket Anda:</p>

<blockquote>{{ $reply->body }}</blockquote>

<p>Anda dapat membalas email ini untuk melanjutkan percakapan. Balasan akan otomatis tercatat pada tiket yang sama.</p>

<p>Terima kasih,<br>{{ config('app.name') }}</p>
