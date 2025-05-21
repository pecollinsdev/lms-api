<?php

namespace App\Controllers\Instructor;

use GuzzleHttp\Client;

class ModuleItemController extends BaseController
{
    public $apiBase = 'http://localhost/lms-api/api/public/api/';
    public $client;

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

    public function index($courseId)
    {
        try {
            // Fetch course data
            $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
            $courseData = json_decode($courseResponse->getBody(), true);
            $course = $courseData['data'] ?? [];

            $this->view('instructor/courses/edit', [
                'course' => $course,
                'profile' => $this->getProfile(),
                'modules' => $course['modules'] ?? []
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/courses/edit', [
                'course' => [],
                'profile' => [],
                'modules' => []
            ]);
        }
    }

    public function create($courseId)
    {
        try {
            // Fetch course data
            $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
            $courseData = json_decode($courseResponse->getBody(), true);
            $course = $courseData['data'] ?? [];

            $this->view('instructor/courses/module_item_form', [
                'course' => $course,
                'profile' => $this->getProfile(),
                'modules' => $course['modules'] ?? []
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/courses/module_item_form', [
                'course' => [],
                'profile' => [],
                'modules' => []
            ]);
        }
    }

    public function store($courseId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['title', 'type', 'module_id'], $_POST);

                // Validate title length
                $this->validateStringLength($_POST['title'], 255, 'title');

                // Validate type
                $this->validateInArray($_POST['type'], ['assignment', 'quiz', 'material'], 'type');

                // Create the module item
                $response = $this->client->post($this->apiBase . "modules/{$_POST['module_id']}/items", [
                    'json' => [
                        'title' => $_POST['title'],
                        'type' => $_POST['type'],
                        'description' => $_POST['description'] ?? null,
                        'content' => $_POST['content'] ?? null,
                        'due_date' => $_POST['due_date'] ?? null,
                        'points' => $_POST['points'] ?? null
                    ]
                ]);

                if ($response->getStatusCode() === 201) {
                    $this->redirect("/instructor/courses/{$courseId}/edit");
                } else {
                    throw new \Exception('Failed to create module item. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch course data for error view
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                $courseData = json_decode($courseResponse->getBody(), true);
                $course = $courseData['data'] ?? [];

                $this->view('instructor/courses/module_item_form', [
                    'error' => $e->getMessage(),
                    'course' => $course,
                    'profile' => $this->getProfile(),
                    'modules' => $course['modules'] ?? []
                ]);
            }
        }
    }

    public function edit($courseId, $moduleId, $itemId)
    {
        try {
            // Fetch module item details with all necessary relationships
            $response = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                'query' => [
                    'include' => 'module.course,questions.options,submissions.student'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch module item: ' . $response->getReasonPhrase());
            }
            
            $itemData = json_decode($response->getBody(), true);
            $item = $itemData['data'] ?? [];

            // Debug log
            error_log('Module Item Data: ' . json_encode($item));

            // Ensure we have the required fields
            if (empty($item['type'])) {
                throw new \Exception('Module item type is missing from the response.');
            }

            // Transform content based on type
            if (isset($item['content']) && is_array($item['content'])) {
                switch ($item['type']) {
                    case 'video':
                        $item['content_data'] = [
                            'url' => $item['content']['url'] ?? null,
                            'provider' => $item['content']['provider'] ?? null,
                            'duration' => $item['content']['duration'] ?? null,
                            'allow_download' => $item['content']['allow_download'] ?? false,
                            'title' => $item['content']['title'] ?? null,
                            'description' => $item['content']['description'] ?? null,
                            'thumbnail' => $item['content']['thumbnail'] ?? null
                        ];
                        break;
                    case 'document':
                        $item['content_data'] = [
                            'url' => $item['content']['url'] ?? null,
                            'type' => $item['content']['type'] ?? null,
                            'size' => $item['content']['size'] ?? null,
                            'allow_download' => $item['content']['allow_download'] ?? false,
                            'title' => $item['content']['title'] ?? null,
                            'description' => $item['content']['description'] ?? null,
                            'file_name' => $item['content']['file_name'] ?? null
                        ];
                        break;
                    case 'assignment':
                        $item['content_data'] = [
                            'instructions' => $item['content']['instructions'] ?? null,
                            'due_date' => $item['content']['due_date'] ?? null,
                            'max_score' => $item['content']['max_score'] ?? 100,
                            'max_attempts' => $item['content']['max_attempts'] ?? null,
                            'allow_late_submission' => $item['content']['allow_late_submission'] ?? false,
                            'late_submission_penalty' => $item['content']['late_submission_penalty'] ?? null,
                            'attachment' => $item['content']['attachment'] ?? null,
                            'rubric' => $item['content']['rubric'] ?? null,
                            'peer_review' => $item['content']['peer_review'] ?? false,
                            'peer_review_due_date' => $item['content']['peer_review_due_date'] ?? null
                        ];
                        break;
                    case 'quiz':
                        $item['content_data'] = [
                            'instructions' => $item['content']['instructions'] ?? null,
                            'due_date' => $item['content']['due_date'] ?? null,
                            'time_limit' => $item['content']['time_limit'] ?? 30,
                            'questions' => $item['content']['questions'] ?? [],
                            'allow_retake' => $item['content']['allow_retake'] ?? false,
                            'show_correct_answers' => $item['content']['show_correct_answers'] ?? false,
                            'passing_score' => $item['content']['passing_score'] ?? null,
                            'randomize_questions' => $item['content']['randomize_questions'] ?? false,
                            'show_progress' => $item['content']['show_progress'] ?? true,
                            'allow_navigation' => $item['content']['allow_navigation'] ?? true,
                            'require_all_questions' => $item['content']['require_all_questions'] ?? false
                        ];
                        break;
                    case 'resource':
                        $item['content_data'] = [
                            'resource_type' => $item['content']['resource_type'] ?? 'file',
                            'file_url' => $item['content']['file_url'] ?? null,
                            'url' => $item['content']['url'] ?? null,
                            'content' => $item['content']['content'] ?? null,
                            'title' => $item['content']['title'] ?? null,
                            'description' => $item['content']['description'] ?? null,
                            'file_name' => $item['content']['file_name'] ?? null,
                            'file_type' => $item['content']['file_type'] ?? null,
                            'file_size' => $item['content']['file_size'] ?? null,
                            'allow_download' => $item['content']['allow_download'] ?? false
                        ];
                        break;
                }
            }

            // If module data is missing, fetch it separately
            if (empty($item['module'])) {
                $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
                if ($moduleResponse->getStatusCode() === 200) {
                    $moduleData = json_decode($moduleResponse->getBody(), true);
                    $item['module'] = $moduleData['data'] ?? [];
                }
            }

            // If course data is still missing, fetch it directly
            if (empty($item['module']['course'])) {
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                if ($courseResponse->getStatusCode() === 200) {
                    $courseData = json_decode($courseResponse->getBody(), true);
                    $item['module']['course'] = $courseData['data'] ?? [];
                }
            }

            $this->view('instructor/courses/modules/items/edit', [
                'module_item' => $item,
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            error_log('Error in ModuleItemController::edit: ' . $e->getMessage());
            $this->handleError($e, 'instructor/courses/modules/items/edit', [
                'module_item' => [],
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'profile' => []
            ]);
        }
    }

    public function show($courseId, $itemId)
    {
        try {
            // Fetch module item details - backend will automatically load module.course, questions.options, and submissions.student
            $response = $this->client->get($this->apiBase . "module-items/{$itemId}");
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch module item: ' . $response->getReasonPhrase());
            }
            $itemData = json_decode($response->getBody(), true);
            $item = $itemData['data'] ?? [];

            // Debug log
            error_log('Module Item Data: ' . json_encode($item));

            // Transform content based on type
            if (isset($item['content']) && is_array($item['content'])) {
                switch ($item['type']) {
                    case 'video':
                        $item['video_url'] = $item['content']['url'] ?? null;
                        $item['video_provider'] = $item['content']['provider'] ?? null;
                        $item['video_duration'] = $item['content']['duration'] ?? null;
                        $item['video_allow_download'] = $item['content']['allow_download'] ?? false;
                        break;
                    case 'document':
                        $item['document_url'] = $item['content']['url'] ?? null;
                        $item['document_type'] = $item['content']['type'] ?? null;
                        $item['document_size'] = $item['content']['size'] ?? null;
                        $item['document_allow_download'] = $item['content']['allow_download'] ?? false;
                        break;
                    case 'assignment':
                        $item['assignment_instructions'] = $item['content']['instructions'] ?? null;
                        $item['max_attempts'] = $item['content']['max_attempts'] ?? null;
                        $item['allow_late_submission'] = $item['content']['allow_late_submission'] ?? false;
                        $item['late_submission_penalty'] = $item['content']['late_submission_penalty'] ?? null;
                        break;
                    case 'quiz':
                        $item['quiz_instructions'] = $item['content']['instructions'] ?? null;
                        $item['time_limit'] = $item['content']['time_limit'] ?? null;
                        $item['allow_retake'] = $item['content']['allow_retake'] ?? false;
                        $item['show_correct_answers'] = $item['content']['show_correct_answers'] ?? false;
                        $item['passing_score'] = $item['content']['passing_score'] ?? null;
                        break;
                }
            }

            // If module data is missing, fetch it separately
            if (empty($item['module'])) {
                // Fetch module data
                $moduleResponse = $this->client->get($this->apiBase . "modules/{$item['module_id']}");
                if ($moduleResponse->getStatusCode() === 200) {
                    $moduleData = json_decode($moduleResponse->getBody(), true);
                    $item['module'] = $moduleData['data'] ?? [];
                }
            }

            // If course data is still missing, fetch it directly
            if (empty($item['module']['course'])) {
                // Fetch course data
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                if ($courseResponse->getStatusCode() === 200) {
                    $courseData = json_decode($courseResponse->getBody(), true);
                    $item['module']['course'] = $courseData['data'] ?? [];
                }
            }

            // Ensure we have the required nested data
            if (empty($item['module'])) {
                throw new \Exception('Module data is missing from the item response.');
            }

            if (empty($item['module']['course'])) {
                throw new \Exception('Course data is missing from the module response.');
            }

            $this->view('instructor/courses/module_item', [
                'item' => $item,
                'course' => $item['module']['course'],
                'module' => $item['module'],
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            error_log('Error in ModuleItemController::show: ' . $e->getMessage());
            $this->handleError($e, 'instructor/courses/module_item', [
                'item' => [],
                'course' => [],
                'module' => [],
                'profile' => []
            ]);
        }
    }

    public function update($itemId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['title', 'type'], $_POST);

                // Validate title length
                $this->validateStringLength($_POST['title'], 255, 'title');

                // Validate type
                $this->validateInArray($_POST['type'], ['assignment', 'quiz', 'material'], 'type');

                // Update the module item
                $response = $this->client->put($this->apiBase . "module-items/{$itemId}", [
                    'json' => [
                        'title' => $_POST['title'],
                        'type' => $_POST['type'],
                        'description' => $_POST['description'] ?? null,
                        'content' => $_POST['content'] ?? null,
                        'due_date' => $_POST['due_date'] ?? null,
                        'points' => $_POST['points'] ?? null
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    // Get the module item data to get the module ID
                    $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}");
                    $itemData = json_decode($itemResponse->getBody(), true);
                    $item = $itemData['data'] ?? [];
                    $moduleId = $item['module_id'] ?? null;

                    if ($moduleId) {
                        // Get the module data to get the course ID
                        $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
                        $moduleData = json_decode($moduleResponse->getBody(), true);
                        $module = $moduleData['data'] ?? [];
                        $courseId = $module['course_id'] ?? null;

                        if ($courseId) {
                            $this->redirect("/instructor/courses/{$courseId}/edit");
                        } else {
                            throw new \Exception('Failed to get course ID from module data.');
                        }
                    } else {
                        throw new \Exception('Failed to get module ID from item data.');
                    }
                } else {
                    throw new \Exception('Failed to update module item. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch module item data to get the module ID
                $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}");
                $itemData = json_decode($itemResponse->getBody(), true);
                $item = $itemData['data'] ?? [];
                $moduleId = $item['module_id'] ?? null;

                if ($moduleId) {
                    // Get the module data to get the course ID
                    $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
                    $moduleData = json_decode($moduleResponse->getBody(), true);
                    $module = $moduleData['data'] ?? [];
                    $courseId = $module['course_id'] ?? null;

                    if ($courseId) {
                        // Fetch course data for error view
                        $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                        $courseData = json_decode($courseResponse->getBody(), true);
                        $course = $courseData['data'] ?? [];

                        $this->view('instructor/courses/edit', [
                            'error' => $e->getMessage(),
                            'course' => $course,
                            'profile' => $this->getProfile(),
                            'modules' => $course['modules'] ?? [],
                            'editingItem' => $item
                        ]);
                    } else {
                        throw new \Exception('Failed to get course ID from module data.');
                    }
                } else {
                    throw new \Exception('Failed to get module ID from item data.');
                }
            }
        }
    }

    public function delete($itemId)
    {
        try {
            // First get the module item to get the module ID
            $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}");
            $itemData = json_decode($itemResponse->getBody(), true);
            $item = $itemData['data'] ?? [];
            $moduleId = $item['module_id'] ?? null;

            if (!$moduleId) {
                throw new \Exception('Failed to get module ID from item data.');
            }

            // Get the module data to get the course ID
            $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
            $moduleData = json_decode($moduleResponse->getBody(), true);
            $module = $moduleData['data'] ?? [];
            $courseId = $module['course_id'] ?? null;

            if (!$courseId) {
                throw new \Exception('Failed to get course ID from module data.');
            }

            // Delete the module item
            $response = $this->client->delete($this->apiBase . "module-items/{$itemId}");
            
            if ($response->getStatusCode() === 200) {
                $this->redirect("/instructor/courses/{$courseId}/edit");
            } else {
                throw new \Exception('Failed to delete module item. Please try again.');
            }
        } catch (\Exception $e) {
            if (isset($courseId)) {
                // Fetch course data for error view
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                $courseData = json_decode($courseResponse->getBody(), true);
                $course = $courseData['data'] ?? [];

                $this->view('instructor/courses/edit', [
                    'error' => $e->getMessage(),
                    'course' => $course,
                    'profile' => $this->getProfile(),
                    'modules' => $course['modules'] ?? []
                ]);
            } else {
                throw new \Exception('Failed to handle module item deletion: ' . $e->getMessage());
            }
        }
    }
} 