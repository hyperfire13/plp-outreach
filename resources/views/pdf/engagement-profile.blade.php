<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Engagement Profile</title>
    <style>
        @page { margin: 28px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #166534; font-size: 20px; margin: 0 0 4px; }
        .meta { color: #6b7280; margin-bottom: 16px; }
        .summary { margin-bottom: 16px; width: 100%; }
        .summary td { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 9px; width: 25%; }
        .summary strong { color: #166534; display: block; font-size: 17px; }
        table.records { border-collapse: collapse; width: 100%; }
        .records th { background: #166534; color: white; padding: 7px; text-align: left; }
        .records td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        .records tr:nth-child(even) { background: #f9fafb; }
        .footer { color: #6b7280; margin-top: 12px; text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $user->full_name ?: 'Engagement Profile' }}</h1>
    <div class="meta">
        {{ $user->college?->name ?? 'No college assigned' }} &middot;
        {{ str($user->role?->name ?? 'No role')->replace('_', ' ')->title() }}
        @if(filled($filters['year'] ?? null)) &middot; Year {{ $filters['year'] }} @endif
        @if(filled($filters['engagement_type'] ?? null)) &middot; {{ str($filters['engagement_type'])->replace('_', ' ')->title() }} @endif
        @if(filled($filters['sdg'] ?? null)) &middot; {{ str($filters['sdg'])->replace('_', ' ')->upper() }} @endif
    </div>

    <table class="summary"><tr>
        <td><strong>{{ $summary['total_engagements'] }}</strong>Verified Engagements</td>
        <td><strong>{{ number_format($summary['total_service_hours'], 2) }}</strong>Service Hours</td>
        <td><strong>{{ $summary['communities_served'] }}</strong>Communities Served</td>
        <td><strong>{{ $summary['projects_joined'] }}</strong>Projects Joined</td>
    </tr></table>

    <table class="records">
        <thead><tr><th>Activity</th><th>Date</th><th>Role</th><th>Type / SDG</th><th>Project</th><th>Community</th><th>Hours</th></tr></thead>
        <tbody>
        @forelse($engagements as $record)
            <tr>
                <td><strong>{{ $record->title }}</strong><br>{{ $record->description }}</td>
                <td>{{ $record->activity_date?->format('M d, Y') }}</td>
                <td>{{ $record->participation_role }}</td>
                <td>{{ str($record->engagement_type)->replace('_', ' ')->title() }}<br>{{ $record->sdg ? str($record->sdg)->replace('_', ' ')->upper() : '-' }}</td>
                <td>{{ $record->project?->title ?? '-' }}</td>
                <td>{{ $record->community?->name ?? '-' }}</td>
                <td>{{ number_format((float) $record->service_hours, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center">No verified engagement records found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="footer">Generated {{ $generated_at->format('M d, Y g:i A') }}</div>
</body>
</html>
