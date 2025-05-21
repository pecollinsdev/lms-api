<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - LMS</title>
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
            <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/assignments" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Assignments
            </a>
        </div>

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="mb-1"><?= htmlspecialchars($moduleItem['title'] ?? 'Submissions') ?></h2>
                <p class="text-muted mb-0">
                    <?= htmlspecialchars($moduleItem['description'] ?? '') ?>
                </p>
            </div>
            <div class="text-end">
                <span class="badge bg-<?= ($moduleItem['type'] ?? '') === 'quiz' ? 'primary' : 'success' ?> mb-2">
                    <?= ucfirst(htmlspecialchars($moduleItem['type'] ?? 'unknown')) ?>
                </span>
                <div class="text-muted small">
                    Due: <?= !empty($moduleItem['due_date']) ? date('M d, Y', strtotime($moduleItem['due_date'])) : 'No due date' ?>
                </div>
            </div>
        </div>

        <!-- Submissions Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Student Submissions
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search students...">
                        </div>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="submitted">Submitted</option>
                            <option value="graded">Graded</option>
                            <option value="late">Late</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($submissions['data']) && is_array($submissions['data'])): ?>
                                <?php foreach ($submissions['data'] as $submission): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <img src="<?= isset($submission['user']['profile_picture']) && !empty($submission['user']['profile_picture']) ? $submission['user']['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($submission['user']['name'] ?? 'Student') ?>" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40" 
                                                         alt="Student Avatar">
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <div class="fw-bold"><?= htmlspecialchars($submission['user']['name'] ?? 'Unknown Student') ?></div>
                                                    <div class="text-muted small"><?= htmlspecialchars($submission['user']['email'] ?? '') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($submission['submitted_at'])): ?>
                                                <div><?= date('M d, Y', strtotime($submission['submitted_at'])) ?></div>
                                                <div class="text-muted small"><?= date('h:i A', strtotime($submission['submitted_at'])) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">Not submitted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $submission['status'] ?? '';
                                            $statusClass = match($status) {
                                                'graded' => 'success',
                                                'late' => 'warning',
                                                'submitted' => 'info',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= ucfirst(htmlspecialchars($status)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($status === 'graded'): ?>
                                                <?php
                                                $score = $submission['score'] ?? $submission['grade'] ?? 0;
                                                $maxScore = $submission['max_score'] ?? $moduleItem['max_score'] ?? 0;
                                                ?>
                                                <div class="fw-bold"><?= $score ?>/<?= $maxScore ?></div>
                                                <div class="text-muted small">
                                                    <?= $maxScore > 0 ? round(($score / $maxScore) * 100) : 0 ?>%
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($submission['file_path'])): ?>
                                                <a href="<?= htmlspecialchars($submission['file_path']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="fas fa-file me-1"></i> View File
                                                </a>
                                            <?php elseif (!empty($submission['content'])): ?>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                    <?= htmlspecialchars($submission['content']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No content</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $moduleItem['id'] ?>/submissions/<?= $submission['id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                                <?php if ($status !== 'graded'): ?>
                                                    <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $moduleItem['id'] ?>/submissions/<?= $submission['id'] ?>/grade" 
                                                       class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-check me-1"></i> Grade
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                        <h5 class="text-muted">No submissions yet</h5>
                                        <p class="text-muted mb-0">Students haven't submitted their work for this item.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($submissions['meta']) && is_array($submissions['meta']) && 
                          isset($submissions['meta']['total']) && 
                          isset($submissions['meta']['per_page']) && 
                          $submissions['meta']['total'] > $submissions['meta']['per_page']): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php
                            $totalPages = ceil($submissions['meta']['total'] / $submissions['meta']['per_page']);
                            $currentPage = $submissions['meta']['current_page'] ?? 1;
                            
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

    <!-- Debug Information -->
    <?php if (isset($debug)): ?>
        <div class="container mt-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Debug Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Module Item Data:</h6>
                            <pre class="bg-light p-3 rounded"><?= htmlspecialchars(print_r($debug['moduleItem'], true)) ?></pre>
                        </div>
                        <div class="col-md-4">
                            <h6>Processed Submissions:</h6>
                            <pre class="bg-light p-3 rounded"><?= htmlspecialchars(print_r($debug['submissions'], true)) ?></pre>
                        </div>
                        <div class="col-md-4">
                            <h6>Raw API Response:</h6>
                            <pre class="bg-light p-3 rounded"><?= htmlspecialchars(print_r($debug['rawResponse'], true)) ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Search and Filter Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const tableRows = document.querySelectorAll('tbody tr');

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    const studentName = row.querySelector('td:first-child').textContent.toLowerCase();
                    const status = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                    const matchesSearch = studentName.includes(searchTerm);
                    const matchesStatus = !statusValue || status.includes(statusValue);

                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterTable);
            statusFilter.addEventListener('change', filterTable);
        });
    </script>
</body>
</html> 