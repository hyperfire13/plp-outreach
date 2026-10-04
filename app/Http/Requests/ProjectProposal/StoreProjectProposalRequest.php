<?php

namespace App\Http\Requests\ProjectProposal;

use App\Models\ProjectApplicationTemplate;
use App\Models\ProjectProposal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'template_responses' => ['required', 'array', 'min:1', 'max:20'],
            'template_responses.*.project_application_template_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('project_application_templates', 'id')->where('status', 'published'),
            ],
            'template_responses.*.response_data' => ['required', 'array', 'max:200'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $responses = collect($this->input('template_responses', []));
            $templates = ProjectApplicationTemplate::query()
                ->published()
                ->whereIn('id', $responses->pluck('project_application_template_id'))
                ->get()
                ->keyBy('id');

            $requiredTemplateIds = ProjectApplicationTemplate::query()
                ->published()
                ->where('is_required', true)
                ->pluck('id');

            if ($requiredTemplateIds->diff($templates->keys())->isNotEmpty()) {
                $validator->errors()->add(
                    'template_responses',
                    'All required project application templates must be included.'
                );
            }

            foreach ($responses as $responseIndex => $response) {
                $template = $templates->get((int) ($response['project_application_template_id'] ?? 0));

                if (! $template) {
                    continue;
                }

                $data = $response['response_data'] ?? [];
                $fields = collect($template->schema['sections'] ?? [])
                    ->flatMap(fn (array $section) => $section['fields'] ?? []);

                foreach ($fields as $field) {
                    $key = $field['key'];
                    $value = $data[$key] ?? null;
                    $path = "template_responses.{$responseIndex}.response_data.{$key}";

                    if ($template->is_required && ($field['required'] ?? false) && blank($value)) {
                        $validator->errors()->add($path, "The {$field['label']} field is required.");

                        continue;
                    }

                    if (blank($value)) {
                        continue;
                    }

                    if (($field['type'] ?? null) === 'number' && ! is_numeric($value)) {
                        $validator->errors()->add($path, "The {$field['label']} field must be a number.");
                    }

                    if (($field['type'] ?? null) === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                        $validator->errors()->add($path, "The {$field['label']} field must be a valid email address.");
                    }

                    if (($field['type'] ?? null) === 'checkbox_group' && ! is_array($value)) {
                        $validator->errors()->add($path, "The {$field['label']} field must be a list.");
                    }

                    if (is_string($value) && mb_strlen($value) > 10000) {
                        $validator->errors()->add($path, "The {$field['label']} field must not exceed 10,000 characters.");
                    }
                }
            }
        }];
    }
}
