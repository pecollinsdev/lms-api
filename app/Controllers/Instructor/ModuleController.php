<?php

namespace App\Controllers\Instructor;

use GuzzleHttp\Client;

class ModuleController extends BaseController
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

    public function create($courseId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['title'], $_POST);

                // Validate title length
                $this->validateStringLength($_POST['title'], 255, 'title');

                // Validate dates if provided
                $this->validateDateRange($_POST['start_date'] ?? null, $_POST['end_date'] ?? null);

                // Create the module
                $response = $this->client->post($this->apiBase . "courses/{$courseId}/modules", [
                    'json' => [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'] ?? null,
                        'start_date' => $_POST['start_date'] ?? null,
                        'end_date' => $_POST['end_date'] ?? null
                    ]
                ]);

                if ($response->getStatusCode() === 201) {
                    $this->redirect("/instructor/courses/{$courseId}/edit");
                } else {
                    throw new \Exception('Failed to create module. Please try again.');
                }
            } catch (\Exception $e) {
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
            }
        }
    }

    public function update($moduleId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['title'], $_POST);

                // Validate title length
                $this->validateStringLength($_POST['title'], 255, 'title');

                // Validate dates if provided
                $this->validateDateRange($_POST['start_date'] ?? null, $_POST['end_date'] ?? null);

                // Update the module
                $response = $this->client->put($this->apiBase . "modules/{$moduleId}", [
                    'json' => [
                        'title' => $_POST['title'],
                        'description' => $_POST['description'] ?? null,
                        'start_date' => $_POST['start_date'] ?? null,
                        'end_date' => $_POST['end_date'] ?? null
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    // Get the course ID from the response
                    $moduleData = json_decode($response->getBody(), true);
                    $courseId = $moduleData['data']['course_id'] ?? null;
                    
                    if ($courseId) {
                        $this->redirect("/instructor/courses/{$courseId}/edit");
                    } else {
                        throw new \Exception('Failed to get course ID from module update response.');
                    }
                } else {
                    throw new \Exception('Failed to update module. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch module data to get course ID
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
                        'editingModule' => $module
                    ]);
                } else {
                    throw new \Exception('Failed to get course ID from module data.');
                }
            }
        }
    }

    public function delete($moduleId)
    {
        try {
            // First get the module to get the course ID
            $moduleResponse = $this->client->get($this->apiBase . "modules/{$moduleId}");
            $moduleData = json_decode($moduleResponse->getBody(), true);
            $module = $moduleData['data'] ?? [];
            $courseId = $module['course_id'] ?? null;

            if (!$courseId) {
                throw new \Exception('Failed to get course ID from module data.');
            }

            // Delete the module
            $response = $this->client->delete($this->apiBase . "modules/{$moduleId}");
            
            if ($response->getStatusCode() === 200) {
                $this->redirect("/instructor/courses/{$courseId}/edit");
            } else {
                throw new \Exception('Failed to delete module. Please try again.');
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
                throw new \Exception('Failed to handle module deletion: ' . $e->getMessage());
            }
        }
    }
} 