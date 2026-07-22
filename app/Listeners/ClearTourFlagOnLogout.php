<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class ClearTourFlagOnLogout
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
    public function handle(Logout $event): void
    {
        // When user logs out, we want to show the tour again on next login
        // This is done via JavaScript in the tour.blade.php file
        // The localStorage flag will remain, but it resets on new session
        // This listener is here as a placeholder for future server-side logic
    }
}
