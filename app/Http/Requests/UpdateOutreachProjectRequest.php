<?php

namespace App\Http\Requests;

class UpdateOutreachProjectRequest extends StoreOutreachProjectRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['outreach_program_id'][0] = 'sometimes';
        $rules['college_id'][0] = 'sometimes';
        $rules['title'][0] = 'sometimes';

        return $rules;
    }
}
