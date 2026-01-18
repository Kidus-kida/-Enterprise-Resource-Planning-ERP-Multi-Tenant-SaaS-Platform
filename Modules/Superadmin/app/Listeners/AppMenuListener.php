<?php

namespace Modules\Superadmin\Listeners;

use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserType;
class AppMenuListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppMenuEvent $event): void
    {
        // NOTE: Superadmin menu items have been moved to app/Listeners/AppMenuListener.php
        // to ensure they appear at the top of the sidebar (after Dashboard)
        // This listener is kept for potential future use but currently does nothing
        
        // All Superadmin menu items are now rendered in:
        // app/Listeners/AppMenuListener.php (lines ~115-185)
        
        return;
    }
}
