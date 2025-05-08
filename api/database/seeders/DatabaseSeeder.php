<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleItem;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Progress;
use App\Models\InstructorCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\AssignmentSubmitted;
use App\Notifications\AssignmentGraded;
use App\Notifications\AssignmentDueSoon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create instructor codes
        InstructorCode::create(['code' => 'INST001', 'is_used' => false]);
        InstructorCode::create(['code' => 'INST002', 'is_used' => false]);

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '1234567890',
            'bio' => 'System administrator with full access to all features.',
        ]);

        // Create instructors
        $instructor1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'phone_number' => '2345678901',
            'bio' => 'Computer Science professor with expertise in web development and programming.',
            'instructor_code' => 'INST001',
            'qualifications' => 'Ph.D. in Computer Science',
            'academic_specialty' => 'Web Development',
        ]);

        $instructor2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'instructor',
            'phone_number' => '3456789012',
            'bio' => 'Mathematics professor specializing in calculus and linear algebra.',
            'instructor_code' => 'INST002',
            'qualifications' => 'Ph.D. in Mathematics',
            'academic_specialty' => 'Pure Mathematics',
        ]);

        // Create courses
        $course1 = Course::create([
            'title' => 'Introduction to Programming',
            'slug' => 'intro-programming',
            'description' => 'Learn the basics of programming with Python',
            'instructor_id' => $instructor1->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_published' => true,
        ]);

        $course2 = Course::create([
            'title' => 'Advanced Web Development',
            'slug' => 'advanced-web-dev',
            'description' => 'Master modern web development techniques',
            'instructor_id' => $instructor1->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
            'is_published' => true,
        ]);

        $course3 = Course::create([
            'title' => 'Calculus I',
            'slug' => 'calculus-1',
            'description' => 'Introduction to differential and integral calculus',
            'instructor_id' => $instructor2->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_published' => true,
        ]);

        // Create modules for Introduction to Programming
        $module1 = Module::create([
            'course_id' => $course1->id,
            'title' => 'Getting Started with Python',
            'description' => 'Introduction to Python programming language and basic concepts',
            'start_date' => now(),
            'end_date' => now()->addWeeks(2),
        ]);

        $module2 = Module::create([
            'course_id' => $course1->id,
            'title' => 'Control Structures and Functions',
            'description' => 'Learn about loops, conditionals, and function definitions',
            'start_date' => now()->addWeeks(2),
            'end_date' => now()->addWeeks(4),
        ]);

        // Create module items for Introduction to Programming
        ModuleItem::create([
            'module_id' => $module1->id,
            'type' => 'video',
            'title' => 'Introduction to Python',
            'description' => 'Overview of Python programming language',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module1->id,
            'type' => 'document',
            'title' => 'Python Installation Guide',
            'description' => 'Step-by-step guide to install Python and set up your development environment',
            'order' => 2,
        ]);

        ModuleItem::create([
            'module_id' => $module1->id,
            'type' => 'quiz',
            'title' => 'Python Basics Quiz',
            'description' => 'Test your understanding of Python fundamentals',
            'due_date' => now()->addWeeks(1),
            'order' => 3,
        ]);

        ModuleItem::create([
            'module_id' => $module2->id,
            'type' => 'video',
            'title' => 'Control Structures in Python',
            'description' => 'Learn about if statements, loops, and other control structures',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module2->id,
            'type' => 'assignment',
            'title' => 'Function Implementation Exercise',
            'description' => 'Practice writing and using functions in Python',
            'due_date' => now()->addWeeks(3),
            'order' => 2,
        ]);

        // Create modules for Advanced Web Development
        $module3 = Module::create([
            'course_id' => $course2->id,
            'title' => 'Modern JavaScript',
            'description' => 'Advanced JavaScript concepts and ES6+ features',
            'start_date' => now(),
            'end_date' => now()->addWeeks(3),
        ]);

        $module4 = Module::create([
            'course_id' => $course2->id,
            'title' => 'React.js Fundamentals',
            'description' => 'Building modern user interfaces with React',
            'start_date' => now()->addWeeks(3),
            'end_date' => now()->addWeeks(6),
        ]);

        // Create module items for Advanced Web Development
        ModuleItem::create([
            'module_id' => $module3->id,
            'type' => 'video',
            'title' => 'ES6+ Features',
            'description' => 'Overview of modern JavaScript features',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module3->id,
            'type' => 'document',
            'title' => 'JavaScript Best Practices',
            'description' => 'Guidelines for writing clean and maintainable JavaScript code',
            'order' => 2,
        ]);

        ModuleItem::create([
            'module_id' => $module4->id,
            'type' => 'video',
            'title' => 'Introduction to React',
            'description' => 'Getting started with React.js',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module4->id,
            'type' => 'assignment',
            'title' => 'React Component Development',
            'description' => 'Build a simple React application using components',
            'due_date' => now()->addWeeks(5),
            'order' => 2,
        ]);

        // Create modules for Calculus I
        $module5 = Module::create([
            'course_id' => $course3->id,
            'title' => 'Limits and Continuity',
            'description' => 'Understanding limits and continuous functions',
            'start_date' => now(),
            'end_date' => now()->addWeeks(3),
        ]);

        $module6 = Module::create([
            'course_id' => $course3->id,
            'title' => 'Derivatives',
            'description' => 'Introduction to derivatives and differentiation',
            'start_date' => now()->addWeeks(3),
            'end_date' => now()->addWeeks(6),
        ]);

        // Create module items for Calculus I
        ModuleItem::create([
            'module_id' => $module5->id,
            'type' => 'video',
            'title' => 'Introduction to Limits',
            'description' => 'Understanding the concept of limits in calculus',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module5->id,
            'type' => 'document',
            'title' => 'Continuity in Functions',
            'description' => 'Study of continuous functions and their properties',
            'order' => 2,
        ]);

        ModuleItem::create([
            'module_id' => $module6->id,
            'type' => 'video',
            'title' => 'Derivatives Basics',
            'description' => 'Introduction to derivatives and their applications',
            'order' => 1,
        ]);

        ModuleItem::create([
            'module_id' => $module6->id,
            'type' => 'quiz',
            'title' => 'Derivatives Quiz',
            'description' => 'Test your understanding of derivatives',
            'due_date' => now()->addWeeks(5),
            'order' => 2,
        ]);

        // Create students
        $students = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'phone_number' => '4567890123',
                'bio' => 'Computer Science student interested in web development and AI.',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'phone_number' => '5678901234',
                'bio' => 'Mathematics major with a focus on statistics and data analysis.',
            ],
            [
                'name' => 'Carol Martinez',
                'email' => 'carol@example.com',
                'phone_number' => '6789012345',
                'bio' => 'Engineering student passionate about artificial intelligence and robotics.',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david@example.com',
                'phone_number' => '7890123456',
                'bio' => 'Computer Science student specializing in cybersecurity and network security.',
            ],
            [
                'name' => 'Eva Garcia',
                'email' => 'eva@example.com',
                'phone_number' => '8901234567',
                'bio' => 'Mathematics student interested in theoretical mathematics and abstract algebra.',
            ],
        ];

        foreach ($students as $studentData) {
            $student = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'phone_number' => $studentData['phone_number'],
                'bio' => $studentData['bio'],
            ]);

            // Enroll students in courses
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course1->id,
                'status' => 'active',
            ]);

            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course3->id,
                'status' => 'active',
            ]);
        }

        // Create assignments based on module items that have submissions
        $assignments = [
            // From Introduction to Programming course
            [
                'course_id' => $course1->id,
                'title' => 'Python Basics Quiz',
                'description' => 'Test your understanding of Python fundamentals',
                'due_date' => now()->addWeeks(1),
                'max_score' => 50,
                'submission_type' => 'quiz',
                'module_item_id' => ModuleItem::where('title', 'Python Basics Quiz')->first()->id,
            ],
            [
                'course_id' => $course1->id,
                'title' => 'Function Implementation Exercise',
                'description' => 'Practice writing and using functions in Python',
                'due_date' => now()->addWeeks(3),
                'max_score' => 100,
                'submission_type' => 'file',
                'module_item_id' => ModuleItem::where('title', 'Function Implementation Exercise')->first()->id,
            ],
            // From Advanced Web Development course
            [
                'course_id' => $course2->id,
                'title' => 'React Component Development',
                'description' => 'Build a simple React application using components',
                'due_date' => now()->addWeeks(5),
                'max_score' => 100,
                'submission_type' => 'file',
                'module_item_id' => ModuleItem::where('title', 'React Component Development')->first()->id,
            ],
            // From Calculus I course
            [
                'course_id' => $course3->id,
                'title' => 'Derivatives Quiz',
                'description' => 'Test your understanding of derivatives',
                'due_date' => now()->addWeeks(5),
                'max_score' => 50,
                'submission_type' => 'quiz',
                'module_item_id' => ModuleItem::where('title', 'Derivatives Quiz')->first()->id,
            ],
        ];

        foreach ($assignments as $assignmentData) {
            Assignment::create($assignmentData);
        }

        // Create some submissions and progress records
        foreach ($students as $index => $studentData) {
            $student = User::where('email', $studentData['email'])->first();
            
            // Get all assignments for the student's enrolled courses
            $studentAssignments = Assignment::whereHas('course', function($query) use ($student) {
                $query->whereHas('enrollments', function($q) use ($student) {
                    $q->where('user_id', $student->id);
                });
            })->get();

            // Create submissions for each assignment
            foreach ($studentAssignments as $assignment) {
                // Randomly decide if student has submitted (80% chance)
                if (rand(1, 100) <= 80) {
                    // Generate a random score between 60% and 100% of max_score for quizzes, 70% to 100% for assignments
                    if ($assignment->submission_type === 'quiz') {
                        $score = round($assignment->max_score * (rand(60, 100) / 100), 2);
                    } else {
                        $score = round($assignment->max_score * (rand(70, 100) / 100), 2);
                    }
                    $grade = $assignment->max_score > 0 ? round(($score / $assignment->max_score) * 100, 2) : 0.0;

                    $submission = Submission::create([
                        'user_id' => $student->id,
                        'assignment_id' => $assignment->id,
                        'content' => $assignment->submission_type === 'quiz' 
                            ? json_encode(['answers' => [1, 2, 3]]) // Sample quiz answers
                            : 'Completed assignment submission',
                        'score' => $score,
                        'grade' => $grade,
                        'feedback' => 'Good work!',
                        'status' => 'graded',
                        'submission_type' => $assignment->submission_type,
                    ]);

                    // Notify instructor of submission
                    $instructor = $assignment->course->instructor;
                    if ($instructor) {
                        $instructor->notify(new AssignmentSubmitted($submission));
                    }

                    // Notify student of grading
                    $student->notify(new AssignmentGraded($submission));

                    // Notify student of due soon if assignment is due within 24 hours
                    if ($assignment->due_date && $assignment->due_date->isBetween(now(), now()->copy()->addDay())) {
                        $alreadyNotified = $student->notifications()
                            ->where('type', AssignmentDueSoon::class)
                            ->where('data->assignment_id', $assignment->id)
                            ->exists();
                        if (!$alreadyNotified) {
                            $student->notify(new AssignmentDueSoon($assignment));
                        }
                    }

                    // Create progress record
                    Progress::create([
                        'user_id' => $student->id,
                        'assignment_id' => $assignment->id,
                        'status' => 'completed',
                    ]);
                }
            }
        }
    }
}
