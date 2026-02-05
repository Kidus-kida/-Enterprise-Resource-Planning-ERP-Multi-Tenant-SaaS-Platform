<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MissedPunchRequest;

class MissedPunchStatusNotification extends Notification
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
            'status' => $this->request->status,
            'date' => $this->request->date->format('Y-m-d'),
            'type' => $this->request->punch_type,
            'message' => __('Your missed punch request for :date has been :status.', [
                'date' => $this->request->date->format('M d, Y'),
                'status' => __($this->request->status)
            ]),
            'action_url' => route('missed-punches.index'),
        ];
    }
}
