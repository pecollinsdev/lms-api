<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Default Laravel event: send email verification on user registration
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Example custom event for LMS grading workflow:
        // \App\Events\SubmissionGraded::class => [
        //     \App\Listeners\NotifyStudentOfGrade::class,
        // ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        // Additional manual event registrations can go here
    }

    /**
     * Determine if events and listeners should be auto-discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
