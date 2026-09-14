<h1>New website enquiry</h1>
<p><strong>Name:</strong> {{ $submission->name }}</p>
<p><strong>Email:</strong> {{ $submission->email }}</p>
<p><strong>Subject:</strong> {{ $submission->subject ?: '—' }}</p>
<p>{{ $submission->message }}</p>
