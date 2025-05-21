<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                        <a class="nav-link active" href="/lms-frontend/public/student/courses">My Courses</a>
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

        <!-- Back Button -->
        <div class="mb-2">
            <a href="/lms-frontend/public/student/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <h2 class="mb-0">My Courses</h2>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary" id="gridView">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="btn btn-outline-primary" id="listView">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No courses found</h4>
                <p class="text-muted">You are not enrolled in any courses yet</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="coursesGrid">
                <?php foreach ($courses as $course): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <?php if (!empty($course['cover_image'])): ?>
                                <img src="<?= htmlspecialchars($course['cover_image']) ?>" class="card-img-top" alt="Course Cover" style="object-fit:cover;max-height:180px;">
                            <?php else: ?>
                                <img src="https://placehold.co/1200x675" class="card-img-top" alt="Course Cover" style="object-fit:cover;max-height:180px;">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($course['title'] ?? '') ?></h5>
                                        <p class="text-muted small mb-0">Instructor: <?= htmlspecialchars($course['instructor']['name'] ?? 'Not assigned') ?></p>
                                    </div>
                                </div>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars(substr($course['description'] ?? 'No description provided.', 0, 100)) ?>
                                    <?= strlen($course['description'] ?? '') > 100 ? '...' : '' ?>
                                </p>
                                <div class="row text-center mb-2 g-1">
                                    <div class="col-6">
                                        <span class="text-muted small">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php if (!empty($course['start_date']) && !empty($course['end_date'])): ?>
                                                <?= date('M d, Y', strtotime($course['start_date'])) ?> - <?= date('M d, Y', strtotime($course['end_date'])) ?>
                                            <?php else: ?>
                                                Dates not set
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted small">
                                            <i class="fas fa-graduation-cap me-1"></i>
                                            Grade: <?= !empty($course['grade']) ? htmlspecialchars($course['grade']) : 'N/A' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold small mb-1">Course Progress</label>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $course['completion_percentage'] ?? 0 ?>%"
                                             aria-valuenow="<?= $course['completion_percentage'] ?? 0 ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">Progress: <?= $course['completion_percentage'] ?? 0 ?>%</small>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="btn-group w-100 d-flex" role="group">
                                    <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/content" class="btn btn-outline-primary flex-fill">
                                        <i class="fas fa-folder me-1"></i> Content
                                    </a>
                                    <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/grades" class="btn btn-outline-success flex-fill">
                                        <i class="fas fa-graduation-cap me-1"></i> Grades
                                    </a>
                                    <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/announcements" class="btn btn-outline-info flex-fill">
                                        <i class="fas fa-bullhorn me-1"></i> Announcements
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Courses List (Hidden by default) -->
            <div class="d-none" id="coursesList">
                <div class="list-group">
                    <?php foreach ($courses as $course): ?>
                        <div class="list-group-item p-0 mb-2 shadow-sm rounded">
                            <div class="d-flex flex-column">
                                <!-- Course Image Banner -->
                                <div style="height: 120px;">
                                    <?php if (!empty($course['cover_image'])): ?>
                                        <img src="<?= htmlspecialchars($course['cover_image']) ?>" class="w-100 h-100 rounded-top" alt="Course Cover" style="object-fit: cover;">
                                    <?php else: ?>
                                        <img src="https://placehold.co/1200x400" class="w-100 h-100 rounded-top" alt="Course Cover" style="object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <!-- Course Details -->
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1"><?= htmlspecialchars($course['title'] ?? '') ?></h5>
                                            <p class="text-muted small mb-2">
                                                Instructor: <?= htmlspecialchars($course['instructor']['name'] ?? 'Not assigned') ?>
                                            </p>
                                            <p class="text-muted small mb-2">
                                                <?= htmlspecialchars(substr($course['description'] ?? 'No description provided.', 0, 150)) ?>
                                                <?= strlen($course['description'] ?? '') > 150 ? '...' : '' ?>
                                            </p>
                                            <div class="row text-center mb-2 g-2">
                                                <div class="col-6">
                                                    <span class="text-muted small">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php if (!empty($course['start_date']) && !empty($course['end_date'])): ?>
                                                            <?= date('M d, Y', strtotime($course['start_date'])) ?> - <?= date('M d, Y', strtotime($course['end_date'])) ?>
                                                        <?php else: ?>
                                                            Dates not set
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <div class="col-6">
                                                    <span class="text-muted small">
                                                        <i class="fas fa-graduation-cap me-1"></i>
                                                        Grade: <?= !empty($course['grade']) ? htmlspecialchars($course['grade']) : 'N/A' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="progress mb-1" style="height: 4px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?= $course['completion_percentage'] ?? 0 ?>%"
                                                     aria-valuenow="<?= $course['completion_percentage'] ?? 0 ?>"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Progress: <?= $course['completion_percentage'] ?? 0 ?>%
                                            </small>
                                        </div>
                                        <div class="btn-group ms-2">
                                            <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/content" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-folder"></i>
                                            </a>
                                            <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/grades" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-graduation-cap"></i>
                                            </a>
                                            <a href="/lms-frontend/public/student/courses/<?= $course['id'] ?>/announcements" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-bullhorn"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const coursesGrid = document.getElementById('coursesGrid');
            const coursesList = document.getElementById('coursesList');

            gridView.addEventListener('click', function() {
                coursesGrid.classList.remove('d-none');
                coursesList.classList.add('d-none');
                gridView.classList.add('active');
                listView.classList.remove('active');
            });

            listView.addEventListener('click', function() {
                coursesGrid.classList.add('d-none');
                coursesList.classList.remove('d-none');
                gridView.classList.remove('active');
                listView.classList.add('active');
            });
        });
    </script>
</body>
</html> 