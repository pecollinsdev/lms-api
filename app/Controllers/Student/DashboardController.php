<?php

namespace App\Controllers\Student;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;

class DashboardController extends BaseController
{
    public function index()
    {
        try {
            error_log("=== Starting student dashboard index() method ===");

            // Make a single API call to get all dashboard data
            try {
                error_log("Fetching dashboard data from: " . $this->apiBase . 'dashboard');
                $response = $this->client->get($this->apiBase . 'dashboard');
                
                // Log raw response
                error_log("Raw API Response Status: " . $response->getStatusCode());
                error_log("Raw API Response Headers: " . print_r($response->getHeaders(), true));
                error_log("Raw API Response Body: " . $response->getBody()->getContents());
                
                // Reset the response body stream position for further processing
                $response->getBody()->rewind();
                
                $data = $this->handleApiResponse($response);
                error_log("Dashboard API Response: " . print_r($data, true));

                // Extract data from the response
                $profile = $data['profile'] ?? [];
                $courses = $data['courses'] ?? [];
                $upcomingDeadlines = $data['deadlines'] ?? [];
                $announcements = $data['announcements'] ?? [];
                $recentSubmissions = $data['submissions'] ?? [];
                $progress = $data['progress'] ?? [];

                // Calculate GPA
                $gpa = $this->calculateGPA($courses);

                error_log("Final data being passed to view:");
                error_log("Profile: " . print_r($profile, true));
                error_log("Courses: " . print_r($courses, true));
                error_log("Deadlines: " . print_r($upcomingDeadlines, true));
                error_log("Announcements: " . print_r($announcements, true));
                error_log("Submissions: " . print_r($recentSubmissions, true));
                error_log("Progress: " . print_r($progress, true));
                error_log("GPA: " . $gpa);

                $this->view('student/dashboard', [
                    'profile' => $profile,
                    'courses' => $courses,
                    'upcomingDeadlines' => $upcomingDeadlines,
                    'announcements' => $announcements,
                    'recentSubmissions' => $recentSubmissions,
                    'progress' => $progress,
                    'gpa' => $gpa
                ]);
            } catch (ClientException $e) {
                error_log("Client error fetching dashboard data: " . $e->getMessage());
                error_log("Response body: " . $e->getResponse()->getBody()->getContents());
                throw $e;
            } catch (ServerException $e) {
                error_log("Server error fetching dashboard data: " . $e->getMessage());
                throw $e;
            } catch (RequestException $e) {
                error_log("Request error fetching dashboard data: " . $e->getMessage());
                throw $e;
            } catch (\Exception $e) {
                error_log("Unexpected error fetching dashboard data: " . $e->getMessage());
                error_log("Dashboard error stack trace: " . $e->getTraceAsString());
                throw $e;
            }
        } catch (\Exception $e) {
            error_log('[DashboardController::index] Error: ' . $e->getMessage());
            error_log('[DashboardController::index] Stack trace: ' . $e->getTraceAsString());
            error_log('[DashboardController::index] Error type: ' . get_class($e));
            
            if ($e instanceof ClientException) {
                error_log('[DashboardController::index] Response status: ' . $e->getResponse()->getStatusCode());
                error_log('[DashboardController::index] Response body: ' . $e->getResponse()->getBody()->getContents());
            }
            
            $this->handleError($e, 'student/dashboard', [
                'profile' => [],
                'courses' => [],
                'upcomingDeadlines' => [],
                'announcements' => [],
                'recentSubmissions' => [],
                'progress' => [],
                'gpa' => 0
            ]);
        }
    }

    private function calculateGPA($courses)
    {
        if (empty($courses)) {
            return 0;
        }

        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($courses as $course) {
            if (isset($course['current_grade']) && isset($course['credits'])) {
                $grade = floatval($course['current_grade']);
                $credits = floatval($course['credits']);

                // Convert percentage to 4.0 scale
                $points = 0;
                if ($grade >= 90) $points = 4.0;
                elseif ($grade >= 80) $points = 3.0;
                elseif ($grade >= 70) $points = 2.0;
                elseif ($grade >= 60) $points = 1.0;

                $totalPoints += ($points * $credits);
                $totalCredits += $credits;
            }
        }

        return $totalCredits > 0 ? ($totalPoints / $totalCredits) : 0;
    }
}
