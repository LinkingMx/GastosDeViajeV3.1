<?php

namespace App\Providers;

use App\Events\ExpenseVerificationApprovedEvent;
use App\Events\ExpenseVerificationClosedEvent;
use App\Events\ExpenseVerificationEscalatedEvent;
use App\Events\ExpenseVerificationHighAuthApprovedEvent;
use App\Events\ExpenseVerificationRejectedEvent;
use App\Events\ExpenseVerificationRevisionRequestedEvent;
use App\Events\ExpenseVerificationSubmittedEvent;
use App\Events\TravelRequestCreated;
use App\Listeners\SendExpenseVerificationApprovedNotification;
use App\Listeners\SendExpenseVerificationClosedNotification;
use App\Listeners\SendExpenseVerificationEscalatedNotification;
use App\Listeners\SendExpenseVerificationHighAuthApprovedNotification;
use App\Listeners\SendExpenseVerificationRejectedNotification;
use App\Listeners\SendExpenseVerificationRevisionRequestedNotification;
use App\Listeners\SendExpenseVerificationSubmittedNotification;
use App\Listeners\SendTravelRequestCreatedNotifications;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TravelRequestCreated::class => [
            SendTravelRequestCreatedNotifications::class,
        ],

        // ExpenseVerification workflow events
        ExpenseVerificationSubmittedEvent::class => [
            SendExpenseVerificationSubmittedNotification::class,
        ],
        ExpenseVerificationApprovedEvent::class => [
            SendExpenseVerificationApprovedNotification::class,
        ],
        ExpenseVerificationRejectedEvent::class => [
            SendExpenseVerificationRejectedNotification::class,
        ],
        ExpenseVerificationEscalatedEvent::class => [
            SendExpenseVerificationEscalatedNotification::class,
        ],
        ExpenseVerificationHighAuthApprovedEvent::class => [
            SendExpenseVerificationHighAuthApprovedNotification::class,
        ],
        ExpenseVerificationClosedEvent::class => [
            SendExpenseVerificationClosedNotification::class,
        ],
        ExpenseVerificationRevisionRequestedEvent::class => [
            SendExpenseVerificationRevisionRequestedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}