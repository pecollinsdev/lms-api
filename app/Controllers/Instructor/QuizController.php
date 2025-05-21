<?php

namespace App\Controllers\Instructor;

use GuzzleHttp\Client;

class QuizController extends BaseController
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

    public function addQuestion($itemId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['prompt', 'options', 'correct_option'], $_POST);

                // Validate prompt length
                $this->validateStringLength($_POST['prompt'], 1000, 'prompt');

                // Validate options
                if (!is_array($_POST['options']) || count($_POST['options']) < 2) {
                    throw new \Exception('At least 2 options are required.');
                }

                // Validate correct option
                $this->validateInArray($_POST['correct_option'], array_keys($_POST['options']), 'correct_option');

                // Add the question
                $response = $this->client->post($this->apiBase . "module-items/{$itemId}/questions", [
                    'json' => [
                        'prompt' => $_POST['prompt'],
                        'options' => $_POST['options'],
                        'correct_option' => $_POST['correct_option']
                    ]
                ]);

                if ($response->getStatusCode() === 201) {
                    $this->redirect("/instructor/module-items/{$itemId}");
                } else {
                    throw new \Exception('Failed to add question. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch module item data
                $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                    'query' => [
                        'include' => 'questions.options'
                    ]
                ]);
                $itemData = json_decode($itemResponse->getBody(), true);
                $item = $itemData['data'] ?? [];

                // Get the module and course IDs
                $moduleId = $item['module_id'] ?? null;
                if (!$moduleId) {
                    throw new \Exception('Failed to get module ID from item data.');
                }

                // Fetch module data to get course ID
                $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
                $moduleData = json_decode($moduleResponse->getBody(), true);
                $module = $moduleData['data'] ?? [];
                $courseId = $module['course_id'] ?? null;

                if (!$courseId) {
                    throw new \Exception('Failed to get course ID from module data.');
                }

                // Fetch course data
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                $courseData = json_decode($courseResponse->getBody(), true);
                $course = $courseData['data'] ?? [];

                $this->view('instructor/courses/module_item', [
                    'error' => $e->getMessage(),
                    'item' => $item,
                    'course' => $course,
                    'module' => $module,
                    'profile' => $this->getProfile()
                ]);
            }
        }
    }

    public function updateQuestion($itemId, $questionId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['prompt', 'options', 'correct_option'], $_POST);

                // Validate prompt length
                $this->validateStringLength($_POST['prompt'], 1000, 'prompt');

                // Validate options
                if (!is_array($_POST['options']) || count($_POST['options']) < 2) {
                    throw new \Exception('At least 2 options are required.');
                }

                // Validate correct option
                $this->validateInArray($_POST['correct_option'], array_keys($_POST['options']), 'correct_option');

                // Update the question
                $response = $this->client->put($this->apiBase . "module-items/{$itemId}/questions/{$questionId}", [
                    'json' => [
                        'prompt' => $_POST['prompt'],
                        'options' => $_POST['options'],
                        'correct_option' => $_POST['correct_option']
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $this->redirect("/instructor/module-items/{$itemId}");
                } else {
                    throw new \Exception('Failed to update question. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch module item data
                $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                    'query' => [
                        'include' => 'questions.options'
                    ]
                ]);
                $itemData = json_decode($itemResponse->getBody(), true);
                $item = $itemData['data'] ?? [];

                // Get the module and course IDs
                $moduleId = $item['module_id'] ?? null;
                if (!$moduleId) {
                    throw new \Exception('Failed to get module ID from item data.');
                }

                // Fetch module data to get course ID
                $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
                $moduleData = json_decode($moduleResponse->getBody(), true);
                $module = $moduleData['data'] ?? [];
                $courseId = $module['course_id'] ?? null;

                if (!$courseId) {
                    throw new \Exception('Failed to get course ID from module data.');
                }

                // Fetch course data
                $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
                $courseData = json_decode($courseResponse->getBody(), true);
                $course = $courseData['data'] ?? [];

                $this->view('instructor/courses/module_item', [
                    'error' => $e->getMessage(),
                    'item' => $item,
                    'course' => $course,
                    'module' => $module,
                    'profile' => $this->getProfile()
                ]);
            }
        }
    }

    public function deleteQuestion($itemId, $questionId)
    {
        try {
            // Delete the question
            $response = $this->client->delete($this->apiBase . "module-items/{$itemId}/questions/{$questionId}");
            
            if ($response->getStatusCode() === 200) {
                $this->redirect("/instructor/module-items/{$itemId}");
            } else {
                throw new \Exception('Failed to delete question. Please try again.');
            }
        } catch (\Exception $e) {
            // Fetch module item data
            $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                'query' => [
                    'include' => 'questions.options'
                ]
            ]);
            $itemData = json_decode($itemResponse->getBody(), true);
            $item = $itemData['data'] ?? [];

            // Get the module and course IDs
            $moduleId = $item['module_id'] ?? null;
            if (!$moduleId) {
                throw new \Exception('Failed to get module ID from item data.');
            }

            // Fetch module data to get course ID
            $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
            $moduleData = json_decode($moduleResponse->getBody(), true);
            $module = $moduleData['data'] ?? [];
            $courseId = $module['course_id'] ?? null;

            if (!$courseId) {
                throw new \Exception('Failed to get course ID from module data.');
            }

            // Fetch course data
            $courseResponse = $this->client->get($this->apiBase . "courses/{$courseId}");
            $courseData = json_decode($courseResponse->getBody(), true);
            $course = $courseData['data'] ?? [];

            $this->view('instructor/courses/module_item', [
                'error' => $e->getMessage(),
                'item' => $item,
                'course' => $course,
                'module' => $module,
                'profile' => $this->getProfile()
            ]);
        }
    }
} 