<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationEscalatedEvent;
use App\Mail\ExpenseVerificationEscalated;
use App\Models\GeneralSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationEscalatedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationEscalatedEvent $event): void
    {
        // Send email to the Autorizador Mayor
        $settings = GeneralSetting::get();

        if ($settings->autorizadorMayor) {
            Mail::to($settings->autorizadorMayor->email)
                ->send(new ExpenseVerificationEscalated($event->verification));
        }
    }
}
