<?php

namespace App\Controllers\Student;

use App\Core\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CourseController extends Controller
{
    protected $apiBase;
    protected $client;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get API base URL from environment or config
        $this->apiBase = getenv('API_BASE_URL') ?: 'http://localhost/lms-api/api/public/api/';

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

        // Set user data in session if not already set
        if (!isset($_SESSION['user'])) {
            try {
                $response = $this->client->get($this->apiBase . 'user/profile');
                $userData = json_decode($response->getBody(), true);
                if (isset($userData['data'])) {
                    $_SESSION['user'] = $userData['data'];
                }
            } catch (\Exception $e) {
                error_log("Error fetching user profile: " . $e->getMessage());
            }
        }
    }

    public function index()
    {
        try {
            // Get enrolled courses
            $coursesResponse = $this->client->get($this->apiBase . 'student/courses');
            $coursesData = json_decode($coursesResponse->getBody(), true);
            $courses = $coursesData['data'] ?? [];
            
            // Get progress for all courses
            $progressResponse = $this->client->get($this->apiBase . 'student/progress');
            $progressData = json_decode($progressResponse->getBody(), true);
            
            // Create a map of course progress
            $courseProgressMap = [];
            if (is_array($progressData['data'] ?? [])) {
                foreach ($progressData['data'] as $courseProgress) {
                    $courseProgressMap[$courseProgress['course_id']] = $courseProgress['progress_percentage'];
                }
            }
            
            // Get grades for all courses
            $gradesMap = [];
            foreach ($courses as $course) {
                try {
                    $gradesResponse = $this->client->get($this->apiBase . 'student/courses/' . $course['id'] . '/grades');
                    $gradesData = json_decode($gradesResponse->getBody(), true);
                    
                    if (isset($gradesData['data'])) {
                        $gradesMap[$course['id']] = $gradesData['data'];
                    }
                } catch (\Exception $e) {
                    error_log("Error fetching grades for course {$course['id']}: " . $e->getMessage());
                }
            }
            
            // Enhance course data with progress and grade info
            foreach ($courses as &$course) {
                $course['completion_percentage'] = $courseProgressMap[$course['id']] ?? 0;
                
                if (isset($gradesMap[$course['id']])) {
                    $grade = $gradesMap[$course['id']];
                    $course['grade'] = $grade['letter_grade'] . ' (' . number_format($grade['average_grade'], 1) . '%)';
                } else {
                    $course['grade'] = 'N/A';
                }
            }
            
            $this->view('student/courses', [
                'courses' => $courses,
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorData = json_decode($response->getBody(), true);
            $errorMessage = $errorData['message'] ?? 'Failed to load courses';
            
            $this->view('student/courses', [
                'error' => $errorMessage,
                'courses' => [],
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (\Exception $e) {
            $this->view('student/courses', [
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
                'courses' => [],
                'profile' => $_SESSION['user'] ?? null
            ]);
        }
    }

    public function content($courseId)
    {
        try {
            // Get course details
            $courseResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId);
            $courseData = json_decode($courseResponse->getBody(), true);
            $course = $courseData['data'] ?? [];

            // Get modules with their items
            $modulesResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId . '/modules');
            $modulesData = json_decode($modulesResponse->getBody(), true);
            $modules = $modulesData['data'] ?? [];

            // Get progress for the course
            $progressResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId . '/progress');
            $progressData = json_decode($progressResponse->getBody(), true);
            
            // Extract progress data
            $allProgress = [];
            if (isset($progressData['data']['items']) && is_array($progressData['data']['items'])) {
                foreach ($progressData['data']['items'] as $item) {
                    $allProgress[$item['module_item_id']] = [
                        'status' => $item['status'],
                        'completed_at' => $item['completed_at'] ?? null
                    ];
                }
            }

            // Get course statistics including grades
            $statsResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId . '/statistics');
            $statsData = json_decode($statsResponse->getBody(), true);
            
            if (isset($statsData['data'])) {
                $course['grade'] = $statsData['data']['grade'] ?? 'N/A';
                $course['progress'] = $statsData['data']['progress_percentage'] ?? 0;
            }

            // Get submissions for all items
            $submissionsResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId . '/submissions');
            $submissionsData = json_decode($submissionsResponse->getBody(), true);
            $submissions = $submissionsData['data'] ?? [];

            // Process module items and attach submissions and progress
            foreach ($modules as &$module) {
                if (isset($module['module_items']) && is_array($module['module_items'])) {
                    foreach ($module['module_items'] as &$item) {
                        // Find submission for this item
                        $itemSubmission = null;
                        foreach ($submissions as $submission) {
                            if ($submission['module_item_id'] == $item['id']) {
                                $itemSubmission = $submission;
                                break;
                            }
                        }
                        
                        // Get progress for this item
                        $itemProgress = $allProgress[$item['id']] ?? null;
                        
                        // Attach submission and progress to item
                        $item['submission'] = $itemSubmission;
                        $item['progress'] = $itemProgress;
                    }
                }
            }

            $this->view('student/courses/content', [
                'course' => $course,
                'modules' => $modules,
                'progress' => $allProgress,
                'submissions' => $submissions,
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorData = json_decode($response->getBody(), true);
            $errorMessage = $errorData['message'] ?? 'Failed to load course content';
            
            $this->view('student/courses/content', [
                'error' => $errorMessage,
                'course' => null,
                'modules' => [],
                'progress' => [],
                'submissions' => [],
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (\Exception $e) {
            $this->view('student/courses/content', [
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
                'course' => null,
                'modules' => [],
                'progress' => [],
                'submissions' => [],
                'profile' => $_SESSION['user'] ?? null
            ]);
        }
    }

    public function showItem($courseId, $itemId)
    {
        try {
            // Get item details
            $itemResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId . '/items/' . $itemId);
            $itemData = json_decode($itemResponse->getBody(), true);
            
            if (!isset($itemData['data'])) {
                throw new \Exception('Invalid item data received from API');
            }
            
            $item = $itemData['data'];

            // Get course details for context
            $courseResponse = $this->client->get($this->apiBase . 'student/courses/' . $courseId);
            $courseData = json_decode($courseResponse->getBody(), true);
            $course = $courseData['data'] ?? [];

            $this->view('student/courses/item', [
                'course' => $course,
                'item' => $item,
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorData = json_decode($response->getBody(), true);
            $errorMessage = $errorData['message'] ?? 'Failed to load item';
            
            $this->view('student/courses/item', [
                'error' => $errorMessage,
                'course' => null,
                'item' => null,
                'profile' => $_SESSION['user'] ?? null
            ]);
        } catch (\Exception $e) {
            $this->view('student/courses/item', [
                'error' => 'An unexpected error occurred: ' . $e->getMessage(),
                'course' => null,
                'item' => null,
                'profile' => $_SESSION['user'] ?? null
            ]);
        }
    }

    public function submitItem($courseId, $itemId)
    {
        try {
            // Get the raw POST data
            $postData = file_get_contents('php://input');
            $data = json_decode($postData, true);

            // If it's a file upload, handle it differently
            if (!empty($_FILES)) {
                $data = [
                    'file_path' => $_FILES['file_path']['tmp_name'],
                    'content' => $_POST['content'] ?? null,
                    'comments' => $_POST['comments'] ?? null
                ];
            }

            // Make the API request
            $response = $this->client->post($this->apiBase . 'student/courses/' . $courseId . '/items/' . $itemId . '/submit', [
                'json' => $data
            ]);

            // Get the response body
            $responseData = json_decode($response->getBody(), true);
            
            if (!isset($responseData['success']) || !$responseData['success']) {
                throw new \Exception($responseData['message'] ?? 'Submission failed');
            }
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $responseData['data'] ?? null,
                'message' => 'Submission successful'
            ]);
            exit;

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorData = json_decode($response->getBody(), true);
            $errorMessage = $errorData['message'] ?? 'Failed to submit';
            
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function markComplete($courseId, $itemId)
    {
        try {
            // Ensure itemId is a valid integer
            $itemId = (int)$itemId;
            if ($itemId <= 0) {
                throw new \Exception('Invalid module item ID');
            }
            
            // Make the API request to mark the item as complete
            $response = $this->client->post($this->apiBase . 'student/courses/' . $courseId . '/items/' . $itemId . '/complete', [
                'json' => [
                    'module_item_id' => $itemId,
                    'status' => 'completed'
                ]
            ]);

            // Get the response body
            $responseData = json_decode($response->getBody(), true);
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $responseData['data'] ?? null,
                'message' => 'Item marked as complete'
            ]);
            exit;

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $errorData = json_decode($response->getBody(), true);
            $errorMessage = $errorData['message'] ?? 'Failed to mark item as complete';
            
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ]);
            exit;
        }
    }
} 