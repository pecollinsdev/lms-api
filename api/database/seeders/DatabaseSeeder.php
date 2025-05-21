<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleItem;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Progress;
use App\Models\InstructorCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Notifications\ModuleItemSubmitted;
use App\Notifications\ModuleItemGraded;
use App\Notifications\ModuleItemDueSoon;
use App\Models\Question;
use App\Models\Option;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedInstructorCodes();
        $admin = $this->seedAdmin();
        $instructors = $this->seedInstructors();
        $courses = $this->seedCourses($instructors);
        $this->seedModules($courses);
        $this->seedModuleItems();
        $this->seedQuestions();
        $students = $this->seedStudents();
        $this->seedEnrollments($students, $courses);
        $this->seedSubmissionsAndProgress($students);
        $this->seedPendingSubmissions($instructors[0], $students);
    }

    /**
     * Seed instructor codes
     */
    private function seedInstructorCodes(): void
    {
        $codes = ['INST001', 'INST002'];
        foreach ($codes as $code) {
            if (!InstructorCode::where('code', $code)->exists()) {
                InstructorCode::create(['code' => $code, 'is_used' => false]);
            }
        }
    }

    /**
     * Seed admin user
     */
    private function seedAdmin(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone_number' => '1234567890',
            'bio' => 'System administrator with full access to all features.',
            'email_verified_at' => now(),
            'profile_picture' => null,
        ]);
    }

    /**
     * Seed instructors
     */
    private function seedInstructors(): array
    {
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
            'email_verified_at' => now(),
            'profile_picture' => null,
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
            'email_verified_at' => now(),
            'profile_picture' => null,
        ]);

        return [$instructor1, $instructor2];
    }

    /**
     * Seed courses
     */
    private function seedCourses(array $instructors): array
    {
        $course1 = Course::create([
            'title' => 'Introduction to Programming',
            'slug' => 'intro-programming',
            'description' => 'Learn the basics of programming with Python',
            'instructor_id' => $instructors[0]->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_published' => true,
            'cover_image' => null,
        ]);

        $course2 = Course::create([
            'title' => 'Advanced Web Development',
            'slug' => 'advanced-web-dev',
            'description' => 'Master modern web development techniques',
            'instructor_id' => $instructors[0]->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
            'is_published' => true,
            'cover_image' => null,
        ]);

        $course3 = Course::create([
            'title' => 'Calculus I',
            'slug' => 'calculus-1',
            'description' => 'Introduction to differential and integral calculus',
            'instructor_id' => $instructors[1]->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_published' => true,
            'cover_image' => null,
        ]);

        return [$course1, $course2, $course3];
    }

    /**
     * Seed modules
     */
    private function seedModules(array $courses): void
    {
        // Modules for Introduction to Programming
        Module::create([
            'course_id' => $courses[0]->id,
            'title' => 'Getting Started with Python',
            'description' => 'Introduction to Python programming language and basic concepts',
            'start_date' => now(),
            'end_date' => now()->addWeeks(2),
        ]);

        Module::create([
            'course_id' => $courses[0]->id,
            'title' => 'Control Structures and Functions',
            'description' => 'Learn about loops, conditionals, and function definitions',
            'start_date' => now()->addWeeks(2),
            'end_date' => now()->addWeeks(4),
        ]);

        // Modules for Advanced Web Development
        Module::create([
            'course_id' => $courses[1]->id,
            'title' => 'Modern JavaScript',
            'description' => 'Advanced JavaScript concepts and ES6+ features',
            'start_date' => now(),
            'end_date' => now()->addWeeks(3),
        ]);

        Module::create([
            'course_id' => $courses[1]->id,
            'title' => 'React.js Fundamentals',
            'description' => 'Building modern user interfaces with React',
            'start_date' => now()->addWeeks(3),
            'end_date' => now()->addWeeks(6),
        ]);

        // Modules for Calculus I
        Module::create([
            'course_id' => $courses[2]->id,
            'title' => 'Limits and Continuity',
            'description' => 'Understanding limits and continuous functions',
            'start_date' => now(),
            'end_date' => now()->addWeeks(3),
        ]);

        Module::create([
            'course_id' => $courses[2]->id,
            'title' => 'Derivatives',
            'description' => 'Introduction to derivatives and differentiation',
            'start_date' => now()->addWeeks(3),
            'end_date' => now()->addWeeks(6),
        ]);
    }

    /**
     * Seed module items
     */
    private function seedModuleItems(): void
    {
        $this->seedPythonModuleItems();
        $this->seedWebDevModuleItems();
        $this->seedCalculusModuleItems();
    }

    /**
     * Seed Python module items
     */
    private function seedPythonModuleItems(): void
    {
        $module = Module::where('title', 'Getting Started with Python')->first();
        
        // Document
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'document',
            'title' => 'Python Installation Guide',
            'description' => 'Step-by-step guide to install Python and set up your development environment',
            'order' => 1,
            'content_data' => json_encode([
                'document_url' => 'https://example.com/docs/python-installation.pdf',
                'document_type' => 'pdf',
                'allow_download' => true
            ]),
            'settings' => json_encode(['level' => 'beginner'])
        ]);

        // Video lecture
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'video',
            'title' => 'Introduction to Python',
            'description' => 'Overview of Python programming language',
            'order' => 2,
            'content_data' => json_encode([
                'video_url' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                'video_provider' => 'youtube',
                'video_duration' => 600,
                'video_allow_download' => false
            ]),
            'settings' => json_encode(['level' => 'beginner', 'language' => 'English'])
        ]);

        // Quiz
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'quiz',
            'title' => 'Python Basics Quiz',
            'description' => 'Test your understanding of Python basics',
            'order' => 3,
            'content_data' => json_encode([
                'time_limit' => 30,
                'passing_score' => 70,
                'max_attempts' => 3
            ]),
            'settings' => json_encode(['level' => 'beginner'])
        ]);

        // Assignment
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'assignment',
            'title' => 'First Python Program',
            'description' => 'Create your first Python program',
            'order' => 4,
            'content_data' => json_encode([
                'instructions' => 'Write a simple Python program that prints "Hello, World!" and performs basic arithmetic operations.',
                'due_date' => now()->addDays(7),
                'max_score' => 100
            ]),
            'settings' => json_encode(['level' => 'beginner'])
        ]);
    }

    /**
     * Seed Web Development module items
     */
    private function seedWebDevModuleItems(): void
    {
        $module = Module::where('title', 'Modern JavaScript')->first();
        
        // Document
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'document',
            'title' => 'ES6+ Features Reference',
            'description' => 'Comprehensive guide to ES6+ JavaScript features',
            'order' => 1,
            'content_data' => json_encode([
                'document_url' => 'https://example.com/docs/es6-features.pdf',
                'document_type' => 'pdf',
                'allow_download' => true
            ]),
            'settings' => json_encode(['level' => 'intermediate'])
        ]);

        // Video lecture
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'video',
            'title' => 'ES6+ Features',
            'description' => 'Overview of modern JavaScript features',
            'order' => 2,
            'content_data' => json_encode([
                'video_url' => 'https://www.youtube.com/watch?v=NCwa_xi0Uuc',
                'video_provider' => 'youtube',
                'video_duration' => 1200,
                'video_allow_download' => false
            ]),
            'settings' => json_encode(['level' => 'advanced'])
        ]);

        // Quiz
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'quiz',
            'title' => 'JavaScript ES6 Quiz',
            'description' => 'Test your knowledge of ES6 features',
            'order' => 3,
            'content_data' => json_encode([
                'time_limit' => 45,
                'passing_score' => 75,
                'max_attempts' => 2
            ]),
            'settings' => json_encode(['level' => 'intermediate'])
        ]);

        // Assignment
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'assignment',
            'title' => 'ES6 Project',
            'description' => 'Create a project using ES6 features',
            'order' => 4,
            'content_data' => json_encode([
                'instructions' => 'Create a simple web application using ES6 features like arrow functions, destructuring, and template literals.',
                'due_date' => now()->addDays(14),
                'max_score' => 100
            ]),
            'settings' => json_encode(['level' => 'intermediate'])
        ]);
    }

    /**
     * Seed Calculus module items
     */
    private function seedCalculusModuleItems(): void
    {
        $module = Module::where('title', 'Limits and Continuity')->first();
        
        // Document
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'document',
            'title' => 'Limits and Continuity Notes',
            'description' => 'Comprehensive notes on limits and continuity concepts',
            'order' => 1,
            'content_data' => json_encode([
                'document_url' => 'https://example.com/docs/limits-continuity.pdf',
                'document_type' => 'pdf',
                'allow_download' => true
            ]),
            'settings' => json_encode(['topic' => 'limits'])
        ]);

        // Video lecture
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'video',
            'title' => 'Introduction to Limits',
            'description' => 'Understanding the concept of limits in calculus',
            'order' => 2,
            'content_data' => json_encode([
                'video_url' => 'https://www.youtube.com/watch?v=HfACrKJ_Y2w',
                'video_provider' => 'youtube',
                'video_duration' => 800,
                'video_allow_download' => false
            ]),
            'settings' => json_encode(['topic' => 'limits'])
        ]);

        // Quiz
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'quiz',
            'title' => 'Limits Quiz',
            'description' => 'Test your understanding of limits',
            'order' => 3,
            'content_data' => json_encode([
                'time_limit' => 40,
                'passing_score' => 70,
                'max_attempts' => 3
            ]),
            'settings' => json_encode(['topic' => 'limits'])
        ]);

        // Assignment
        ModuleItem::create([
            'module_id' => $module->id,
            'type' => 'assignment',
            'title' => 'Limits Problem Set',
            'description' => 'Practice problems on limits',
            'order' => 4,
            'content_data' => json_encode([
                'instructions' => 'Solve the following problems involving limits and continuity.',
                'due_date' => now()->addDays(10),
                'max_score' => 100
            ]),
            'settings' => json_encode(['topic' => 'limits'])
        ]);
    }

    /**
     * Seed questions
     */
    private function seedQuestions(): void
    {
        $this->seedPythonQuizQuestions();
        $this->seedDerivativesQuizQuestions();
    }

    /**
     * Seed Python quiz questions
     */
    private function seedPythonQuizQuestions(): void
    {
        $quiz = ModuleItem::where('title', 'Python Basics Quiz')->first();
        if (!$quiz) return;

        $questions = [
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the correct way to create a variable in Python?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 1,
            ],
            // Add more questions...
        ];

        foreach ($questions as $questionData) {
            $question = Question::create($questionData);
            $this->createQuestionOptions($question);
        }
    }

    /**
     * Seed derivatives quiz questions
     */
    private function seedDerivativesQuizQuestions(): void
    {
        $quiz = ModuleItem::where('title', 'Derivatives Quiz')->first();
        if (!$quiz) return;

        $questions = [
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the derivative of f(x) = x²?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 1,
            ],
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the derivative of f(x) = sin(x)?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 2,
            ],
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the derivative of f(x) = e^x?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 3,
            ],
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the derivative of f(x) = ln(x)?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 4,
            ],
            [
                'module_item_id' => $quiz->id,
                'prompt' => 'What is the derivative of f(x) = cos(x)?',
                'type' => 'multiple_choice',
                'points' => 10,
                'order' => 5,
            ],
        ];

        foreach ($questions as $questionData) {
            $question = Question::create($questionData);
            $this->createQuestionOptions($question);
        }
    }

    /**
     * Create options for a question
     */
    private function createQuestionOptions(Question $question): void
    {
        $options = $this->getQuestionOptions($question);
        foreach ($options as $option) {
            Option::create([
                'question_id' => $question->id,
                'text' => $option['text'],
                'is_correct' => $option['is_correct']
            ]);
        }
    }

    /**
     * Get options for a question
     */
    private function getQuestionOptions(Question $question): array
    {
        // Return appropriate options based on question
        return [];
    }

    /**
     * Seed students
     */
    private function seedStudents(): array
    {
        $students = [
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'phone_number' => '4567890123',
                'bio' => 'Computer Science student interested in web development and AI.',
            ],
            // Add more students...
        ];

        $createdStudents = [];
        foreach ($students as $studentData) {
            $createdStudents[] = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'phone_number' => $studentData['phone_number'],
                'bio' => $studentData['bio'],
                'email_verified_at' => now(),
                'profile_picture' => null,
            ]);
        }

        return $createdStudents;
    }

    /**
     * Seed enrollments
     */
    private function seedEnrollments(array $students, array $courses): void
    {
        foreach ($students as $student) {
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $courses[0]->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $courses[2]->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }
    }

    /**
     * Seed submissions and progress
     */
    private function seedSubmissionsAndProgress(array $students): void
    {
        foreach ($students as $student) {
            $this->createStudentSubmissions($student);
        }
    }

    /**
     * Create submissions for a student
     */
    private function createStudentSubmissions(User $student): void
    {
        $moduleItems = $this->getStudentModuleItems($student);
        
        foreach ($moduleItems as $moduleItem) {
            // Only create submissions for quizzes and assignments
            if (in_array($moduleItem->type, ['quiz', 'assignment'])) {
                if (rand(1, 100) <= 80) {
                    $this->createSubmission($student, $moduleItem);
                } else {
                    $this->createPendingSubmission($student, $moduleItem);
                }
            }
        }
    }

    /**
     * Get module items for a student
     */
    private function getStudentModuleItems(User $student): Collection
    {
        return ModuleItem::whereHas('module.course', function($query) use ($student) {
            $query->whereHas('enrollments', function($q) use ($student) {
                $q->where('user_id', $student->id);
            });
        })->get();
    }

    /**
     * Create a submission for a module item
     */
    private function createSubmission(User $student, ModuleItem $moduleItem): void
    {
        if ($moduleItem->type === 'quiz') {
            $this->createQuizSubmission($student, $moduleItem);
        } else {
            $this->createRegularSubmission($student, $moduleItem);
        }
    }

    /**
     * Create a quiz submission
     */
    private function createQuizSubmission(User $student, ModuleItem $moduleItem): void
    {
        $answers = $this->generateQuizAnswers($moduleItem);
        $score = $this->calculateQuizScore($answers, $moduleItem);

        $submission = Submission::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'content' => json_encode(['answers' => $answers]),
            'status' => 'graded',
            'submission_type' => 'quiz',
            'submitted_at' => now(),
        ]);

        $this->createGrade($student, $moduleItem, $submission, $score);
        $this->createProgress($student, $moduleItem);
        $this->sendNotifications($student, $moduleItem, $submission);
    }

    /**
     * Create a regular submission
     */
    private function createRegularSubmission(User $student, ModuleItem $moduleItem): void
    {
        $score = $this->calculateRegularScore($moduleItem);

        $submission = Submission::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'content' => 'Completed ' . $moduleItem->type . ' submission',
            'status' => 'graded',
            'submission_type' => $moduleItem->submission_type ?? 'file',
            'submitted_at' => now(),
        ]);

        $this->createGrade($student, $moduleItem, $submission, $score);
        $this->createProgress($student, $moduleItem);
        $this->sendNotifications($student, $moduleItem, $submission);
    }

    /**
     * Generate quiz answers
     */
    private function generateQuizAnswers(ModuleItem $moduleItem): array
    {
        $answers = [];
        $questions = Question::where('module_item_id', $moduleItem->id)->get();

        foreach ($questions as $question) {
            $correctOption = Option::where('question_id', $question->id)
                ->where('is_correct', true)
                ->first();
            
            if (!$correctOption) {
                continue; // Skip this question if no correct option exists
            }

            $isCorrect = rand(1, 100) <= 70;
            
            if ($isCorrect) {
                $selectedOption = $correctOption;
            } else {
                $incorrectOptions = Option::where('question_id', $question->id)
                    ->where('is_correct', false)
                    ->get();
                
                if ($incorrectOptions->isEmpty()) {
                    $selectedOption = $correctOption; // Fallback to correct option if no incorrect options exist
                } else {
                    $selectedOption = $incorrectOptions->random();
                }
            }
            
            $answers[] = [
                'question_id' => $question->id,
                'selected_option_id' => $selectedOption->id,
                'is_correct' => $isCorrect
            ];
        }

        return $answers;
    }

    /**
     * Calculate quiz score
     */
    private function calculateQuizScore(array $answers, ModuleItem $moduleItem): float
    {
        if (empty($answers)) {
            return 0.0; // Return 0 if there are no answers
        }
        
        $correctAnswers = count(array_filter($answers, fn($answer) => $answer['is_correct']));
        return ($correctAnswers / count($answers)) * $moduleItem->max_score;
    }

    /**
     * Calculate regular submission score
     */
    private function calculateRegularScore(ModuleItem $moduleItem): float
    {
        return $moduleItem->max_score > 0 ? 
            round($moduleItem->max_score * (rand(70, 100) / 100), 2) : 0.0;
    }

    /**
     * Create a grade record
     */
    private function createGrade(User $student, ModuleItem $moduleItem, Submission $submission, float $score): void
    {
        Grade::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'submission_id' => $submission->id,
            'graded_by' => $moduleItem->module->course->instructor_id,
            'score' => $score,
            'letter_grade' => $this->calculateLetterGrade($score),
            'feedback' => 'Good work!',
            'graded_at' => now(),
            'is_final' => true,
        ]);
    }

    /**
     * Create a progress record
     */
    private function createProgress(User $student, ModuleItem $moduleItem): void
    {
        Progress::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'status' => 'graded',
            'completed_at' => now(),
        ]);
    }

    /**
     * Send notifications
     */
    private function sendNotifications(User $student, ModuleItem $moduleItem, Submission $submission): void
    {
        $instructor = $moduleItem->module->course->instructor;
        if ($instructor) {
            $instructor->notify(new ModuleItemSubmitted($submission));
        }
        $student->notify(new ModuleItemGraded($submission));
    }

    /**
     * Calculate letter grade based on numeric grade
     */
    private function calculateLetterGrade(float $grade): string
    {
        if ($grade >= 90) return 'A';
        if ($grade >= 80) return 'B';
        if ($grade >= 70) return 'C';
        if ($grade >= 60) return 'D';
        return 'F';
    }

    /**
     * Create a pending submission for a module item
     */
    private function createPendingSubmission(User $student, ModuleItem $moduleItem): void
    {
        if ($moduleItem->type === 'quiz') {
            $this->createPendingQuizSubmission($student, $moduleItem);
        } else {
            $this->createPendingRegularSubmission($student, $moduleItem);
        }
    }

    /**
     * Create a pending quiz submission
     */
    private function createPendingQuizSubmission(User $student, ModuleItem $moduleItem): void
    {
        $answers = $this->generateQuizAnswers($moduleItem);

        $submission = Submission::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'content' => json_encode(['answers' => $answers]),
            'status' => 'pending',
            'submission_type' => 'quiz',
            'submitted_at' => now(),
        ]);

        $this->createProgress($student, $moduleItem);
        $this->sendPendingNotification($student, $moduleItem, $submission);
    }

    /**
     * Create a pending regular submission
     */
    private function createPendingRegularSubmission(User $student, ModuleItem $moduleItem): void
    {
        $submission = Submission::create([
            'user_id' => $student->id,
            'module_item_id' => $moduleItem->id,
            'content' => 'Pending ' . $moduleItem->type . ' submission',
            'status' => 'pending',
            'submission_type' => $moduleItem->submission_type ?? 'file',
            'submitted_at' => now(),
        ]);

        $this->createProgress($student, $moduleItem);
        $this->sendPendingNotification($student, $moduleItem, $submission);
    }

    /**
     * Send pending notification
     */
    private function sendPendingNotification(User $student, ModuleItem $moduleItem, Submission $submission): void
    {
        $instructor = $moduleItem->module->course->instructor;
        if ($instructor) {
            $instructor->notify(new ModuleItemSubmitted($submission));
        }
    }

    /**
     * Seed pending submissions for John's courses
     */
    private function seedPendingSubmissions(User $instructor, array $students): void
    {
        // Get all module items from John's courses that are quizzes or assignments
        $moduleItems = ModuleItem::whereHas('module.course', function($query) use ($instructor) {
            $query->where('instructor_id', $instructor->id);
        })
        ->whereIn('type', ['quiz', 'assignment'])
        ->get();

        // Create pending submissions for each student
        foreach ($students as $student) {
            // Select 2-3 random module items for pending submissions
            $selectedItems = $moduleItems->random(rand(2, 3));
            
            foreach ($selectedItems as $moduleItem) {
                if ($moduleItem->type === 'quiz') {
                    $this->createPendingQuizSubmission($student, $moduleItem);
                } else {
                    $this->createPendingRegularSubmission($student, $moduleItem);
                }
            }
        }
    }
}
