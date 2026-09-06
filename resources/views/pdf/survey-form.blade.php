<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $surveyTemplate->title }} - Blank Survey Form</title>
    <style>
        @page { margin: 38px 42px 46px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.35; }
        h1 { color: #166534; font-size: 19px; margin: 0 0 3px; text-align: center; }
        .institution { font-size: 11px; font-weight: bold; text-align: center; }
        .subtitle { color: #6b7280; margin-bottom: 15px; text-align: center; }
        .description { background: #f0fdf4; border: 1px solid #bbf7d0; margin-bottom: 14px; padding: 8px; }
        .information { border-collapse: collapse; margin-bottom: 16px; width: 100%; }
        .information td { border: 1px solid #9ca3af; height: 24px; padding: 5px; width: 50%; }
        .section { page-break-inside: avoid; }
        .section-title { background: #166534; color: white; font-size: 12px; margin: 12px 0 0; padding: 6px 8px; }
        .question { border: 1px solid #d1d5db; border-top: 0; page-break-inside: avoid; padding: 8px; }
        .question-text { font-weight: bold; margin-bottom: 4px; }
        .required { color: #b91c1c; }
        .help { color: #6b7280; font-size: 9px; margin-bottom: 5px; }
        .line { border-bottom: 1px solid #6b7280; height: 17px; }
        .answer-box { border: 1px solid #9ca3af; height: 58px; margin-top: 5px; }
        .option { display: inline-block; margin: 3px 18px 3px 0; }
        .mark { border: 1px solid #374151; display: inline-block; height: 9px; margin-right: 5px; vertical-align: -1px; width: 9px; }
        .date-box span { border-bottom: 1px solid #6b7280; display: inline-block; margin-right: 7px; width: 45px; }
        .recommendation { page-break-inside: avoid; }
        .footer { bottom: 15px; color: #6b7280; font-size: 8px; position: fixed; right: 42px; }
    </style>
</head>
<body>
    <div class="institution">PAMANTASAN NG LUNGSOD NG PASIG</div>
    <h1>{{ $surveyTemplate->title }}</h1>
    <div class="subtitle">Blank Community Survey Form &middot; Version {{ $surveyTemplate->version }}</div>

    @if($surveyTemplate->description)
        <div class="description">{{ $surveyTemplate->description }}</div>
    @endif

    <table class="information">
        <tr><td><strong>Community:</strong></td><td><strong>Survey Date:</strong></td></tr>
        <tr><td><strong>Academic Department:</strong></td><td><strong>Conducted By:</strong></td></tr>
        <tr><td colspan="2"><strong>Respondent / Community Representative:</strong></td></tr>
    </table>

    @forelse($questionsBySection as $section => $questions)
        <div class="section">
            <div class="section-title">{{ $section }}</div>
            @foreach($questions as $index => $question)
                <div class="question">
                    <div class="question-text">
                        {{ $loop->parent->index + 1 }}.{{ $index + 1 }} {{ $question->question }}
                        @if($question->is_required)<span class="required">*</span>@endif
                    </div>
                    @if($question->help_text)<div class="help">{{ $question->help_text }}</div>@endif

                    @if(in_array($question->question_type, ['select', 'radio', 'checkbox'], true))
                        @foreach($question->options ?? [] as $option)
                            <span class="option"><span class="mark"></span>{{ $option }}</span>
                        @endforeach
                    @elseif($question->question_type === 'boolean')
                        <span class="option"><span class="mark"></span>Yes</span>
                        <span class="option"><span class="mark"></span>No</span>
                    @elseif($question->question_type === 'textarea')
                        <div class="answer-box"></div>
                    @elseif($question->question_type === 'date')
                        <div class="date-box"><span></span>/<span></span>/<span></span></div>
                    @else
                        <div class="line"></div>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="description">This template currently has no active questions.</div>
    @endforelse

    <div class="recommendation">
        <div class="section-title">Priority Needs</div>
        <div class="question"><div class="answer-box"></div></div>
        <div class="section-title">Suggested Outreach Program</div>
        <div class="question"><div class="answer-box"></div></div>
        <div class="section-title">Remarks</div>
        <div class="question"><div class="answer-box"></div></div>
    </div>

    <div class="footer">Generated {{ $generatedAt->format('M d, Y g:i A') }} &middot; * Required question</div>
</body>
</html>
