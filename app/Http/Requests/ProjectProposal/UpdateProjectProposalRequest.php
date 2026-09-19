<?php

namespace App\Http\Requests\ProjectProposal;

class UpdateProjectProposalRequest extends StoreProjectProposalRequest
{
    public function authorize(): bool
    {
        $proposal = $this->route('project_proposal');

        return $proposal && ($this->user()?->can('update', $proposal) ?? false);
    }
}
