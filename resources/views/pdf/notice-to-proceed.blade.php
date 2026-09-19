<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px;line-height:1.6;color:#222}h1{text-align:center;font-size:20px}.meta{margin:28px 0}.signature{margin-top:70px}</style></head><body>
<h1>NOTICE TO PROCEED</h1>
<div class="meta"><strong>NTP No.:</strong> {{ $proposal->noticeToProceed->ntp_number }}<br><strong>Date:</strong> {{ $proposal->noticeToProceed->issued_at->format('F j, Y') }}<br><strong>Proposal:</strong> {{ $proposal->proposal_number }}</div>
<p>Dear {{ $proposal->applicant->full_name }},</p>
<p>This formally authorizes implementation of <strong>{{ $proposal->title }}</strong>, responding to the validated community need “{{ $proposal->priorityNeed->need }}” in {{ $proposal->community->name }}.</p>
<p>The project must follow the approved proposal, workplan, budget, institutional policies, and monitoring requirements.</p>
<div class="signature"><strong>{{ $proposal->noticeToProceed->issuer->full_name }}</strong><br>CALO Authorized Representative</div>
</body></html>
