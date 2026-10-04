<?php

namespace App\Services;

use App\Models\ProjectApplicationTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectApplicationTemplateService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        return ProjectApplicationTemplate::query()
            ->with('creator:id,first_name,middle_name,last_name')
            ->withCount('responses')
            ->search($filters['search'] ?? null)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['phase'] ?? null, fn ($query, $phase) => $query->where('phase', $phase))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(min(max((int) ($filters['per_page'] ?? 10), 1), 100));
    }

    public function store(array $data, int $userId): ProjectApplicationTemplate
    {
        $data['schema'] = $this->normalizeSchema($data['schema']);
        $data['created_by'] = $userId;
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        return DB::transaction(fn () => ProjectApplicationTemplate::create($data));
    }

    public function update(ProjectApplicationTemplate $template, array $data): ProjectApplicationTemplate
    {
        $data['schema'] = $this->normalizeSchema($data['schema']);

        if ($template->responses()->exists() && $this->changesSchemaIdentity($template, $data)) {
            throw ValidationException::withMessages([
                'template' => ['The code, version, phase, and schema cannot be changed after responses exist. Create a new version instead.'],
            ]);
        }

        if ($data['status'] === 'published' && ! $template->published_at) {
            $data['published_at'] = now();
        } elseif ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        return DB::transaction(function () use ($template, $data) {
            $template->update($data);

            return $template->refresh()->loadCount('responses');
        });
    }

    public function delete(ProjectApplicationTemplate $template): void
    {
        if ($template->responses()->exists()) {
            throw ValidationException::withMessages([
                'template' => ['This template has proposal responses and cannot be deleted. Archive it instead.'],
            ]);
        }

        $template->delete();
    }

    private function changesSchemaIdentity(ProjectApplicationTemplate $template, array $data): bool
    {
        return $template->code !== $data['code']
            || $template->version !== (int) $data['version']
            || $template->phase !== $data['phase']
            || $template->schema !== $data['schema'];
    }

    private function normalizeSchema(array $schema): array
    {
        $optionBasedTypes = ['select', 'radio', 'checkbox_group'];

        return [
            'sections' => collect($schema['sections'])
                ->map(fn (array $section) => [
                    'title' => trim($section['title']),
                    'fields' => collect($section['fields'])
                        ->map(function (array $field) use ($optionBasedTypes) {
                            $normalized = [
                                'key' => trim($field['key']),
                                'label' => trim($field['label']),
                                'type' => $field['type'],
                                'required' => (bool) ($field['required'] ?? false),
                            ];

                            if (in_array($field['type'], $optionBasedTypes, true)) {
                                $normalized['options'] = collect($field['options'] ?? [])
                                    ->map(fn ($option) => trim($option))
                                    ->values()
                                    ->all();
                            }

                            if (filled($field['help_text'] ?? null)) {
                                $normalized['help_text'] = trim($field['help_text']);
                            }

                            return $normalized;
                        })
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ];
    }
}
