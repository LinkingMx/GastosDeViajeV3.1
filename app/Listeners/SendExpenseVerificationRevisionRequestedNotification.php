<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationRevisionRequestedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExpenseVerificationRevisionRequestedNotification
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
    public function handle(ExpenseVerificationRevisionRequestedEvent $event): void
    {
        //
    }
}
