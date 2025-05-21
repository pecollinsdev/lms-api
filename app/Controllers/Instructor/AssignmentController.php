<?php

namespace App\Controllers\Instructor;

class AssignmentController extends BaseController
{
    public function index($courseId)
    {
        try {
            // Get page number from query string
            $page = $_GET['page'] ?? 1;
            
            // Fetch submittable items with pagination and include module and submission status
            $response = $this->client->get($this->apiBase . "courses/{$courseId}/submittable-items", [
                'query' => [
                    'page' => $page,
                    'include' => 'module',
                    'fields' => 'id,type,title,description,order,max_score,submission_type,due_date,created_at,updated_at,is_public,module'
                ]
            ]);
            
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch submittable items: ' . $response->getReasonPhrase());
            }

            $responseData = json_decode($response->getBody(), true);
            
            // Log the response data for debugging
            error_log('Submittable Items Response: ' . print_r($responseData, true));
            
            // Ensure proper data structure
            $submittableItems = [
                'data' => $responseData['data'] ?? [],
                'meta' => [
                    'current_page' => $responseData['meta']['current_page'] ?? 1,
                    'per_page' => $responseData['meta']['per_page'] ?? 15,
                    'total' => $responseData['meta']['total'] ?? 0
                ]
            ];

            return $this->view('instructor/courses/assignments', [
                'submittableItems' => $submittableItems,
                'courseId' => $courseId,
                'profile' => $this->getProfile()
            ]);
        } catch (\Exception $e) {
            error_log('API Error: ' . $e->getMessage());
            return $this->handleError($e, 'instructor/courses/assignments', [
                'submittableItems' => ['data' => [], 'meta' => ['total' => 0, 'current_page' => 1, 'per_page' => 15]],
                'courseId' => $courseId,
                'profile' => $this->getProfile()
            ]);
        }
    }
} 