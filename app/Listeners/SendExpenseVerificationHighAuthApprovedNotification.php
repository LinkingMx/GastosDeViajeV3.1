<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationHighAuthApprovedEvent;
use App\Mail\ExpenseVerificationHighAuthApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationHighAuthApprovedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationHighAuthApprovedEvent $event): void
    {
        // Send email to the creator
        Mail::to($event->verification->creator->email)
            ->send(new ExpenseVerificationHighAuthApproved($event->verification));
    }
}
