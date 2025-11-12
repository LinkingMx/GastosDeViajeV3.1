<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationClosedEvent;
use App\Mail\ExpenseVerificationClosed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationClosedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationClosedEvent $event): void
    {
        // Send email to the creator
        Mail::to($event->verification->creator->email)
            ->send(new ExpenseVerificationClosed($event->verification));
    }
}
