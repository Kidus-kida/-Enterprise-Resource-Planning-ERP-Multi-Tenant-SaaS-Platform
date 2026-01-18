<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\Menu\Laravel\Menu;
use App\Helpers\AppMenu;

class AppSuperadminMenuEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $menu;

    /**
     * Create a new event instance.
     */
    public function __construct(\App\Helpers\AppMenu $menu)
    {
        $this->menu = $menu->get();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
