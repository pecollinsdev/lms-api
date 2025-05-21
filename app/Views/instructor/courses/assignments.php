<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Assignments - LMS</title>
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
            <a href="/lms-frontend/public/instructor/courses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Courses
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <h2 class="mb-0">Course Assignments</h2>
        </div>

        <!-- Assignments Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Submittable Items
                    </h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Module</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th>Visibility</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($submittableItems['data']) && is_array($submittableItems['data'])): ?>
                                <?php foreach ($submittableItems['data'] as $item): ?>
                                    <?php if (is_array($item)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?= htmlspecialchars($item['title'] ?? 'Untitled') ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($item['description'] ?? 'No description') ?></small>
                                            </td>
                                            <td>
                                                <?php $type = $item['type'] ?? 'unknown'; ?>
                                                <span class="badge bg-<?= $type === 'quiz' ? 'primary' : 'success' ?>">
                                                    <?= ucfirst(htmlspecialchars($type)) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (isset($item['module']) && is_array($item['module']) && isset($item['module']['id'])): ?>
                                                    <?= htmlspecialchars($item['module']['title'] ?? 'Untitled Module') ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No module</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if (!empty($item['due_date'])) {
                                                    echo date('M d, Y', strtotime($item['due_date']));
                                                } else {
                                                    echo '<span class="text-muted">No due date</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?= $item['max_score'] ?? 'N/A' ?></td>
                                            <td>
                                                <?php $isPublic = $item['is_public'] ?? false; ?>
                                                <span class="badge bg-<?= $isPublic ? 'success' : 'secondary' ?>">
                                                    <?= $isPublic ? 'Public' : 'Private' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $item['id'] ?? '' ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> View
                                                    </a>
                                                    <?php if (($item['type'] ?? '') === 'quiz'): ?>
                                                        <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $item['id'] ?? '' ?>/submissions" 
                                                           class="btn btn-sm btn-outline-info">
                                                            <i class="fas fa-list me-1"></i> Submissions
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-tasks fa-2x text-muted mb-3"></i>
                                        <h5 class="text-muted">No submittable items found</h5>
                                        <p class="text-muted mb-0">This course doesn't have any assignments or quizzes yet.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($submittableItems['meta']) && is_array($submittableItems['meta']) && 
                          isset($submittableItems['meta']['total']) && 
                          isset($submittableItems['meta']['per_page']) && 
                          $submittableItems['meta']['total'] > $submittableItems['meta']['per_page']): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                            $totalPages = ceil($submittableItems['meta']['total'] / $submittableItems['meta']['per_page']);
                            $currentPage = $submittableItems['meta']['current_page'] ?? 1;
                            
                            // Previous page
                            if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
