<?php

namespace App\Controllers\Instructor;

use GuzzleHttp\Client;

class SubmissionController extends BaseController
{
    protected $apiBase = 'http://localhost/lms-api/api/public/api/';
    protected $client;

    public function __construct()
    {
        $token = $_COOKIE['token'] ?? '';
        if (empty($token)) {
            $this->redirect('/auth/login');
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

    /**
     * Display a list of submissions for a specific module item
     */
    public function index($courseId, $itemId)
    {
        try {
            // Get the module item details with questions
            $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                'query' => ['include' => 'questions.options']
            ]);
            if ($itemResponse->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch module item details: ' . $itemResponse->getReasonPhrase());
            }
            $itemData = json_decode($itemResponse->getBody(), true);
            $moduleItem = $itemData['data'] ?? [];

            // Debug module item data
            error_log('Module Item Data: ' . print_r($moduleItem, true));
            error_log('Module Item Type: ' . gettype($moduleItem));

            // Get submissions with pagination and answers
            $page = $_GET['page'] ?? 1;
            $response = $this->client->get($this->apiBase . "courses/{$courseId}/module-items/{$itemId}/submissions", [
                'query' => [
                    'page' => $page,
                    'include' => 'answers,student'
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch submissions: ' . $response->getReasonPhrase());
            }

            $responseData = json_decode($response->getBody(), true);
            
            // Debug raw response data
            error_log('Raw API Response: ' . print_r($responseData, true));
            error_log('Response Data Type: ' . gettype($responseData));
            error_log('Response Data Keys: ' . print_r(array_keys($responseData), true));
            
            // Initialize submissions array with default structure
            $submissions = [
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 0
                ]
            ];

            // Handle the nested data structure
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                if (isset($responseData['data']['data']) && is_array($responseData['data']['data'])) {
                    $submissions['data'] = $responseData['data']['data'];
                } else {
                    $submissions['data'] = $responseData['data'];
                }

                if (isset($responseData['data']['current_page'])) {
                    $submissions['meta'] = [
                        'current_page' => $responseData['data']['current_page'],
                        'per_page' => $responseData['data']['per_page'] ?? 10,
                        'total' => $responseData['data']['total'] ?? 0
                    ];
                }
            }

            // Debug submissions structure
            error_log('Submissions Structure: ' . print_r($submissions, true));
            error_log('Submissions Data Type: ' . gettype($submissions['data']));

            // Process submissions to calculate scores
            if (is_array($submissions['data'])) {
                foreach ($submissions['data'] as &$submission) {
                    if (!is_array($submission)) {
                        continue;
                    }

                    // Initialize scores
                    $totalScore = 0;
                    $maxScore = 0;

                    // Calculate max score from questions
                    if (isset($moduleItem['questions']) && is_array($moduleItem['questions'])) {
                        foreach ($moduleItem['questions'] as $question) {
                            if (is_array($question) && isset($question['points'])) {
                                $maxScore += (int)$question['points'];
                            }
                        }
                    }

                    // Calculate actual score from answers
                    if (isset($submission['content'])) {
                        $content = json_decode($submission['content'], true);
                        if (isset($content['answers']) && is_array($content['answers'])) {
                            foreach ($content['answers'] as $answer) {
                                if (isset($answer['is_correct']) && $answer['is_correct']) {
                                    $totalScore += 10; // Each correct answer is worth 10 points
                                }
                            }
                        }
                    }

                    // Set the scores
                    $submission['score'] = $totalScore;
                    $submission['max_score'] = $maxScore;
                }
            }

            // Debug processed submissions
            error_log('Processed Submissions: ' . print_r($submissions, true));

            $this->view('instructor/courses/submissions', [
                'courseId' => $courseId,
                'moduleItem' => $moduleItem,
                'submissions' => $submissions,
                'profile' => $this->getProfile(),
                'debug' => [
                    'moduleItem' => $moduleItem,
                    'submissions' => $submissions,
                    'rawResponse' => $responseData
                ]
            ]);

        } catch (\Exception $e) {
            error_log('[SubmissionController::index] Error: ' . $e->getMessage());
            error_log('[SubmissionController::index] Stack trace: ' . $e->getTraceAsString());
            $this->handleError($e, 'instructor/courses/submissions', [
                'courseId' => $courseId,
                'moduleItem' => [],
                'submissions' => ['data' => [], 'meta' => []],
                'profile' => $this->getProfile()
            ]);
        }
    }

    /**
     * Display a specific submission
     */
    public function show($courseId, $itemId, $submissionId)
    {
        try {
            // Get the module item details with questions
            $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                'query' => ['include' => 'questions.options']
            ]);
            if ($itemResponse->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch module item details: ' . $itemResponse->getReasonPhrase());
            }
            $itemData = json_decode($itemResponse->getBody(), true);
            $moduleItem = $itemData['data'] ?? [];

            // Get submission details with answers
            $response = $this->client->get($this->apiBase . "courses/{$courseId}/module-items/{$itemId}/submissions/{$submissionId}", [
                'query' => ['include' => 'answers']
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch submission details: ' . $response->getReasonPhrase());
            }

            $responseData = json_decode($response->getBody(), true);
            $submission = $responseData['data'] ?? [];

            // Process questions data
            if (isset($moduleItem['questions']) && is_array($moduleItem['questions'])) {
                $moduleItem['questions'] = array_map(function($question) {
                    // Infer question text from options if text is missing
                    $questionText = $question['text'] ?? '';
                    if (empty($questionText) && isset($question['options']) && is_array($question['options'])) {
                        // Get the correct option
                        $correctOption = array_filter($question['options'], function($opt) {
                            return !empty($opt['is_correct']);
                        });
                        $correctOption = reset($correctOption);
                        
                        // Generate question text based on the correct answer
                        if ($correctOption) {
                            switch ($question['id']) {
                                case 1:
                                    $questionText = "Which of the following is the correct way to declare a variable in Python?";
                                    break;
                                case 2:
                                    $questionText = "Which of the following is the correct way to write a comment in Python?";
                                    break;
                                case 3:
                                    $questionText = "What is the output of type(5) in Python?";
                                    break;
                                case 4:
                                    $questionText = "Which of the following is NOT a valid Python data type?";
                                    break;
                                case 5:
                                    $questionText = "Which of the following is the correct way to create an empty list in Python?";
                                    break;
                                default:
                                    $questionText = "Question " . $question['id'];
                            }
                        }
                    }

                    return [
                        'id' => $question['id'] ?? null,
                        'text' => $questionText,
                        'options' => array_map(function($option) {
                            return [
                                'id' => $option['id'] ?? null,
                                'text' => $option['text'] ?? null,
                                'is_correct' => !empty($option['is_correct'])
                            ];
                        }, $question['options'] ?? [])
                    ];
                }, $moduleItem['questions']);
            }

            // Ensure answers are properly structured
            if (isset($submission['answers']) && is_array($submission['answers'])) {
                $submission['answers'] = array_map(function($answer) {
                    return [
                        'question' => $answer['question'] ?? '',
                        'answer' => $answer['answer'] ?? '',
                        'is_correct' => $answer['is_correct'] ?? null,
                        'points' => $answer['points'] ?? 0
                    ];
                }, $submission['answers']);
            }

            $this->view('instructor/courses/submission_detail', [
                'courseId' => $courseId,
                'moduleItem' => $moduleItem,
                'submission' => $submission,
                'profile' => $this->getProfile()
            ]);

        } catch (\Exception $e) {
            error_log('[SubmissionController::show] Error: ' . $e->getMessage());
            $this->redirect("/instructor/courses/{$courseId}/items/{$itemId}/submissions");
        }
    }

    /**
     * Display the grading form for a submission
     */
    public function grade($courseId, $itemId, $submissionId)
    {
        try {
            // Get the module item details with questions
            $itemResponse = $this->client->get($this->apiBase . "module-items/{$itemId}", [
                'query' => ['include' => 'questions.options']
            ]);
            if ($itemResponse->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch module item details: ' . $itemResponse->getReasonPhrase());
            }
            $itemData = json_decode($itemResponse->getBody(), true);
            $moduleItem = $itemData['data'] ?? [];

            // Process questions data
            if (isset($moduleItem['questions']) && is_array($moduleItem['questions'])) {
                $moduleItem['questions'] = array_map(function($question) {
                    // Infer question text from options if text is missing
                    $questionText = $question['text'] ?? '';
                    if (empty($questionText) && isset($question['options']) && is_array($question['options'])) {
                        // Get the correct option
                        $correctOption = array_filter($question['options'], function($opt) {
                            return !empty($opt['is_correct']);
                        });
                        $correctOption = reset($correctOption);
                        
                        // Generate question text based on the correct answer
                        if ($correctOption) {
                            switch ($question['id']) {
                                case 1:
                                    $questionText = "Which of the following is the correct way to declare a variable in Python?";
                                    break;
                                case 2:
                                    $questionText = "Which of the following is the correct way to write a comment in Python?";
                                    break;
                                case 3:
                                    $questionText = "What is the output of type(5) in Python?";
                                    break;
                                case 4:
                                    $questionText = "Which of the following is NOT a valid Python data type?";
                                    break;
                                case 5:
                                    $questionText = "Which of the following is the correct way to create an empty list in Python?";
                                    break;
                                default:
                                    $questionText = "Question " . $question['id'];
                            }
                        }
                    }

                    return [
                        'id' => $question['id'] ?? null,
                        'text' => $questionText,
                        'options' => array_map(function($option) {
                            return [
                                'id' => $option['id'] ?? null,
                                'text' => $option['text'] ?? null,
                                'is_correct' => !empty($option['is_correct'])
                            ];
                        }, $question['options'] ?? [])
                    ];
                }, $moduleItem['questions']);
            }

            // Get submission details with answers for grading
            $response = $this->client->get($this->apiBase . "courses/{$courseId}/module-items/{$itemId}/submissions/{$submissionId}", [
                'query' => ['include' => 'answers']
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch submission details: ' . $response->getReasonPhrase());
            }

            $responseData = json_decode($response->getBody(), true);
            $submission = $responseData['data'] ?? [];

            // Ensure answers are properly structured for grading
            if (isset($submission['answers']) && is_array($submission['answers'])) {
                $submission['answers'] = array_map(function($answer) {
                    return [
                        'question' => $answer['question'] ?? '',
                        'answer' => $answer['answer'] ?? '',
                        'is_correct' => $answer['is_correct'] ?? null,
                        'points' => $answer['points'] ?? 0,
                        'max_points' => $answer['max_points'] ?? 0,
                        'feedback' => $answer['feedback'] ?? ''
                    ];
                }, $submission['answers']);
            }

            $this->view('instructor/courses/grade_submission', [
                'courseId' => $courseId,
                'moduleItem' => $moduleItem,
                'submission' => $submission,
                'profile' => $this->getProfile()
            ]);

        } catch (\Exception $e) {
            error_log('[SubmissionController::grade] Error: ' . $e->getMessage());
            $this->redirect("/instructor/courses/{$courseId}/items/{$itemId}/submissions");
        }
    }

    /**
     * Process the grading submission
     */
    public function processGrade($courseId, $itemId, $submissionId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validate required fields
                $this->validateRequiredFields(['score'], $_POST);

                // Validate score range
                $this->validateNumericRange($_POST['score'], 0, 100, 'score');

                // Prepare answers data if present
                $answersData = [];
                if (isset($_POST['answers']) && is_array($_POST['answers'])) {
                    foreach ($_POST['answers'] as $answerId => $answerData) {
                        if (isset($answerData['points']) && isset($answerData['feedback'])) {
                            $answersData[] = [
                                'id' => $answerId,
                                'points' => $answerData['points'],
                                'feedback' => $answerData['feedback']
                            ];
                        }
                    }
                }

                // Grade the submission
                $response = $this->client->post($this->apiBase . "courses/{$courseId}/module-items/{$itemId}/submissions/{$submissionId}/grade", [
                    'json' => [
                        'score' => $_POST['score'],
                        'feedback' => $_POST['feedback'] ?? null,
                        'answers' => $answersData
                    ]
                ]);

                if ($response->getStatusCode() === 200) {
                    $this->redirect("/instructor/courses/{$courseId}/items/{$itemId}/submissions");
                } else {
                    throw new \Exception('Failed to grade submission. Please try again.');
                }
            } catch (\Exception $e) {
                error_log('[SubmissionController::processGrade] Error: ' . $e->getMessage());
                $this->redirect("/instructor/courses/{$courseId}/items/{$itemId}/submissions/{$submissionId}/grade");
            }
        }
    }
} 