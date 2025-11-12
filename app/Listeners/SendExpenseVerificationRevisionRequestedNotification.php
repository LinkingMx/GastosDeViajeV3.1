<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationRevisionRequestedEvent;
use App\Mail\ExpenseVerificationRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationRevisionRequestedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationRevisionRequestedEvent $event): void
    {
        // Send email to the creator - reusing the rejected email template
        Mail::to($event->verification->creator->email)
            ->send(new ExpenseVerificationRejected($event->verification));
    }
}
