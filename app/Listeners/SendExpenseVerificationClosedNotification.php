<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationClosedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExpenseVerificationClosedNotification
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
    public function handle(ExpenseVerificationClosedEvent $event): void
    {
        //
    }
}
