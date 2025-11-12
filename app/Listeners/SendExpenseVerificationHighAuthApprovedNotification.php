<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationHighAuthApprovedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendExpenseVerificationHighAuthApprovedNotification
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
    public function handle(ExpenseVerificationHighAuthApprovedEvent $event): void
    {
        //
    }
}
