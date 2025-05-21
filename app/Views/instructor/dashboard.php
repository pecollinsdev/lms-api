<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/lms-frontend/public/instructor/dashboard">LMS Instructor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/lms-frontend/public/instructor/courses">Courses</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($profile['name'] ?? 'Instructor') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/lms-frontend/public/instructor/profile">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/lms-frontend/public/auth/logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 pt-5">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Panel -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($profile['profile_picture'] ?? '/lms-frontend/public/images/default-avatar.png') ?>" 
                                 alt="Profile Picture" 
                                 class="rounded-circle me-3"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <div>
                                <h4 class="mb-1">Welcome back, <?= htmlspecialchars($profile['name'] ?? 'Instructor') ?>!</h4>
                                <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Courses</h5>
                        <h2 class="mb-0"><?= count($courses ?? []) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- My Courses -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Courses</h5>
                        <div>
                            <a href="/lms-frontend/public/instructor/courses" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-list me-1"></i> View All
                            </a>
                            <a href="/lms-frontend/public/instructor/courses/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> New Course
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p class="text-muted mb-0">No courses found.</p>
                        <?php else: ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <?php foreach ($courses as $course): ?>
                                    <div class="col">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                                <p class="card-text text-muted">
                                                    <small>Students: <?= $course['student_count'] ?? 0 ?></small>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <?php 
                                                            // Find the course in calendar data to get dates
                                                            $courseDates = null;
                                                            if (!empty($calendar_data['courses'])) {
                                                                foreach ($calendar_data['courses'] as $calendarCourse) {
                                                                    if ($calendarCourse['id'] == $course['id']) {
                                                                        $courseDates = $calendarCourse;
                                                                        break;
                                                                    }
                                                                }
                                                            }
                                                            if ($courseDates) {
                                                                echo date('M d, Y', strtotime($courseDates['start_date'])) . ' - ' . 
                                                                     date('M d, Y', strtotime($courseDates['end_date']));
                                                            } else {
                                                                echo 'Dates not available';
                                                            }
                                                        ?>
                                                    </small>
                                                    <div class="btn-group">
                                                        <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/assignments" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-tasks"></i>
                                                        </a>
                                                        <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/progress" class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-chart-line"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Submissions -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Pending Submissions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submissions)): ?>
                            <p class="text-muted mb-0">No pending submissions.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($submissions as $submission): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($submission['module_item_title'] ?? 'Untitled Assignment') ?></h6>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($submission['student_name'] ?? 'Unknown Student') ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-secondary">
                                                    <?= date('M d, Y H:i', strtotime($submission['submitted_at'] ?? 'now')) ?>
                                                </span>
                                                <a href="/lms-frontend/public/instructor/courses/<?= $submission['course_id'] ?? '' ?>/submissions/<?= $submission['id'] ?? '' ?>/grade" 
                                                   class="btn btn-sm btn-primary ms-2">
                                                    Grade
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Course Statistics -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Course Statistics</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p class="text-muted mb-0">No course statistics available.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php 
                                $totalStudents = 0;
                                $totalAssignments = 0;
                                $averageGrade = 0;
                                $gradeSum = 0;
                                $gradeCount = 0;
                                
                                foreach ($courses as $course) {
                                    $totalStudents += $course['student_count'] ?? 0;
                                    $totalAssignments += $course['assignment_count'] ?? 0;
                                    if (isset($course['average_grade'])) {
                                        $gradeSum += $course['average_grade'];
                                        $gradeCount++;
                                    }
                                }
                                
                                $averageGrade = $gradeCount > 0 ? round($gradeSum / $gradeCount, 1) : 0;
                                ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Students</span>
                                        <span class="fw-bold"><?= $totalStudents ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Assignments</span>
                                        <span class="fw-bold"><?= $totalAssignments ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Average Grade</span>
                                        <span class="fw-bold"><?= $averageGrade ?>%</span>
                                    </div>
                                </div>
                                <div class="list-group-item px-0">
                                    <h6 class="mb-2">Top Performing Courses</h6>
                                    <?php
                                    usort($courses, function($a, $b) {
                                        return ($b['average_grade'] ?? 0) - ($a['average_grade'] ?? 0);
                                    });
                                    $topCourses = array_slice($courses, 0, 3);
                                    foreach ($topCourses as $course):
                                    ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-truncate" style="max-width: 70%;">
                                                <?= htmlspecialchars($course['title']) ?>
                                            </small>
                                            <span class="badge bg-success">
                                                <?= number_format($course['average_grade'] ?? 0, 1) ?>%
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Messages / Notifications -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Messages & Notifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="text-muted mb-0">No new messages or notifications.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas <?= $notification['type'] === 'assignment' ? 'fa-tasks' : 'fa-file' ?> 
                                                           <?= $notification['type'] === 'assignment' ? 'text-primary' : 'text-warning' ?>"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <p class="mb-1"><?= htmlspecialchars($notification['title'] ?? 'Untitled Item') ?></p>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($notification['description'] ?? '') ?>
                                                    <?php if (!empty($notification['content_data'])): ?>
                                                        <?php 
                                                            $contentData = is_array($notification['content_data']) 
                                                                ? $notification['content_data'] 
                                                                : json_decode($notification['content_data'], true);
                                                            if (isset($contentData['due_date'])): 
                                                        ?>
                                                            <br>Due: <?= date('M d, Y', strtotime($contentData['due_date'])) ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Calendar</h5>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: <?php 
                    error_log('Calendar Data: ' . print_r($calendar_data ?? [], true));
                    
                    // Combine module items and courses into a single array
                    $allEvents = [];
                    
                    // Add module items
                    if (!empty($calendar_data['module_items'])) {
                        foreach ($calendar_data['module_items'] as $item) {
                            $allEvents[] = [
                                'title' => $item['title'] ?? 'Untitled Item',
                                'start' => $item['start_date'] ?? $item['start'] ?? date('Y-m-d'),
                                'end' => $item['end_date'] ?? $item['end'] ?? date('Y-m-d'),
                                'url' => $item['url'] ?? null,
                                'backgroundColor' => '#0dcaf0' // Default color for module items
                            ];
                        }
                    }
                    
                    // Add courses
                    if (!empty($calendar_data['courses'])) {
                        foreach ($calendar_data['courses'] as $course) {
                            $allEvents[] = [
                                'title' => $course['title'] ?? 'Untitled Course',
                                'start' => $course['start_date'] ?? $course['start'] ?? date('Y-m-d'),
                                'end' => $course['end_date'] ?? $course['end'] ?? date('Y-m-d'),
                                'url' => '/lms-frontend/public/instructor/courses/' . ($course['id'] ?? ''),
                                'backgroundColor' => '#198754' // Green color for courses
                            ];
                        }
                    }
                    
                    error_log('Processed Events: ' . print_r($allEvents, true));
                    echo json_encode($allEvents);
                ?>,
                eventClick: function(info) {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                }
            });
            calendar.render();
        });
    </script>
</body>
</html> 