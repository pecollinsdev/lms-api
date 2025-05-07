<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Progress;
use App\Models\InstructorCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // Create assignments
        $assignment1 = Assignment::create([
            'course_id' => $course1->id,
            'title' => 'Python Basics',
            'description' => 'Complete basic Python programming exercises',
            'due_date' => now()->addDays(7),
            'max_score' => 100,
            'submission_type' => 'file',
        ]);

        $assignment2 = Assignment::create([
            'course_id' => $course1->id,
            'title' => 'Web Development Quiz',
            'description' => 'Test your knowledge of HTML, CSS, and JavaScript',
            'due_date' => now()->addDays(14),
            'max_score' => 50,
            'submission_type' => 'quiz',
        ]);

        $assignment3 = Assignment::create([
            'course_id' => $course3->id,
            'title' => 'Calculus Problem Set',
            'description' => 'Practice problems on derivatives and integrals',
            'due_date' => now()->addDays(10),
            'max_score' => 100,
            'submission_type' => 'file',
        ]);

        // Create some submissions and progress records
        foreach ($students as $index => $studentData) {
            $student = User::where('email', $studentData['email'])->first();
            
            // Create submission for first assignment
            Submission::create([
                'user_id' => $student->id,
                'assignment_id' => $assignment1->id,
                'content' => 'Completed Python exercises',
                'grade' => rand(70, 100),
                'feedback' => 'Good work!',
                'status' => 'graded',
                'submission_type' => 'file',
            ]);

            // Create progress record
            Progress::create([
                'user_id' => $student->id,
                'assignment_id' => $assignment1->id,
                'status' => 'completed',
            ]);
        }
    }
}
