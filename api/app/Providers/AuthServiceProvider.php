<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Assignment;
use App\Models\Question;
use App\Models\Option;
use App\Models\Submission;
use App\Models\Answer;
use App\Policies\UserPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\OptionPolicy;
use App\Policies\SubmissionPolicy;
use App\Policies\AnswerPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class       => UserPolicy::class,
        Course::class     => CoursePolicy::class,
        Enrollment::class => EnrollmentPolicy::class,
        Assignment::class => AssignmentPolicy::class,
        Question::class   => QuestionPolicy::class,
        Option::class     => OptionPolicy::class,
        Submission::class => SubmissionPolicy::class,
        Answer::class     => AnswerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
        // You can define additional gates here if needed, for example:
        //
        // Gate::define('grade-submission', function ($user, $submission) {
        //     return $user->id === $submission->assignment->course->instructor_id
        //         || $user->isAdmin();
        // });
    }
}
