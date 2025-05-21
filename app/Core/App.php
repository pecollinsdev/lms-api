<?php

namespace App\Core;

class App
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Auth Routes
        if (isset($url[0]) && strtolower($url[0]) === 'auth') {
            $controllerClass = '\App\Controllers\AuthController';
            $controller = new $controllerClass;

            switch (strtolower($url[1] ?? '')) {
                case 'register':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->register();
                    } else {
                        $controller->showRegister();
                    }
                    exit;

                case 'login':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->login();
                    } else {
                        $controller->showLogin();
                    }
                    exit;

                case 'forgot-password':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->forgotPassword();
                    } else {
                        $controller->showForgotPassword();
                    }
                    exit;
            }
        }

        // Student Routes
        if (isset($url[0]) && strtolower($url[0]) === 'student') {
            // Check authentication for student routes
            if (!isset($_COOKIE['token']) || empty($_COOKIE['token'])) {
                header('Location: /lms-frontend/public/auth/login');
                exit;
            }

            // Dashboard Routes
            if (strtolower($url[1] ?? '') === 'dashboard') {
                $controllerClass = '\App\Controllers\Student\DashboardController';
                $controller = new $controllerClass;
                $controller->index();
                exit;
            }

            // Course Routes
            if (strtolower($url[1] ?? '') === 'courses') {
                $controllerClass = '\App\Controllers\Student\CourseController';
                $controller = new $controllerClass;

                // Course index
                if (!isset($url[2]) || $url[2] === '') {
                    $controller->index();
                    exit;
                }

                $courseId = $url[2];

                // Course content
                if (is_numeric($courseId) && strtolower($url[3] ?? '') === 'content') {
                    $controller->content($courseId);
                    exit;
                }

                // Course item
                if (is_numeric($courseId) && $url[3] === 'items' && is_numeric($url[4] ?? '')) {
                    $itemId = $url[4];
                    
                    // Item show
                    if (!isset($url[5]) || $url[5] === '') {
                        $controller->showItem($courseId, $itemId);
                        exit;
                    }

                    // Item submit
                    if ($url[5] === 'submit') {
                        $controller->submitItem($courseId, $itemId);
                        exit;
                    }

                    // Item complete
                    if ($url[5] === 'complete') {
                        $controller->markComplete($courseId, $itemId);
                        exit;
                    }
                }
            }
        }

        // Instructor Routes
        if (isset($url[0]) && strtolower($url[0]) === 'instructor') {
            // Check authentication for instructor routes
            if (!isset($_COOKIE['token']) || empty($_COOKIE['token'])) {
                header('Location: /lms-frontend/public/auth/login');
                exit;
            }

            // Direct Module Item Access - Redirect to course context
            if (strtolower($url[1] ?? '') === 'module-items' && is_numeric($url[2] ?? '')) {
                $itemId = $url[2];
                $moduleItemController = new \App\Controllers\Instructor\ModuleItemController();
                
                try {
                    // Fetch module item to get its module ID
                    $response = $moduleItemController->client->get($moduleItemController->apiBase . "module-items/{$itemId}");
                    $itemData = json_decode($response->getBody(), true);
                    $item = $itemData['data'] ?? [];
                    $moduleId = $item['module_id'] ?? null;

                    if ($moduleId) {
                        // Fetch module to get course ID
                        $moduleResponse = $moduleItemController->client->get($moduleItemController->apiBase . "modules/{$moduleId}");
                        $moduleData = json_decode($moduleResponse->getBody(), true);
                        $module = $moduleData['data'] ?? [];
                        $courseId = $module['course_id'] ?? null;

                        if ($courseId) {
                            // Redirect to the proper course context
                            header('Location: /lms-frontend/public/instructor/courses/' . $courseId . '/module-items/' . $itemId);
                            exit;
                        }
                    }
                } catch (\Exception $e) {
                    // If we can't get the course ID, redirect to courses index
                    header('Location: /lms-frontend/public/instructor/courses');
                    exit;
                }
            }

            // Dashboard Routes
            if (strtolower($url[1] ?? '') === 'dashboard') {
                $controllerClass = '\App\Controllers\Instructor\DashboardController';
                $controller = new $controllerClass;
                $controller->index();
                exit;
            }

            // Profile Routes
            if (strtolower($url[1] ?? '') === 'profile') {
                $controllerClass = '\App\Controllers\Instructor\DashboardController';
                $controller = new $controllerClass;

                switch (strtolower($url[2] ?? '')) {
                    case 'update':
                        $controller->updateProfile();
                        exit;
                    case 'change-password':
                        $controller->changePassword();
                        exit;
                    default:
                        $controller->profile();
                        exit;
                }
            }

            // Course Routes
            if (strtolower($url[1] ?? '') === 'courses') {
                $controllerClass = '\App\Controllers\Instructor\CourseController';
                $controller = new $controllerClass;

                // Course index
                if (!isset($url[2]) || $url[2] === '') {
                    $controller->index();
                    exit;
                }

                // Course create
                if ($url[2] === 'create') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->store();
                    } else {
                        $controller->create();
                    }
                    exit;
                }

                // Course show/edit/delete
                if (is_numeric($url[2])) {
                    $courseId = $url[2];

                    // Course show
                    if (!isset($url[3]) || $url[3] === '') {
                        $controller->show($courseId);
                        exit;
                    }

                    // Module Item View
                    if ($url[3] === 'module' && is_numeric($url[4]) && $url[5] === 'item' && is_numeric($url[6])) {
                        $moduleId = $url[4];
                        $itemId = $url[6];
                        $moduleItemController = new \App\Controllers\Instructor\ModuleItemController();
                        $moduleItemController->show($courseId, $itemId);
                        exit;
                    }

                    // Course students
                    if ($url[3] === 'students') {
                        // Students index
                        if (!isset($url[4]) || $url[4] === '') {
                            $controller->students($courseId);
                            exit;
                        }

                        // Enroll student
                        if ($url[4] === 'enroll') {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $controller->enrollStudent($courseId);
                            }
                            exit;
                        }

                        // Unenroll student
                        if ($url[4] === 'unenroll') {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $controller->unenroll($courseId);
                            }
                            exit;
                        }
                    }

                    // Course edit
                    if ($url[3] === 'edit') {
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $controller->update($courseId);
                        } else {
                            $controller->edit($courseId);
                        }
                        exit;
                    }

                    // Course delete
                    if ($url[3] === 'delete') {
                        $controller->delete($courseId);
                        exit;
                    }

                    // Course modules
                    if ($url[3] === 'modules') {
                        // Module create
                        if ($url[4] === 'create') {
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $controller->storeModule($courseId);
                            } else {
                                $controller->createModule($courseId);
                            }
                            exit;
                        }

                        // Module items
                        if (is_numeric($url[4]) && $url[5] === 'items') {
                            $moduleId = $url[4];
                            $moduleItemController = new \App\Controllers\Instructor\ModuleItemController();
                            
                            // Item create
                            if ($url[6] === 'create') {
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $moduleItemController->store($courseId, $moduleId);
                                } else {
                                    $moduleItemController->create($courseId, $moduleId);
                                }
                                exit;
                            }

                            // Item edit/update
                            if (is_numeric($url[6])) {
                                $itemId = $url[6];
                                
                                if ($url[7] === 'edit') {
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                        $moduleItemController->update($courseId, $moduleId, $itemId);
                                    } else {
                                        $moduleItemController->edit($courseId, $moduleId, $itemId);
                                    }
                                    exit;
                                }

                                // Item delete
                                if ($url[7] === 'delete') {
                                    $moduleItemController->delete($courseId, $moduleId, $itemId);
                                    exit;
                                }

                                // Item show
                                $moduleItemController->show($courseId, $moduleId, $itemId);
                                exit;
                            }
                        }
                    }

                    // Direct Item Access
                    if ($url[3] === 'items' && is_numeric($url[4] ?? '')) {
                        $itemId = $url[4];
                        $moduleItemController = new \App\Controllers\Instructor\ModuleItemController();
                        
                        // Item show
                        if (!isset($url[5]) || $url[5] === '') {
                            $moduleItemController->show($courseId, $itemId);
                            exit;
                        }

                        // Submissions for Item
                        if (strtolower($url[5] ?? '') === 'submissions') {
                            $submissionController = new \App\Controllers\Instructor\SubmissionController();

                            // Submissions index
                            if (!isset($url[6]) || $url[6] === '') {
                                $submissionController->index($courseId, $itemId);
                                exit;
                            }

                            // Submission show
                            if (is_numeric($url[6])) {
                                $submissionId = $url[6];

                                // Submission show
                                if (!isset($url[7]) || $url[7] === '') {
                                    $submissionController->show($courseId, $itemId, $submissionId);
                                    exit;
                                }

                                // Submission grade
                                if ($url[7] === 'grade') {
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                        $submissionController->processGrade($courseId, $itemId, $submissionId);
                                    } else {
                                        $submissionController->grade($courseId, $itemId, $submissionId);
                                    }
                                    exit;
                                }
                            }
                        }
                    }

                    // Module Items within Course
                    if ($url[3] === 'module-items') {
                        $moduleItemController = new \App\Controllers\Instructor\ModuleItemController();

                        // Module Items index
                        if (!isset($url[4]) || $url[4] === '') {
                            $moduleItemController->index($courseId);
                            exit;
                        }

                        // Module Item create
                        if ($url[4] === 'create') {
                            $moduleItemController->store($courseId);
                            exit;
                        }

                        // Module Item show/edit/delete
                        if (is_numeric($url[4])) {
                            $itemId = $url[4];

                            // Module Item show
                            if (!isset($url[5]) || $url[5] === '') {
                                $moduleItemController->show($courseId, $itemId);
                                exit;
                            }

                            // Module Item edit
                            if ($url[5] === 'edit') {
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $moduleItemController->update($courseId, null, $itemId);
                                } else {
                                    $moduleItemController->edit($courseId, null, $itemId);
                                }
                                exit;
                            }

                            // Module Item delete
                            if ($url[5] === 'delete') {
                                $moduleItemController->delete($courseId, null, $itemId);
                                exit;
                            }
                        }
                    }

                    // Submissions within Course
                    if ($url[3] === 'items' && is_numeric($url[4] ?? '')) {
                        $itemId = $url[4];
                        $submissionController = new \App\Controllers\Instructor\SubmissionController();

                        // Submissions index
                        if (strtolower($url[5] ?? '') === 'submissions') {
                            if (!isset($url[6]) || $url[6] === '') {
                                $submissionController->index($courseId, $itemId);
                                exit;
                            }

                            // Submission show
                            if (is_numeric($url[6])) {
                                $submissionId = $url[6];

                                // Submission show
                                if (!isset($url[7]) || $url[7] === '') {
                                    $submissionController->show($courseId, $itemId, $submissionId);
                                    exit;
                                }

                                // Submission grade
                                if ($url[7] === 'grade') {
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                        $submissionController->processGrade($courseId, $itemId, $submissionId);
                                    } else {
                                        $submissionController->grade($courseId, $itemId, $submissionId);
                                    }
                                    exit;
                                }
                            }
                        }
                    }

                    // Assignments within Course
                    if ($url[3] === 'assignments') {
                        $assignmentController = new \App\Controllers\Instructor\AssignmentController();
                        $assignmentController->index($courseId);
                        exit;
                    }
                }
            }

            // Quiz Routes
            if (strtolower($url[1] ?? '') === 'modules' && is_numeric($url[2] ?? '') && strtolower($url[3] ?? '') === 'questions') {
                $controllerClass = '\App\Controllers\Instructor\QuizController';
                $controller = new $controllerClass;
                $moduleId = $url[2];

                // Add question
                if (strtolower($url[4] ?? '') === 'add') {
                    $controller->addQuestion($_GET['course_id'] ?? null, $moduleId);
                    exit;
                }

                // Edit/Delete question
                if (is_numeric($url[4] ?? '')) {
                    $questionId = $url[4];

                    if (strtolower($url[5] ?? '') === 'edit') {
                        $controller->updateQuestion($_GET['course_id'] ?? null, $moduleId, $questionId);
                        exit;
                    }

                    if (strtolower($url[5] ?? '') === 'delete') {
                        $controller->deleteQuestion($_GET['course_id'] ?? null, $moduleId, $questionId);
                        exit;
                    }
                }
            }
        }

        // Default routing
        if (isset($url[0]) && file_exists('../app/Controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        $controllerClass = '\\App\\Controllers\\' . $this->controller;
        $this->controller = new $controllerClass;

        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            // Remove any double slashes and trim
            $url = preg_replace('#/+#', '/', $_GET['url']);
            $url = trim($url, '/');
            return explode('/', filter_var($url, FILTER_SANITIZE_URL));
        }
        return [];
    }
}
