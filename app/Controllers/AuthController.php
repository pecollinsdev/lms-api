<?php

namespace App\Controllers;

use App\Core\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class AuthController extends Controller
{
    protected $apiBase = 'http://localhost/lms-api/api/public/api/';
    protected $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar();
    }

    protected function getClient()
    {
        return new Client([
            'base_uri' => $this->apiBase,
            'timeout'  => 5.0,
            'verify'   => false, // Set to true in production with proper SSL
            'cookies' => $this->cookieJar
        ]);
    }

    public function showRegister()
    {
        $this->view('register');
    }

    public function register()
    {
        $client = $this->getClient();
    
        try {
            $name = trim(($_POST['first_name'] ?? '') . ' ' . ($_POST['last_name'] ?? ''));
            $data = [
                'name' => $name,
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'password_confirmation' => $_POST['password_confirmation'] ?? '',
                'role' => $_POST['role'] ?? 'student',
                'phone_number' => $_POST['phone_number'] ?? '',
                'bio' => $_POST['bio'] ?? '',
            ];
            if (($data['role'] ?? '') === 'instructor') {
                $data['instructor_code'] = $_POST['instructor_code'] ?? '';
                $data['academic_specialty'] = $_POST['academic_specialty'] ?? '';
                $data['qualifications'] = $_POST['qualifications'] ?? '';
            }

            $response = $client->post('register', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);
    
            $body = json_decode($response->getBody(), true);
            $token = $body['token'] ?? $body['data']['token'] ?? null;
            $role = $body['role'] ?? $body['data']['role'] ?? 'student';
    
            if ($token) {
                // Set the token cookie
                setcookie('token', $token, time() + 3600, '/', '', false, true); // HttpOnly cookie
            }
    
            // Redirect based on role
            if ($role === 'instructor') {
                header('Location: /lms-frontend/public/instructor/dashboard');
            } else {
                header('Location: /lms-frontend/public/student/dashboard');
            }
            exit;
        } catch (\Exception $e) {
            $this->view('register', ['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function showLogin()
    {
        $this->view('login');
    }

    public function login()
    {    
        $client = $this->getClient();
    
        try {
            $response = $client->post('login', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'email'    => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'role'     => $_POST['role'] ?? 'student',
                ]
            ]);
    
            $body = json_decode($response->getBody(), true);
            
            // Debug the response
            error_log('Login API Response: ' . print_r($body, true));
            
            // Get all response headers for debugging
            error_log('Response Headers: ' . print_r($response->getHeaders(), true));
            
            // Try to get token from various possible locations
            $token = null;
            
            // Check Authorization header
            $authHeader = $response->getHeader('Authorization');
            if (!empty($authHeader)) {
                $token = str_replace('Bearer ', '', $authHeader[0]);
            }
            
            // Check Set-Cookie header
            $cookies = $response->getHeader('Set-Cookie');
            if (!empty($cookies)) {
                foreach ($cookies as $cookie) {
                    if (strpos($cookie, 'token=') !== false) {
                        preg_match('/token=([^;]+)/', $cookie, $matches);
                        if (isset($matches[1])) {
                            $token = $matches[1];
                            break;
                        }
                    }
                }
            }
            
            $role = $body['data']['user']['role'] ?? $body['data']['role'] ?? $body['role'] ?? 'student';
            
            // Debug role detection
            error_log('Detected Role: ' . $role);
            error_log('POST Role: ' . ($_POST['role'] ?? 'not set'));
            error_log('Token found: ' . ($token ? 'yes' : 'no'));
    
            if (!$token) {
                throw new \Exception('No authentication token received');
            }
    
            // Set the token cookie
            setcookie('token', $token, time() + 3600, '/', '', false, true); // HttpOnly cookie
    
            // Debug before redirect
            error_log('Redirecting to: ' . ($role === 'instructor' ? '/lms-frontend/public/instructor/dashboard' : '/lms-frontend/public/dashboard'));
            
            // Redirect based on role
            if ($role === 'instructor') {
                header('Location: /lms-frontend/public/instructor/dashboard');
            } else {
                header('Location: /lms-frontend/public/dashboard');
            }
            exit;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody(), true);
            $errorMessage = $body['message'] ?? 'Login failed: ' . $e->getMessage();
            $this->view('login', ['error' => $errorMessage]);
        } catch (\Exception $e) {
            $this->view('login', ['error' => 'Login failed: ' . $e->getMessage()]);
        }
    }

    public function showForgotPassword()
    {
        $this->view('forgot-password');
    }

    public function forgotPassword()
    {
        $client = $this->getClient();
    
        try {
            $response = $client->post('forgot-password', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'email' => $_POST['email'] ?? ''
                ]
            ]);
    
            $body = json_decode($response->getBody(), true);
            
            if (isset($body['message'])) {
                $this->view('forgot-password', ['success' => $body['message']]);
            } else {
                $this->view('forgot-password', ['success' => 'If your email is registered, you will receive password reset instructions shortly.']);
            }
        } catch (\Exception $e) {
            $this->view('forgot-password', ['error' => 'Failed to process request: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        $client = $this->getClient();
        
        try {
            // Get the token from the cookie
            $token = $_COOKIE['token'] ?? null;
            
            if ($token) {
                // Call the API logout endpoint to invalidate the token
                $client->post('logout', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ]
                ]);
            }
        } catch (\Exception $e) {
            // Even if the API call fails, we'll still clear the local cookie
            error_log('Logout API call failed: ' . $e->getMessage());
        }

        // Clear the token cookie by setting it to expire in the past
        setcookie('token', '', time() - 3600, '/', '', false, true);
        
        // Redirect to login page
        header('Location: /lms-frontend/public/login');
        exit;
    }
}
