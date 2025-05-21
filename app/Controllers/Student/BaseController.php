<?php

namespace App\Controllers\Student;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class BaseController
{
    protected $apiBase = 'http://localhost/lms-api/api/public/api/students/';
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

    protected function view($view, $data = [])
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../../Views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception("View file not found: {$viewPath}");
        }
        
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        
        echo $content;
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function handleError(\Exception $e, $view, $data = [])
    {
        error_log('[Student BaseController] Error: ' . $e->getMessage());
        error_log('[Student BaseController] Stack trace: ' . $e->getTraceAsString());
        
        $errorMessage = 'An error occurred while processing your request.';
        
        if ($e instanceof ClientException) {
            $response = json_decode($e->getResponse()->getBody(), true);
            $errorMessage = $response['error']['message'] ?? $errorMessage;
        } elseif ($e instanceof ServerException) {
            $errorMessage = 'Server error. Please try again later.';
        }
        
        $data['error'] = $errorMessage;
        $this->view($view, $data);
    }

    protected function validateRequiredFields($fields, $data)
    {
        $missing = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('Required fields missing: ' . implode(', ', $missing));
        }
    }

    protected function getProfile()
    {
        try {
            $response = $this->client->get($this->apiBase . 'profile');
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                return $data['data'] ?? [];
            }
            
            error_log("Failed to fetch student profile: " . $response->getReasonPhrase());
            return [];
        } catch (\Exception $e) {
            error_log("Failed to fetch student profile: " . $e->getMessage());
            return [];
        }
    }

    protected function handleApiResponse($response, $defaultValue = [])
    {
        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody(), true);
            // Handle both nested data structure and root-level data
            return $data['data'] ?? $data ?? $defaultValue;
        }
        
        error_log("API call failed with status: " . $response->getStatusCode());
        return $defaultValue;
    }

    protected function handleApiException(\Exception $e, $endpoint)
    {
        error_log("API call to {$endpoint} failed: " . $e->getMessage());
        if ($e instanceof ClientException) {
            $response = json_decode($e->getResponse()->getBody(), true);
            throw new \Exception($response['error']['message'] ?? 'API request failed');
        }
        throw new \Exception('Failed to communicate with the server');
    }
}
