<?php

namespace App\Notifications;

use App\Models\ProjectProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectProposalNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly ProjectProposal $proposal, private readonly string $message)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['project_proposal_id' => $this->proposal->id, 'proposal_number' => $this->proposal->proposal_number, 'title' => $this->proposal->title, 'message' => $this->message, 'status' => $this->proposal->status, 'current_step' => $this->proposal->current_step];
    }
}
