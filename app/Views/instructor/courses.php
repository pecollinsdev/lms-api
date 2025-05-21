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
            <a class="navbar-brand fw-bold" href="/lms-frontend/public/instructor/dashboard">LMS Instructor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="/lms-frontend/public/instructor/courses">Courses</a>
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

        <!-- Back Button -->
        <div class="mb-2">
            <a href="/lms-frontend/public/instructor/dashboard" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <h2 class="mb-0">My Courses</h2>
            <a href="/lms-frontend/public/instructor/courses/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create New Course
            </a>
        </div>

        <!-- Courses Grid -->
        <?php if (empty($courses['data'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No courses found</h4>
                <p class="text-muted">Get started by creating your first course</p>
                <a href="/lms-frontend/public/instructor/courses/create" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Course
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach (($courses['data'] ?? []) as $course): ?>
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
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($course['title']) ?></h5>
                                    </div>
                                    <?php if (!empty($course['is_published'])): ?>
                                        <span class="badge bg-success align-self-start">Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark align-self-start">Draft</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mb-2">
                                    <?= htmlspecialchars(substr($course['description'] ?? 'No description provided.', 0, 100)) ?>
                                    <?= strlen($course['description'] ?? '') > 100 ? '...' : '' ?>
                                </p>
                                <div class="row text-center mb-2 g-1">
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small"><i class="fas fa-users me-1"></i> <?= $course['statistics']['student_count'] ?? 0 ?> Students</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small"><i class="fas fa-tasks me-1"></i> <?= $course['statistics']['submission_count'] ?? 0 ?> Submissions</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small"><i class="fas fa-layer-group me-1"></i> <?= $course['statistics']['module_count'] ?? 0 ?> Modules</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small"><i class="fas fa-list me-1"></i> <?= $course['statistics']['total_items'] ?? 0 ?> Items</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small"><i class="fas fa-calendar me-1"></i>
                                            <?php if (!empty($course['start_date']) && !empty($course['end_date'])): ?>
                                                <?= date('M d, Y', strtotime($course['start_date'])) ?> - <?= date('M d, Y', strtotime($course['end_date'])) ?>
                                            <?php else: ?>
                                                Dates not set
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted small">
                                            <i class="fas fa-user me-1"></i> <?= htmlspecialchars($course['instructor']['name'] ?? 'Unknown') ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (isset($course['progress'])): ?>
                                <div class="mb-2">
                                    <label class="form-label fw-bold small mb-1">Course Progress</label>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $course['progress'] ?? 0 ?>%"
                                             aria-valuenow="<?= $course['progress'] ?? 0 ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">Progress: <?= $course['progress'] ?? 0 ?>%</small>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer bg-white border-top-0">
                                <div class="btn-group w-100 d-flex" role="group">
                                    <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>" class="btn btn-outline-primary flex-fill">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/assignments" class="btn btn-outline-success flex-fill">
                                        <i class="fas fa-tasks me-1"></i> Assignments
                                    </a>
                                    <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/students" class="btn btn-outline-info flex-fill">
                                        <i class="fas fa-user-graduate me-1"></i> Students
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle flex-shrink-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/edit">
                                                <i class="fas fa-edit me-1"></i> Edit Course
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/delete" onclick="return confirm('Are you sure you want to delete this course? This will also delete all modules and items within it.')">
                                                <i class="fas fa-trash me-1"></i> Delete Course
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Controls -->
            <?php if (!empty($courses['meta']['total']) && !empty($courses['meta']['per_page']) && ceil($courses['meta']['total'] / $courses['meta']['per_page']) > 1): ?>
                <div class="mt-4">
                    <nav aria-label="Course pagination">
                        <ul class="pagination justify-content-center">
                            <?php
                            $currentPage = $courses['meta']['current_page'] ?? 1;
                            $lastPage = ceil($courses['meta']['total'] / $courses['meta']['per_page']);
                            
                            // Previous page link
                            if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif;

                            // Page numbers
                            for ($page = 1; $page <= $lastPage; $page++):
                                // Show current page, first page, last page, and pages around current page
                                if ($page == 1 || $page == $lastPage || ($page >= $currentPage - 2 && $page <= $currentPage + 2)): ?>
                                    <li class="page-item <?= $page == $currentPage ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page ?>"><?= $page ?></a>
                                    </li>
                                <?php elseif ($page == $currentPage - 3 || $page == $currentPage + 3): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif;
                            endfor;

                            // Next page link
                            if ($currentPage < $lastPage): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 