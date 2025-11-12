<?php

namespace App\Listeners;

use App\Events\ExpenseVerificationSubmittedEvent;
use App\Mail\ExpenseVerificationSubmitted;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendExpenseVerificationSubmittedNotification implements ShouldQueue
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
    public function handle(ExpenseVerificationSubmittedEvent $event): void
    {
        // Send email to all Travel Team members (using travel_team boolean field)
        $travelTeamMembers = User::where('travel_team', true)->get();

        foreach ($travelTeamMembers as $member) {
            Mail::to($member->email)
                ->send(new ExpenseVerificationSubmitted($event->verification));
        }
    }
}
