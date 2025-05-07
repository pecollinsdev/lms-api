<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Enrollment;
use App\Models\Submission;
use App\Models\Progress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create instructor codes
        DB::table('instructor_codes')->insert([
            ['code' => 'INST001', 'used' => false, 'created_at' => now()],
            ['code' => 'INST002', 'used' => false, 'created_at' => now()],
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'phone_number' => '555-0001',
            'bio' => 'System administrator with 10 years of experience in educational technology.',
        ]);

        // Create instructors
        $instructor1 = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'instructor',
            'password' => bcrypt('password'),
            'instructor_code' => 'INST001',
            'academic_specialty' => 'Computer Science',
            'qualifications' => 'PhD in Computer Science',
            'phone_number' => '555-0002',
            'bio' => 'Computer Science professor specializing in programming languages and software engineering. Over 15 years of teaching experience.',
        ]);

        $instructor2 = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'role' => 'instructor',
            'password' => bcrypt('password'),
            'instructor_code' => 'INST002',
            'academic_specialty' => 'Mathematics',
            'qualifications' => 'PhD in Mathematics',
            'phone_number' => '555-0003',
            'bio' => 'Mathematics professor with expertise in calculus and linear algebra. Published author and research scientist.',
        ]);

        // Create students with detailed information
        $students = collect([
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'phone_number' => '555-0004',
                'bio' => 'Computer Science major with a passion for web development.',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'phone_number' => '555-0005',
                'bio' => 'Mathematics student interested in applied mathematics and statistics.',
            ],
            [
                'name' => 'Carol Martinez',
                'email' => 'carol@example.com',
                'phone_number' => '555-0006',
                'bio' => 'Engineering student focusing on software development and AI.',
            ],
            [
                'name' => 'David Brown',
                'email' => 'david@example.com',
                'phone_number' => '555-0007',
                'bio' => 'Computer Science student with an interest in cybersecurity.',
            ],
            [
                'name' => 'Eva Garcia',
                'email' => 'eva@example.com',
                'phone_number' => '555-0008',
                'bio' => 'Mathematics major with a focus on theoretical mathematics.',
            ],
        ])->map(function ($studentData) {
            return User::factory()->create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'role' => 'student',
                'password' => bcrypt('password'),
                'phone_number' => $studentData['phone_number'],
                'bio' => $studentData['bio'],
            ]);
        });

        // Create courses for instructor 1
        $course1 = Course::create([
            'title' => 'Introduction to Programming',
            'slug' => 'intro-to-programming',
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

        // Create courses for instructor 2
        $course3 = Course::create([
            'title' => 'Calculus I',
            'slug' => 'calculus-1',
            'description' => 'Introduction to differential calculus',
            'instructor_id' => $instructor2->id,
            'start_date' => now(),
            'end_date' => now()->addMonths(3),
            'is_published' => true,
        ]);

        // Enroll students in courses
        foreach ($students as $student) {
            // Enroll in course 1
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course1->id,
                'status' => 'active',
            ]);

            // Enroll in course 3
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course3->id,
                'status' => 'active',
            ]);
        }

        // Create assignments for course 1
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
            'title' => 'Python Quiz',
            'description' => 'Test your Python knowledge',
            'due_date' => now()->addDays(14),
            'max_score' => 50,
            'submission_type' => 'quiz',
        ]);

        // Create assignments for course 3
        $assignment3 = Assignment::create([
            'course_id' => $course3->id,
            'title' => 'Derivatives Practice',
            'description' => 'Practice problems on derivatives',
            'due_date' => now()->addDays(10),
            'max_score' => 100,
            'submission_type' => 'essay',
        ]);

        // Create some submissions and progress records
        foreach ($students as $student) {
            // Create submission for assignment 1
            Submission::create([
                'assignment_id' => $assignment1->id,
                'user_id' => $student->id,
                'submission_type' => 'file',
                'file_path' => 'submissions/sample.py',
                'submitted_at' => now(),
                'status' => 'pending',
            ]);

            // Create progress record
            Progress::create([
                'user_id' => $student->id,
                'assignment_id' => $assignment1->id,
                'status' => 'in_progress',
            ]);
        }
    }
} 