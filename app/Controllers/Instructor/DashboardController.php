<?php

namespace App\Controllers\Instructor;

use App\Core\Controller;
use GuzzleHttp\Client;

class DashboardController extends BaseController
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
            // Fetch dashboard data
            $response = $this->client->get($this->apiBase . 'dashboard/instructor');
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch dashboard data: ' . $response->getReasonPhrase());
            }
            $data = json_decode($response->getBody(), true);
            error_log('Dashboard API Response: ' . print_r($data, true));
            $dashboard = $data['data'] ?? [];
            error_log('Dashboard Data: ' . print_r($dashboard, true));
            
            // Debug each section of data
            error_log('Course Stats: ' . print_r($dashboard['course_stats'] ?? [], true));
            error_log('Recent Module Items: ' . print_r($dashboard['recent_module_items'] ?? [], true));
            error_log('Pending Submissions: ' . print_r($dashboard['pending_submissions'] ?? [], true));
            error_log('Calendar Data: ' . print_r($dashboard['calendar_data'] ?? [], true));
            
            // Ensure we have the correct data structure
            $viewData = [
                'courses' => $dashboard['course_stats'] ?? [],
                'submissions' => $dashboard['pending_submissions'] ?? [],
                'notifications' => $dashboard['recent_module_items'] ?? [],
                'calendar_data' => $dashboard['calendar_data'] ?? [],
                'profile' => $dashboard['profile'] ?? []
            ];
            
            error_log('View Data Being Passed: ' . print_r($viewData, true));

            $this->view('instructor/dashboard', $viewData);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/dashboard', [
                'course_stats' => [],
                'recent_module_items' => [],
                'pending_submissions' => [],
                'calendar_data' => []
            ]);
        }
    }

    public function profile()
    {
        try {
            // Fetch profile data
            $response = $this->client->get($this->apiBase . 'instructor/profile');
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch profile data: ' . $response->getReasonPhrase());
            }
            $data = json_decode($response->getBody(), true);
            $profile = $data['data'] ?? [];

            $this->view('instructor/profile', [
                'profile' => $profile
            ]);
        } catch (\Exception $e) {
            $this->handleError($e, 'instructor/profile', [
                'profile' => []
            ]);
        }
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['name', 'email'], $_POST);

                // Validate email format
                $this->validateEmail($_POST['email']);

                // Update profile
                $response = $this->client->put($this->apiBase . 'instructor/profile', [
                    'json' => [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'bio' => $_POST['bio'] ?? null,
                        'phone' => $_POST['phone'] ?? null,
                        'address' => $_POST['address'] ?? null
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $this->redirect('/instructor/profile');
                } else {
                    throw new \Exception('Failed to update profile. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch current profile data
                $profileResponse = $this->client->get($this->apiBase . 'instructor/profile');
                $profileData = json_decode($profileResponse->getBody(), true);
                $profile = $profileData['data'] ?? [];

                $this->view('instructor/profile', [
                    'error' => $e->getMessage(),
                    'profile' => $profile
                ]);
            }
        }
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['current_password', 'new_password', 'new_password_confirmation'], $_POST);

                // Validate password confirmation
                if ($_POST['new_password'] !== $_POST['new_password_confirmation']) {
                    throw new \Exception('New password and confirmation do not match.');
                }

                // Change password
                $response = $this->client->put($this->apiBase . 'instructor/change-password', [
                    'json' => [
                        'current_password' => $_POST['current_password'],
                        'new_password' => $_POST['new_password'],
                        'new_password_confirmation' => $_POST['new_password_confirmation']
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $this->redirect('/instructor/profile');
                } else {
                    throw new \Exception('Failed to change password. Please try again.');
                }
            } catch (\Exception $e) {
                // Fetch current profile data
                $profileResponse = $this->client->get($this->apiBase . 'instructor/profile');
                $profileData = json_decode($profileResponse->getBody(), true);
                $profile = $profileData['data'] ?? [];

                $this->view('instructor/profile', [
                    'error' => $e->getMessage(),
                    'profile' => $profile
                ]);
            }
        }
    }
} 