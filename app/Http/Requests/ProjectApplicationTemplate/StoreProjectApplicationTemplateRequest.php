<?php

namespace App\Http\Requests\ProjectApplicationTemplate;

use App\Models\ProjectApplicationTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProjectApplicationTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role_name, config('role_access.proposal_template_managers', []), true);
    }

    public function rules(): array
    {
        $templateId = $this->route('project_application_template')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9_\-]+$/',
                Rule::unique('project_application_templates')->where(
                    fn ($query) => $query->where('version', $this->integer('version'))
                )->ignore($templateId),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'version' => ['required', 'integer', 'min:1', 'max:999'],
            'phase' => ['required', Rule::in(ProjectApplicationTemplate::PHASES)],
            'status' => ['required', Rule::in(ProjectApplicationTemplate::STATUSES)],
            'is_required' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'schema' => ['required', 'array'],
            'schema.sections' => ['required', 'array', 'min:1', 'max:30'],
            'schema.sections.*.title' => ['required', 'string', 'max:255'],
            'schema.sections.*.fields' => ['required', 'array', 'min:1', 'max:100'],
            'schema.sections.*.fields.*.key' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/'],
            'schema.sections.*.fields.*.label' => ['required', 'string', 'max:255'],
            'schema.sections.*.fields.*.type' => ['required', Rule::in([
                'text', 'email', 'number', 'date', 'time', 'textarea', 'select',
                'radio', 'checkbox', 'checkbox_group',
            ])],
            'schema.sections.*.fields.*.required' => ['sometimes', 'boolean'],
            'schema.sections.*.fields.*.options' => ['sometimes', 'array', 'max:100'],
            'schema.sections.*.fields.*.options.*' => ['required', 'string', 'max:255'],
            'schema.sections.*.fields.*.help_text' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $usedKeys = [];
            $optionBasedTypes = ['select', 'radio', 'checkbox_group'];

            foreach ($this->input('schema.sections', []) as $sectionIndex => $section) {
                foreach ($section['fields'] ?? [] as $fieldIndex => $field) {
                    $basePath = "schema.sections.{$sectionIndex}.fields.{$fieldIndex}";
                    $key = $field['key'] ?? null;
                    $type = $field['type'] ?? null;
                    $options = $field['options'] ?? [];

                    if ($key && isset($usedKeys[$key])) {
                        $validator->errors()->add(
                            "{$basePath}.key",
                            'Field keys must be unique across the entire template.'
                        );
                    }

                    if ($key) {
                        $usedKeys[$key] = true;
                    }

                    if (in_array($type, $optionBasedTypes, true) && count($options) < 1) {
                        $validator->errors()->add(
                            "{$basePath}.options",
                            'Add at least one choice option for this field type.'
                        );
                    }

                    $normalizedOptions = array_map(
                        fn ($option) => mb_strtolower(trim((string) $option)),
                        $options
                    );

                    if (count($normalizedOptions) !== count(array_unique($normalizedOptions))) {
                        $validator->errors()->add(
                            "{$basePath}.options",
                            'Choice options must be unique.'
                        );
                    }
                }
            }
        }];
    }
}
