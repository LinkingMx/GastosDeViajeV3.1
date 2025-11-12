<?php

namespace App\Events;

use App\Models\ExpenseVerification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseVerificationApprovedEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ExpenseVerification $verification
    ) {
        //
    }
}
