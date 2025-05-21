<?php

namespace App\Controllers\Instructor;

use App\Core\Controller;
use GuzzleHttp\Client;

class BaseController extends Controller
{
    protected $apiBase = 'http://localhost/lms-api/api/public/api/';
    protected $client;

    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function __construct()
    {
        $token = $_COOKIE['token'] ?? '';
        if (empty($token)) {
            $this->handleAuthFailure();
            return;
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

    protected function handleAuthFailure()
    {
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Authentication required. Please log in.']);
            exit;
        }
        $this->redirect('/auth/login');
        exit;
    }

    protected function getProfile()
    {
        try {
            $response = $this->client->get($this->apiBase . 'instructor/dashboard');
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch instructor profile');
            }
            $dashboardData = json_decode($response->getBody(), true);
            return $dashboardData['profile'] ?? [];
        } catch (\Exception $e) {
            error_log('Failed to fetch instructor profile: ' . $e->getMessage());
            return [];
        }
    }

    protected function handleError($e, $view, $data = [])
    {
        error_log($e->getMessage());
        $this->view($view, array_merge([
            'error' => $e->getMessage()
        ], $data));
    }

    protected function redirect($url)
    {
        header("Location: /lms-frontend/public{$url}");
        exit;
    }

    protected function validateRequiredFields($fields, $data)
    {
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                throw new \Exception("The {$field} field is required.");
            }
        }
    }

    protected function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email format.");
        }
    }

    protected function validateDateRange($startDate, $endDate)
    {
        if (!empty($startDate) && !empty($endDate)) {
            $start = new \DateTime($startDate);
            $end = new \DateTime($endDate);
            if ($end < $start) {
                throw new \Exception("The end date must be a date after or equal to start date.");
            }
        }
    }

    protected function validateNumericRange($value, $min, $max, $fieldName)
    {
        if (!is_numeric($value)) {
            throw new \Exception("{$fieldName} must be a number.");
        }
        $value = floatval($value);
        if ($value < $min || $value > $max) {
            throw new \Exception("{$fieldName} must be between {$min} and {$max}.");
        }
    }

    protected function validateStringLength($value, $maxLength, $fieldName)
    {
        if (strlen($value) > $maxLength) {
            throw new \Exception("The {$fieldName} may not be greater than {$maxLength} characters.");
        }
    }

    protected function validateInArray($value, $array, $fieldName)
    {
        if (!in_array($value, $array)) {
            throw new \Exception("Invalid {$fieldName}.");
        }
    }
} 