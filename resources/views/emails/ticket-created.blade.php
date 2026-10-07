<h2>[{{ $ticket->uid }}] {{ $ticket->subject }}</h2>

<p>Halo,</p>

<p>Tiket Anda telah kami terima dengan rincian:</p>

<ul>
    <li><strong>ID:</strong> {{ $ticket->uid }}</li>
    <li><strong>Status:</strong> {{ $ticket->status instanceof \BackedEnum ? $ticket->status->value : $ticket->status }}</li>
    <li><strong>Prioritas:</strong> {{ ucfirst($ticket->priority ?? 'medium') }}</li>
</ul>

<p>Anda dapat membalas email ini untuk menambahkan informasi. Balasan akan otomatis tercatat pada tiket yang sama.</p>

<p>Terima kasih,<br>{{ config('app.name') }}</p>
