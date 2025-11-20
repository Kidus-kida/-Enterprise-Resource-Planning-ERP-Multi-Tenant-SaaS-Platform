<?php

namespace Modules\Project\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Project\Models\Task;

class UserAssignedToTask extends Mailable
{
    use Queueable, SerializesModels;

    private $task;
    private $user;

    /**
     * Create a new message instance.
     */
    public function __construct(Task $task, User $user)
    {
        $this->task = $task;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been assigned a new task',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'project::pages.tasks.mail.user-assigned',
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'url' => route('tasks.show', $this->task->id),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
