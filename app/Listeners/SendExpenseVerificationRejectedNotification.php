<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationRejectedEvent;
use App\Mail\ExpenseVerificationRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationRejectedNotification implements ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(ExpenseVerificationRejectedEvent $event): void
    {
        // Send email to the creator
        Mail::to($event->verification->creator->email)
            ->send(new ExpenseVerificationRejected($event->verification));
    }
}
