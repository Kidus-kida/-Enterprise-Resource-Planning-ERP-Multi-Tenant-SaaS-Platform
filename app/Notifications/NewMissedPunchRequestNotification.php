<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\MissedPunchRequest;

class NewMissedPunchRequestNotification extends Notification
{
    use Queueable;

    protected $request;

    /**
     * Create a new notification instance.
     */
    public function __construct(MissedPunchRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'employee_name' => $this->request->user->full_name,
            'date' => $this->request->date->format('Y-m-d'),
            'type' => $this->request->punch_type,
            'message' => __('New missed punch request from :employee for :date.', [
                'employee' => $this->request->user->full_name,
                'date' => $this->request->date->format('M d, Y')
            ]),
            'action_url' => route('admin.missed-punches.index'),
        ];
    }
}
