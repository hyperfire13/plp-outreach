<?php

namespace App\Http\Requests\ProjectProposal;

use App\Models\ProjectProposal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ProjectProposal::class) ?? false;
    }

    public function rules(): array
    {
        $text = ['required', 'string', 'max:10000'];

        return [
            'priority_need_id' => ['required', 'integer', Rule::exists('priority_needs', 'id')->where('status', 'validated')],
            'title' => ['required', 'string', 'max:255'], 'rationale' => $text, 'objectives' => $text, 'beneficiaries' => $text,
            'expected_outputs' => $text, 'expected_outcomes' => $text, 'sustainability_plan' => $text, 'risk_assessment' => $text,
            'monitoring_indicators' => $text, 'sdg_alignment' => $text, 'development_plan_alignment' => $text,
            'partner_involvement' => ['nullable', 'string', 'max:10000'], 'proposed_budget' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'resources' => ['sometimes', 'array', 'max:100'], 'resources.*.name' => ['required', 'string', 'max:255'], 'resources.*.description' => ['nullable', 'string', 'max:2000'], 'resources.*.quantity' => ['required', 'numeric', 'min:0.01'], 'resources.*.estimated_cost' => ['required', 'numeric', 'min:0'],
            'workplans' => ['sometimes', 'array', 'max:100'], 'workplans.*.activity' => ['required', 'string', 'max:255'], 'workplans.*.expected_output' => ['nullable', 'string', 'max:2000'], 'workplans.*.responsible_person' => ['nullable', 'string', 'max:255'], 'workplans.*.start_date' => ['required', 'date'], 'workplans.*.end_date' => ['required', 'date', 'after_or_equal:workplans.*.start_date'], 'workplans.*.estimated_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
