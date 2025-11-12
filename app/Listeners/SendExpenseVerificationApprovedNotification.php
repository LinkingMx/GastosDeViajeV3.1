<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationApprovedEvent;
use App\Mail\ExpenseVerificationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationApprovedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationApprovedEvent $event): void
    {
        // Send email to the creator
        Mail::to($event->verification->creator->email)
            ->send(new ExpenseVerificationApproved($event->verification));
    }
}
