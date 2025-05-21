<?php

namespace App\Controllers\Instructor;

use GuzzleHttp\Client;

class CourseController extends BaseController
{
    protected $apiBase = 'http://localhost/lms-api/api/public/api/';
    protected $client;

    public function __construct()
    {
        $token = $_COOKIE['token'] ?? '';
        if (empty($token)) {
            header('Location: /lms-frontend/public/auth/login');
            exit;
        }

        $this->client = new Client([
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ],
            'verify' => false // Only for development
        ]);
    }

    public function index()
    {
        try {
            // Fetch courses with all necessary relations in a single call
            $response = $this->client->get($this->apiBase . 'courses', [
                'query' => [
                    'include' => 'modules.items,students,instructor'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch courses: ' . $response->getReasonPhrase());
            }
            
            $data = json_decode($response->getBody(), true);
            
            $this->view('instructor/courses', [
                'courses' => $data,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/courses', [
                'courses' => [],
                'profile' => []
            ]);
        }
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $this->view('instructor/courses/create', [
            'profile' => $this->getProfile()
        ]);
    }

    public function store()
    {
        try {
            // Validate required fields
            $this->validateRequiredFields(['title', 'description'], $_POST);

            // Create the course
            $response = $this->client->post($this->apiBase . 'courses', [
                'json' => [
                    'title' => $_POST['title'],
                    'slug' => strtolower(str_replace(' ', '-', $_POST['title'])),
                    'description' => $_POST['description'],
                    'start_date' => $_POST['start_date'] ?? null,
                    'end_date' => $_POST['end_date'] ?? null,
                    'is_published' => isset($_POST['is_published']),
                    'cover_image' => $_POST['cover_image'] ?? null,
                    'credits' => $_POST['credits'] ?? null,
                    'status' => $_POST['status'] ?? 'draft',
                    'code' => $_POST['code'] ?? null
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                $courseData = json_decode($response->getBody(), true);
                $courseId = $courseData['data']['id'] ?? null;
                
                if ($courseId) {
                    // Redirect to module creation page
                    $this->redirect("/instructor/courses/{$courseId}/modules/create");
                } else {
                    $this->redirect('/instructor/courses');
                }
            } else {
                throw new \Exception('Failed to create course. Please try again.');
            }
        } catch (\Exception $e) {
            $this->view('instructor/courses/create', [
                'error' => $e->getMessage(),
                'formData' => $_POST,
                'profile' => $this->getProfile()
            ]);
        }
    }

    public function show($courseId)
    {
        try {
            // Fetch course with all necessary relations in a single call
            $response = $this->client->get($this->apiBase . "courses/{$courseId}", [
                'query' => [
                    'include' => 'modules.items,students,instructor'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch course: ' . $response->getReasonPhrase());
            }
            
            $courseData = json_decode($response->getBody(), true);
            $course = $courseData['data'] ?? [];

            // Debug information
            error_log('Course data structure: ' . json_encode($course));
            if (!empty($course['modules'])) {
                error_log('First module items: ' . json_encode($course['modules'][0]['items'] ?? []));
            }

            // If modules don't have items, fetch them separately
            if (!empty($course['modules']) && empty($course['modules'][0]['items'])) {
                $modulesResponse = $this->client->get($this->apiBase . "courses/{$courseId}/modules", [
                    'query' => [
                        'include' => 'items'
                    ]
                ]);
                
                if ($modulesResponse->getStatusCode() === 200) {
                    $modulesData = json_decode($modulesResponse->getBody(), true);
                    $course['modules'] = $modulesData['data'] ?? [];
                }
            }

            $this->view('instructor/courses/show', [
                'course' => $course,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/courses/show', [
                'course' => [],
                'profile' => []
            ]);
        }
    }

    public function edit($courseId)
    {
        try {
            // Fetch course with modules and items in a single call
            $response = $this->client->get($this->apiBase . "courses/{$courseId}", [
                'query' => [
                    'include' => 'modules.module_items'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch course: ' . $response->getReasonPhrase());
            }
            
            $courseData = json_decode($response->getBody(), true);
            $course = $courseData['data'] ?? [];

            // Debug information
            error_log('Course data structure: ' . json_encode($course));
            if (!empty($course['modules'])) {
                error_log('First module items: ' . json_encode($course['modules'][0]['items'] ?? []));
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Validate required fields
                    $this->validateRequiredFields(['title', 'description'], $_POST);

                    // Update the course
                    $response = $this->client->put($this->apiBase . "courses/{$courseId}", [
                        'json' => [
                            'title' => $_POST['title'],
                            'description' => $_POST['description'],
                            'start_date' => $_POST['start_date'] ?? null,
                            'end_date' => $_POST['end_date'] ?? null,
                            'is_published' => isset($_POST['is_published'])
                        ]
                    ]);

                    if ($response->getStatusCode() === 200) {
                        $this->redirect("/instructor/courses/{$courseId}/edit");
                    } else {
                        throw new \Exception('Failed to update course. Please try again.');
                    }
                } catch (\Exception $e) {
                    $this->view('instructor/courses/edit', [
                        'error' => $e->getMessage(),
                        'course' => $course,
                        'profile' => $this->getProfile(),
                        'modules' => $course['modules'] ?? []
                    ]);
                }
            } else {
                $this->view('instructor/courses/edit', [
                    'course' => $course,
                    'profile' => $this->getProfile(),
                    'modules' => $course['modules'] ?? []
                ]);
            }
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/courses/edit', [
                'course' => [],
                'profile' => [],
                'modules' => []
            ]);
        }
    }

    public function delete($courseId)
    {
        try {
            // First, get all modules for the course
            $modulesResponse = $this->client->get($this->apiBase . "courses/{$courseId}/modules", [
                'query' => [
                    'include' => 'items'
                ]
            ]);
            
            if ($modulesResponse->getStatusCode() === 200) {
                $modulesData = json_decode($modulesResponse->getBody(), true);
                $modules = $modulesData['data'] ?? [];

                // Delete all module items first
                foreach ($modules as $module) {
                    if (!empty($module['items'])) {
                        foreach ($module['items'] as $item) {
                            try {
                                $itemResponse = $this->client->delete($this->apiBase . "module-items/{$item['id']}");
                                if ($itemResponse->getStatusCode() !== 204) {
                                    error_log("Failed to delete module item {$item['id']}: " . $itemResponse->getReasonPhrase());
                                }
                            } catch (\Exception $e) {
                                error_log("Failed to delete module item {$item['id']}: " . $e->getMessage());
                            }
                        }
                    }
                }

                // Delete all modules
                foreach ($modules as $module) {
                    try {
                        $moduleResponse = $this->client->delete($this->apiBase . "modules/{$module['id']}");
                        if ($moduleResponse->getStatusCode() !== 204) {
                            error_log("Failed to delete module {$module['id']}: " . $moduleResponse->getReasonPhrase());
                        }
                    } catch (\Exception $e) {
                        error_log("Failed to delete module {$module['id']}: " . $e->getMessage());
                    }
                }
            }

            // Finally, delete the course
            $response = $this->client->delete($this->apiBase . "courses/{$courseId}", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            if ($response->getStatusCode() === 204) {
                $_SESSION['success'] = 'Course and all associated content have been deleted successfully.';
                $this->redirect('/instructor/courses');
            } else {
                $errorData = json_decode($response->getBody(), true);
                throw new \Exception($errorData['message'] ?? 'Failed to delete course. Please try again.');
            }
        } catch (\Exception $e) {
            error_log("Course deletion error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete course: ' . $e->getMessage();
            $this->redirect('/instructor/courses');
        }
    }

    public function students($courseId)
    {
        try {
            error_log("=== Starting students() method for course ID: {$courseId} ===");

            // Get course details with students in a single call
            $response = $this->client->get($this->apiBase . "courses/{$courseId}", [
                'query' => [
                    'include' => 'students'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                error_log("Course details API call failed with status: " . $response->getStatusCode());
                throw new \Exception('Failed to fetch course details');
            }
            
            $courseData = json_decode($response->getBody(), true);
            error_log("Course API Response: " . print_r($courseData, true));
            $course = $courseData['data'] ?? [];
            error_log("Extracted course data: " . print_r($course, true));

            // Get paginated enrollments (students) with statistics
            $page = $_GET['page'] ?? 1;
            error_log("Fetching enrollments for page: {$page}");
            
            $enrollmentsResponse = $this->client->get($this->apiBase . "courses/{$courseId}/enrollments", [
                'query' => [
                    'page' => $page
                ]
            ]);
            
            if ($enrollmentsResponse->getStatusCode() !== 200) {
                error_log("Enrollments API call failed with status: " . $enrollmentsResponse->getStatusCode());
                throw new \Exception('Failed to fetch enrollments');
            }
            
            $enrollmentsData = json_decode($enrollmentsResponse->getBody(), true);
            error_log("Enrollments API Response: " . print_r($enrollmentsData, true));

            // Get progress data for all students
            error_log("Fetching progress data for course ID: {$courseId}");
            $progressResponse = $this->client->get($this->apiBase . "courses/{$courseId}/progress");
            if ($progressResponse->getStatusCode() === 200) {
                $progressData = json_decode($progressResponse->getBody(), true);
                error_log("Progress API Response: " . print_r($progressData, true));
                
                // Create a map of student grades
                $gradeMap = [];
                if (!empty($progressData['grade'])) {
                    foreach ($progressData['grade'] as $grade) {
                        if (isset($grade['user_id'])) {
                            $gradeMap[$grade['user_id']] = [
                                'average_grade' => $grade['average_grade'] ?? 0,
                                'letter_grade' => $grade['letter_grade'] ?? 'F'
                            ];
                        }
                    }
                }
                
                // Create a map of student completion
                $completionMap = [];
                if (!empty($progressData['completion'])) {
                    foreach ($progressData['completion'] as $completion) {
                        if (isset($completion['id'])) {
                            $completionMap[$completion['id']] = [
                                'completed_count' => $completion['completed_count'] ?? 0,
                                'completion_percentage' => $completion['completion_percentage'] ?? 0
                            ];
                        }
                    }
                }
                
                $progress = [
                    'student_count' => $progressData['student_count'] ?? 0,
                    'module_count' => $progressData['module_count'] ?? 0,
                    'total_items' => $progressData['total_items'] ?? 0,
                    'grade_map' => $gradeMap,
                    'completion_map' => $completionMap
                ];
                
                error_log("Processed progress data: " . print_r($progress, true));
            } else {
                error_log("Progress API call failed with status: " . $progressResponse->getStatusCode());
                $progress = [
                    'student_count' => 0,
                    'module_count' => 0,
                    'total_items' => 0,
                    'grade_map' => [],
                    'completion_map' => []
                ];
            }

            // Map enrollments to students with stats
            $students = [];
            if (!empty($enrollmentsData['data'])) {
                error_log("Processing " . count($enrollmentsData['data']) . " enrollments");
                foreach ($enrollmentsData['data'] as $index => $enrollment) {
                    error_log("Processing enrollment #{$index}: " . print_r($enrollment, true));
                    
                    // Validate enrollment structure
                    if (!isset($enrollment['student']) || !is_array($enrollment['student'])) {
                        error_log("Invalid enrollment structure - missing or invalid 'student' field in enrollment #{$index}");
                        continue;
                    }

                    $student = $enrollment['student'];
                    $studentId = $student['id'] ?? null;
                    
                    // Get grade and completion data from progress
                    $gradeData = $progress['grade_map'][$studentId] ?? ['average_grade' => 0, 'letter_grade' => 'F'];
                    $completionData = $progress['completion_map'][$studentId] ?? ['completed_count' => 0, 'completion_percentage' => 0];
                    
                    $studentData = [
                        'id' => $studentId,
                        'name' => $student['name'] ?? '',
                        'email' => $student['email'] ?? '',
                        'enrolled_at' => $enrollment['enrolled_at'] ?? '',
                        'progress' => $completionData['completion_percentage'] ?? 0,
                        'average_grade' => $gradeData['average_grade'] ?? 0,
                        'letter_grade' => $gradeData['letter_grade'] ?? 'F'
                    ];

                    // Log any fallback values being used
                    if (!isset($student['id'])) error_log("Using fallback for student ID in enrollment #{$index}");
                    if (!isset($student['name'])) error_log("Using fallback for student name in enrollment #{$index}");
                    if (!isset($student['email'])) error_log("Using fallback for student email in enrollment #{$index}");
                    if (!isset($enrollment['enrolled_at'])) error_log("Using fallback for enrolled_at in enrollment #{$index}");
                    if (!isset($completionData['completion_percentage'])) error_log("Using fallback for progress in enrollment #{$index}");
                    if (!isset($gradeData['average_grade'])) error_log("Using fallback for average_grade in enrollment #{$index}");

                    $students[] = $studentData;
                    error_log("Processed student data: " . print_r(end($students), true));
                }
            } else {
                error_log("No enrollments data found in response");
            }

            error_log("Final students array: " . print_r($students, true));
            error_log("Final progress array: " . print_r($progress, true));
            error_log("Final pagination data: " . print_r($enrollmentsData['meta'] ?? [], true));

            $this->view('instructor/courses/students', [
                'course' => $course,
                'students' => $students,
                'progress' => $progress,
                'pagination' => $enrollmentsData['meta'] ?? [],
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            error_log('[CourseController::students] Error: ' . $e->getMessage());
            error_log('[CourseController::students] Stack trace: ' . $e->getTraceAsString());
            $this->view('instructor/courses/students', [
                'error' => $e->getMessage(),
                'course' => [],
                'students' => [],
                'progress' => [
                    'student_count' => 0,
                    'module_count' => 0,
                    'total_items' => 0,
                    'grade_map' => [],
                    'completion_map' => []
                ],
                'pagination' => [],
                'profile' => $this->getProfile()
            ]);
        }
    }

    public function enrollStudent($courseId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateRequiredFields(['email'], $_POST);

                $response = $this->client->post($this->apiBase . "courses/{$courseId}/enroll", [
                    'json' => [
                        'email' => $_POST['email'],
                        'enrolled_by' => $_SESSION['user_id'] ?? null
                    ]
                ]);

                $data = json_decode($response->getBody(), true);
                
                $_SESSION['enrollSuccess'] = $data['message'] ?? 'Student enrolled successfully.';
                $this->redirect("/instructor/courses/{$courseId}/students");
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $error = json_decode($e->getResponse()->getBody(), true);
                $_SESSION['enrollError'] = $error['message'] ?? 'Failed to enroll student.';
                $this->redirect("/instructor/courses/{$courseId}/students");
            } catch (\Exception $e) {
                $_SESSION['enrollError'] = $e->getMessage();
                $this->redirect("/instructor/courses/{$courseId}/students");
            }
        }
    }

    public function unenroll($courseId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->validateRequiredFields(['student_id'], $_POST);

                $response = $this->client->delete($this->apiBase . "courses/{$courseId}/unenroll/{$_POST['student_id']}");

                if ($response->getStatusCode() === 200) {
                    $_SESSION['unenrollSuccess'] = 'Student unenrolled successfully.';
                } else {
                    throw new \Exception('Failed to unenroll student. Please try again.');
                }
                
                $this->redirect("/instructor/courses/{$courseId}/students");
            } catch (\Exception $e) {
                error_log('[CourseController::unenroll] Error: ' . $e->getMessage());
                $_SESSION['unenrollError'] = $e->getMessage();
                $this->redirect("/instructor/courses/{$courseId}/students");
            }
        }
    }

    public function createModule($courseId)
    {
        try {
            // Fetch existing modules for the course with their items
            $response = $this->client->get($this->apiBase . "courses/{$courseId}", [
                'query' => [
                    'include' => 'modules.module_items'
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $courseData = json_decode($response->getBody(), true);
                $modules = $courseData['data']['modules'] ?? [];
                
                // Debug log to check data structure
                error_log('Modules data: ' . json_encode($modules));
                
                // Transform module_items to items for the view
                foreach ($modules as &$module) {
                    if (isset($module['module_items'])) {
                        $module['items'] = $module['module_items'];
                        unset($module['module_items']);
                    } else {
                        $module['items'] = [];
                    }
                }
            } else {
                throw new \Exception('Failed to fetch modules');
            }

            $this->view('instructor/courses/modules/create', [
                'courseId' => $courseId,
                'modules' => $modules,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            error_log('Error in createModule: ' . $e->getMessage());
            $this->view('instructor/courses/modules/create', [
                'error' => $e->getMessage(),
                'courseId' => $courseId,
                'modules' => [],
                'profile' => $this->getProfile()
            ]);
        }
    }

    public function storeModule($courseId)
    {
        try {
            // Validate required fields
            $this->validateRequiredFields(['title'], $_POST);

            // Create the module
            $response = $this->client->post($this->apiBase . "courses/{$courseId}/modules", [
                'json' => [
                    'title' => $_POST['title'],
                    'description' => $_POST['description'] ?? '',
                    'start_date' => $_POST['start_date'] ?? null,
                    'end_date' => $_POST['end_date'] ?? null
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                $_SESSION['success'] = 'Module created successfully.';
                $this->redirect("/instructor/courses/{$courseId}/modules/create");
            } else {
                throw new \Exception('Failed to create module. Please try again.');
            }
        } catch (\Exception $e) {
            $this->view('instructor/courses/modules/create', [
                'error' => $e->getMessage(),
                'courseId' => $courseId,
                'formData' => $_POST,
                'profile' => $this->getProfile()
            ]);
        }
    }

    public function createModuleItem($courseId, $moduleId)
    {
        try {
            $this->view('instructor/courses/modules/items/create', [
                'courseId' => $courseId,
                'moduleId' => $moduleId,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            $this->redirect("/instructor/courses/{$courseId}/modules/create");
        }
    }

    public function storeModuleItem($courseId, $moduleId)
    {
        try {
            // Validate required fields
            $this->validateRequiredFields(['title', 'type'], $_POST);

            // Prepare content_data based on item type
            $contentData = [];
            $settings = [];

            switch ($_POST['type']) {
                case 'video':
                    $contentData = [
                        'video_url' => $_POST['video_url'] ?? '',
                        'video_provider' => $_POST['video_provider'] ?? 'youtube',
                        'video_duration' => (int)($_POST['video_duration'] ?? 0),
                        'video_allow_download' => isset($_POST['allow_download'])
                    ];
                    $settings = [
                        'auto_complete' => isset($_POST['auto_complete']),
                        'required_watch_time' => (int)($_POST['required_watch_time'] ?? 0)
                    ];
                    break;

                case 'document':
                    $contentData = [
                        'document_url' => $_POST['document_url'] ?? '',
                        'document_type' => $_POST['document_type'] ?? 'pdf',
                        'document_size' => (int)($_POST['document_size'] ?? 0),
                        'document_allow_download' => isset($_POST['allow_download'])
                    ];
                    $settings = [
                        'required_read_time' => (int)($_POST['required_read_time'] ?? 0)
                    ];
                    break;

                case 'assignment':
                    $contentData = [
                        'assignment_instructions' => $_POST['description'] ?? '',
                        'max_score' => (int)($_POST['max_score'] ?? 100),
                        'submission_type' => $_POST['submission_type'] ?? 'file',
                        'settings' => [
                            'max_attempts' => (int)($_POST['max_attempts'] ?? 1),
                            'allow_late_submission' => isset($_POST['allow_late_submission']),
                            'late_submission_penalty' => (int)($_POST['late_submission_penalty'] ?? 0),
                            'require_peer_review' => isset($_POST['require_peer_review']),
                            'peer_review_count' => (int)($_POST['peer_review_count'] ?? 1)
                        ]
                    ];
                    break;

                case 'quiz':
                    $contentData = [
                        'quiz_instructions' => $_POST['description'] ?? '',
                        'max_score' => (int)($_POST['max_score'] ?? 100),
                        'time_limit' => (int)($_POST['time_limit'] ?? 0),
                        'allow_retake' => isset($_POST['allow_retake']),
                        'show_correct_answers' => isset($_POST['show_correct_answers']),
                        'passing_score' => (int)($_POST['passing_score'] ?? 70),
                        'settings' => [
                            'randomize_questions' => isset($_POST['randomize_questions']),
                            'show_progress' => isset($_POST['show_progress']),
                            'allow_skip' => isset($_POST['allow_skip'])
                        ]
                    ];
                    break;
            }

            // Create the module item
            $response = $this->client->post($this->apiBase . "courses/{$courseId}/modules/{$moduleId}/items", [
                'json' => [
                    'title' => $_POST['title'],
                    'type' => $_POST['type'],
                    'description' => $_POST['description'] ?? '',
                    'due_date' => $_POST['due_date'] ?? null,
                    'order' => (int)($_POST['order'] ?? 0),
                    'max_score' => (int)($_POST['max_score'] ?? 100),
                    'submission_type' => $_POST['submission_type'] ?? 'file',
                    'content_data' => $contentData,
                    'settings' => $settings
                ]
            ]);

            if ($response->getStatusCode() === 201) {
                $_SESSION['success'] = 'Module item created successfully.';
                $this->redirect("/instructor/courses/{$courseId}/modules/create");
            } else {
                $errorData = json_decode($response->getBody(), true);
                throw new \Exception($errorData['message'] ?? 'Failed to create module item. Please try again.');
            }
        } catch (\Exception $e) {
            $this->view('instructor/courses/modules/items/create', [
                'error' => $e->getMessage(),
                'courseId' => $courseId,
                'moduleId' => $moduleId,
                'formData' => $_POST,
                'profile' => $this->getProfile()
            ]);
        }
    }

    protected function getProfile()
    {
        try {
            $response = $this->client->get($this->apiBase . 'instructor/profile');
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $data['data'] ?? [];
            }
            
            error_log("Failed to fetch instructor profile: " . $response->getReasonPhrase());
            return [];
        } catch (\Exception $e) {
            error_log("Failed to fetch instructor profile: " . $e->getMessage());
            return [];
        }
    }
} 