<?php

namespace App\Events;

use App\Models\TravelRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TravelRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TravelRequest $travelRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }
}