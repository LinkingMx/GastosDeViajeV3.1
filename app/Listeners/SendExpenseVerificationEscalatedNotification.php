<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationEscalatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExpenseVerificationEscalatedNotification
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
    public function handle(ExpenseVerificationEscalatedEvent $event): void
    {
        //
    }
}
