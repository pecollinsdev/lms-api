<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Content - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .list-group-item > a:hover, 
    .list-group-item > a:focus {
        background-color: #f0f6ff;
        transition: background 0.2s;
        text-decoration: none;
        color: #212529;
        border-radius: 0.375rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .progress {
        height: 8px;
        border-radius: 4px;
    }
    .badge {
        font-weight: 500;
    }
    .module-card {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    .module-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    </style>
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
        <div class="mb-3">
            <a href="/lms-frontend/public/student/courses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Courses
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (empty($course) || empty($course['id'])): ?>
            <div class="alert alert-warning" role="alert">
                Course not found or could not be loaded.
            </div>
        <?php else: ?>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($course['title'] ?? 'Untitled Course') ?></h2>
                        <p class="text-muted mb-0">
                            <?= nl2br(htmlspecialchars($course['description'] ?? 'No description provided.')) ?>
                        </p>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-layer-group me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Modules</small>
                                <span class="fw-bold"><?= count($modules) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-list me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Total Items</small>
                                <span class="fw-bold"><?= array_sum(array_map(function($m) { return count($m['module_items'] ?? []); }, $modules)) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Grade</small>
                                <span class="fw-bold"><?= htmlspecialchars($course['grade'] ?? 'N/A') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            <div>
                                <small class="text-muted d-block">Progress</small>
                                <span class="fw-bold"><?= $course['progress'] ?? 0 ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: <?= $course['progress'] ?? 0 ?>%"
                             aria-valuenow="<?= $course['progress'] ?? 0 ?>"
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <?php if (!empty($course['start_date']) && !empty($course['end_date'])): ?>
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i>
                    <?= date('M d, Y', strtotime($course['start_date'])) ?> - <?= date('M d, Y', strtotime($course['end_date'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($modules)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Course Modules</h5>
            </div>
            <div class="card-body">
                <?php foreach ($modules as $module): ?>
                    <div class="module-card mb-4 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><?= htmlspecialchars($module['title']) ?></h5>
                            <?php if (!empty($module['start_date']) && !empty($module['end_date'])): ?>
                            <span class="text-muted small">
                                <?= date('M d, Y', strtotime($module['start_date'])) ?> - <?= date('M d, Y', strtotime($module['end_date'])) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($module['description'])): ?>
                        <div class="mb-3 text-muted"><?= htmlspecialchars($module['description']) ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($module['module_items']) && is_array($module['module_items'])): ?>
                            <ul class="list-group">
                                <?php foreach ($module['module_items'] as $item): ?>
                                    <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center p-0">
                                        <a href="/lms-frontend/public/student/courses/<?= htmlspecialchars($course['id']) ?>/items/<?= htmlspecialchars($item['id']) ?>" 
                                           class="d-flex flex-grow-1 flex-column flex-md-row justify-content-between align-items-md-center text-decoration-none text-dark p-3">
                                            <div>
                                                <strong><?= htmlspecialchars($item['title'] ?? 'Untitled Item') ?></strong>
                                                <?php if (!empty($item['type'])): ?>
                                                    <span class="badge bg-<?= $item['type'] === 'quiz' ? 'info' : ($item['type'] === 'assignment' ? 'warning' : 'secondary') ?> ms-2">
                                                        <?= ucfirst($item['type']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($item['description'])): ?>
                                                <div class="small text-muted mt-1"><?= htmlspecialchars($item['description']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                                                <?php if (!empty($item['submission'])): ?>
                                                    <?php if ($item['submission']['status'] === 'graded'): ?>
                                                        <span class="badge bg-success">Graded</span>
                                                        <span class="text-muted small">
                                                            <?php if ($item['type'] === 'quiz'): ?>
                                                                Score: <?= $item['submission']['score'] ?? 'N/A' ?> / <?= $item['max_score'] ?? 'N/A' ?>
                                                                (<?= isset($item['submission']['grade']) ? number_format($item['submission']['grade'], 1) . '%' : 'N/A' ?>)
                                                            <?php else: ?>
                                                                Grade: <?= isset($item['submission']['grade']) ? number_format($item['submission']['grade'], 1) . '%' : 'N/A' ?>
                                                                <?php if (!empty($item['submission']['score'])): ?>
                                                                    (<?= $item['submission']['score'] ?? 'N/A' ?> / <?= $item['max_score'] ?? 'N/A' ?>)
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php elseif ($item['submission']['status'] === 'submitted'): ?>
                                                        <span class="badge bg-info">Submitted</span>
                                                        <span class="text-muted small">
                                                            <?= date('M d, Y', strtotime($item['submission']['submitted_at'] ?? 'now')) ?>
                                                        </span>
                                                    <?php elseif ($item['submission']['status'] === 'pending'): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                        <span class="text-muted small">
                                                            <?= date('M d, Y', strtotime($item['submission']['submitted_at'] ?? 'now')) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php elseif (!empty($item['progress']) && $item['progress']['status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                    <span class="text-muted small">
                                                        <?= date('M d, Y', strtotime($item['progress']['completed_at'] ?? 'now')) ?>
                                                    </span>
                                                <?php elseif (!empty($item['due_date'])): ?>
                                                    <?php
                                                    $dueDate = strtotime($item['due_date']);
                                                    $now = time();
                                                    $daysLeft = ceil(($dueDate - $now) / (60 * 60 * 24));
                                                    $badgeClass = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'info');
                                                    ?>
                                                    <span class="badge bg-<?= $badgeClass ?> text-white">
                                                        Due: <?= date('M d, Y', $dueDate) ?>
                                                        <?php if ($daysLeft > 0): ?>
                                                            (<?= $daysLeft ?> days left)
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (in_array($item['type'] ?? '', ['video', 'document']) && empty($item['submission']) && ($item['progress']['status'] ?? '') !== 'completed'): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success mark-complete" 
                                                            data-item-id="<?= $item['id'] ?>"
                                                            data-course-id="<?= $course['id'] ?>">
                                                        <i class="fas fa-check me-1"></i> Mark as Complete
                                                    </button>
                                                <?php endif; ?>
                                                <i class="fas fa-chevron-right ms-2"></i>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-muted small">No items in this module.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Add this before the closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle mark as complete buttons
        document.querySelectorAll('.mark-complete').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const itemId = this.dataset.itemId;
                const courseId = this.dataset.courseId;
                
                try {
                    const response = await fetch(`/lms-frontend/public/student/courses/${courseId}/items/${itemId}/complete`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update UI to show completion
                        const listItem = this.closest('.list-group-item');
                        const statusContainer = this.parentElement;
                        
                        // Remove the mark complete button
                        this.remove();
                        
                        // Add completion badge
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-success';
                        badge.textContent = 'Completed';
                        statusContainer.insertBefore(badge, statusContainer.firstChild);
                        
                        // Add completion date
                        const dateSpan = document.createElement('span');
                        dateSpan.className = 'text-muted small ms-2';
                        dateSpan.textContent = 'Just now';
                        statusContainer.insertBefore(dateSpan, statusContainer.firstChild);
                        
                        // Refresh the page after a short delay to update progress
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alert(data.message || 'Failed to mark item as complete. Please try again.');
                    }
                } catch (error) {
                    console.error('Error marking item as complete:', error);
                    alert('An error occurred while marking the item as complete. Please try again.');
                }
            });
        });
    });
    </script>
</body>
</html>
