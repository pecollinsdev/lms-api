<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - LMS</title>
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
            <a class="navbar-brand fw-bold" href="/lms-frontend/public/student/dashboard">LMS Student</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/lms-frontend/public/student/courses">My Courses</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($profile['name'] ?? 'Student') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/lms-frontend/public/student/profile">Profile</a></li>
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
                                <h4 class="mb-1">Welcome back, <?= htmlspecialchars($profile['name'] ?? 'Student') ?>!</h4>
                                <p class="text-muted mb-0"><?= date('l, F j, Y') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Current GPA</h5>
                        <h2 class="mb-0"><?= number_format(min(max($gpa ?? 0, 0), 4.0), 2) ?></h2>
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
                        <a href="/lms-frontend/public/student/courses" class="btn btn-primary btn-sm">
                            <i class="fas fa-book me-1"></i> View All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <p class="text-muted mb-0">You are not enrolled in any courses yet.</p>
                        <?php else: ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <?php foreach ($courses as $course): ?>
                                    <div class="col">
                                        <div class="card h-100">
                                            <?php if (!empty($course['thumbnail'])): ?>
                                                <img src="<?= htmlspecialchars($course['thumbnail']) ?>" 
                                                     class="card-img-top" 
                                                     alt="<?= htmlspecialchars($course['title']) ?>"
                                                     style="height: 120px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                                                <p class="card-text text-muted">
                                                    <small>Instructor: <?= htmlspecialchars($course['instructor'] ?? 'Not assigned') ?></small>
                                                </p>
                                                <div class="progress mb-3" style="height: 5px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $course['completion_percentage'] ?? 0 ?>%"
                                                         aria-valuenow="<?= $course['completion_percentage'] ?? 0 ?>"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-muted">
                                                        <small>Progress: <?= $course['completion_percentage'] ?? 0 ?>%</small><br>
                                                        <small>Grade: <?= htmlspecialchars($course['current_grade'] ?? 'N/A') ?></small>
                                                    </div>
                                                    <div class="btn-group">
                                                        <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/content" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-folder"></i>
                                                        </a>
                                                        <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/grades" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </a>
                                                        <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/announcements" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-bullhorn"></i>
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

                <!-- Upcoming Deadlines -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Upcoming Deadlines</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($upcomingDeadlines)): ?>
                            <p class="text-muted mb-0">No upcoming deadlines.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($upcomingDeadlines as $deadline): ?>
                                    <?php
                                    $dueDate = strtotime($deadline['due_date']);
                                    $isToday = date('Y-m-d') === date('Y-m-d', $dueDate);
                                    $isThisWeek = date('Y-m-d', strtotime('+7 days')) >= date('Y-m-d', $dueDate);
                                    $typeIcon = $deadline['type'] === 'assignment' ? 'fa-tasks' : 'fa-question-circle';
                                    $typeClass = $deadline['type'] === 'assignment' ? 'text-primary' : 'text-warning';
                                    ?>
                                    <div class="list-group-item <?= $isToday ? 'list-group-item-warning' : ($isThisWeek ? 'list-group-item-info' : '') ?>">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <i class="fas <?= $typeIcon ?> <?= $typeClass ?> me-2"></i>
                                                    <?= htmlspecialchars($deadline['title']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    Course: <?= htmlspecialchars($deadline['course_title'] ?? 'Unknown Course') ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge <?= $isToday ? 'bg-warning' : ($isThisWeek ? 'bg-info' : 'bg-secondary') ?>">
                                                    <?= date('M d, Y', strtotime($deadline['due_date'])) ?>
                                                </span>
                                                <a href="/lms-frontend/public/student/module-items/<?= $deadline['id'] ?>/submit" 
                                                   class="btn btn-sm btn-primary ms-2">
                                                    Submit
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
                <!-- Recent Announcements -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($announcements)): ?>
                            <p class="text-muted mb-0">No recent announcements.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($announcement['title'] ?? '') ?></h6>
                                            <small class="text-muted">
                                                <?= date('M d', strtotime($announcement['created_at'] ?? '')) ?>
                                            </small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($announcement['content'] ?? '') ?></p>
                                        <small class="text-muted">
                                            Course: <?= htmlspecialchars($announcement['course']['title'] ?? 'Unknown Course') ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Submissions</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentSubmissions)): ?>
                            <p class="text-muted mb-0">No recent submissions.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recentSubmissions as $submission): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($submission['module_item_title'] ?? '') ?></h6>
                                                <small class="text-muted">
                                                    Course: <?= htmlspecialchars($submission['course_title'] ?? 'Unknown Course') ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= ($submission['status'] ?? '') === 'graded' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($submission['status'] ?? 'pending') ?>
                                                </span>
                                                <?php if (($submission['status'] ?? '') === 'graded'): ?>
                                                    <span class="ms-2">
                                                        Grade: <?= $submission['grade_display'] ?? 'N/A' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Course Progress -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Course Progress</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($progress)): ?>
                            <p class="text-muted mb-0">No progress data available.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($progress as $item): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($item['module_item_title'] ?? '') ?></h6>
                                                <small class="text-muted">
                                                    Course: <?= htmlspecialchars($item['course_title'] ?? 'Unknown Course') ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?= ($item['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($item['status'] ?? 'pending') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
</body>
</html> 