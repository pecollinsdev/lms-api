<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Item - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-preview {
            max-height: 400px;
            overflow-y: auto;
        }
        .submission-list {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
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

    <div class="container mt-5 pt-5">
        <div class="mb-3">
            <?php 
            $courseId = $item['module']['course']['id'] ?? null;
            if ($courseId): 
            ?>
                <a href="/lms-frontend/public/instructor/courses/<?= htmlspecialchars($courseId) ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Course
                </a>
            <?php else: ?>
                <a href="/lms-frontend/public/instructor/courses" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Courses
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (empty($item)): ?>
            <div class="alert alert-warning" role="alert">
                Module item not found or could not be loaded.
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h3 class="mb-1">
                                        <?= htmlspecialchars($item['title'] ?? 'Untitled Item') ?>
                                        <span class="badge bg-<?= $item['type'] === 'quiz' ? 'info' : ($item['type'] === 'assignment' ? 'warning' : 'secondary') ?> ms-2">
                                            <?= ucfirst($item['type'] ?? 'item') ?>
                                        </span>
                                    </h3>
                                    <div class="text-muted">
                                        <?php if (!empty($item['module'])): ?>
                                            Module: <strong><?= htmlspecialchars($item['module']['title'] ?? '') ?></strong>
                                            <?php if (!empty($item['module']['course'])): ?>
                                                | Course: <strong><?= htmlspecialchars($item['module']['course']['title'] ?? '') ?></strong>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="btn-group">
                                    <?php 
                                    $courseId = $item['module']['course']['id'] ?? null;
                                    $moduleId = $item['module']['id'] ?? null;
                                    $itemId = $item['id'] ?? null;
                                    
                                    if ($courseId && $moduleId && $itemId): 
                                    ?>
                                        <a href="/lms-frontend/public/instructor/courses/<?= htmlspecialchars($courseId) ?>/modules/<?= htmlspecialchars($moduleId) ?>/items/<?= htmlspecialchars($itemId) ?>/edit" class="btn btn-primary">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="deleteItem(<?= htmlspecialchars($itemId) ?>, <?= htmlspecialchars($courseId) ?>, <?= htmlspecialchars($moduleId) ?>)">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            Missing required data for editing this item.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($item['description'])): ?>
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p class="text-muted"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item['due_date'])): ?>
                                <div class="mb-4">
                                    <h5>Due Date</h5>
                                    <p class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('F j, Y g:i A', strtotime($item['due_date'])) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Content by Type -->
                            <?php switch ($item['type'] ?? ''):
                                case 'video': ?>
                                    <div class="mb-4">
                                        <h5>Video Content</h5>
                                        <?php if (!empty($item['video_url'])): ?>
                                            <?php
                                            // Convert YouTube URL to embed format
                                            $videoUrl = $item['video_url'];
                                            if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                                                $videoId = substr($videoUrl, strpos($videoUrl, 'v=') + 2);
                                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                                            } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                                                $videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
                                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                                            }
                                            ?>
                                            <div class="ratio ratio-16x9 mb-3">
                                                <iframe 
                                                    src="<?= htmlspecialchars($videoUrl) ?>?rel=0&modestbranding=1" 
                                                    title="Video content"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen
                                                    loading="lazy"
                                                    referrerpolicy="origin"
                                                    sandbox="allow-same-origin allow-scripts allow-popups allow-forms"
                                                ></iframe>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                No video URL provided.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'document': ?>
                                    <div class="mb-4">
                                        <h5>Document</h5>
                                        <?php if (!empty($item['content']['url'])): ?>
                                            <div class="d-flex gap-2">
                                                <a href="<?= htmlspecialchars($item['content']['url']) ?>" class="btn btn-primary" target="_blank">
                                                    <i class="fas fa-file-download me-1"></i> Download Document
                                                </a>
                                                <a href="<?= htmlspecialchars($item['content']['url']) ?>" class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i> View Document
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                No document URL provided.
                                                <pre class="mt-2"><?= htmlspecialchars(json_encode($item, JSON_PRETTY_PRINT)) ?></pre>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'quiz': ?>
                                    <div class="mb-4">
                                        <h5>Quiz Content</h5>
                                        <?php if (!empty($item['questions'])): ?>
                                            <div class="list-group">
                                                <?php foreach ($item['questions'] as $index => $question): ?>
                                                    <div class="list-group-item">
                                                        <h6 class="mb-2">Question <?= $index + 1 ?></h6>
                                                        <p class="mb-2"><?= htmlspecialchars($question['prompt']) ?></p>
                                                        <?php if (!empty($question['options'])): ?>
                                                            <div class="ms-3">
                                                                <?php foreach ($question['options'] as $option): ?>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="radio" disabled>
                                                                        <label class="form-check-label">
                                                                            <?= htmlspecialchars($option['text']) ?>
                                                                            <?php if ($option['is_correct']): ?>
                                                                                <i class="fas fa-check text-success ms-1"></i>
                                                                            <?php endif; ?>
                                                                        </label>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                No quiz questions available.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'assignment': ?>
                                    <div class="mb-4">
                                        <h5>Assignment Details</h5>
                                        <?php if (!empty($item['assignment_instructions'])): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6>Instructions:</h6>
                                                    <p><?= nl2br(htmlspecialchars($item['assignment_instructions'])) ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="row mb-3">
                                            <?php if (!empty($item['max_score'])): ?>
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6>Maximum Score:</h6>
                                                            <p class="mb-0"><?= htmlspecialchars($item['max_score']) ?> points</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($item['submission_type'])): ?>
                                                <div class="col-md-6">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6>Submission Type:</h6>
                                                            <p class="mb-0"><?= ucfirst(htmlspecialchars($item['submission_type'])) ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php break;
                            endswitch; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Submissions (for assignments and quizzes) -->
                    <?php if (in_array($item['type'], ['assignment', 'quiz'])): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Submissions</h5>
                            </div>
                            <div class="card-body submission-list">
                                <?php if (!empty($item['submissions'])): ?>
                                    <div class="list-group">
                                        <?php foreach ($item['submissions'] as $submission): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?= htmlspecialchars($submission['student']['name']) ?></h6>
                                                        <small class="text-muted">
                                                            Submitted: <?= date('M d, Y H:i', strtotime($submission['submitted_at'])) ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-<?= $submission['status'] === 'graded' ? 'success' : 'warning' ?>">
                                                            <?= ucfirst($submission['status']) ?>
                                                        </span>
                                                        <?php if ($submission['status'] === 'graded'): ?>
                                                            <div class="mt-1">
                                                                Score: <?= $submission['score'] ?>/<?= $item['max_score'] ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0">No submissions yet.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Progress Stats -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Progress Statistics</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($item['type'] === 'assignment' || $item['type'] === 'quiz'): ?>
                                <div class="mb-3">
                                    <h6>Completion Rate</h6>
                                    <div class="progress" style="height: 5px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= $item['completion_rate'] ?? 0 ?>%;" 
                                             aria-valuenow="<?= $item['completion_rate'] ?? 0 ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= $item['completion_rate'] ?? 0 ?>% of students have completed this item
                                    </small>
                                </div>

                                <?php if (!empty($item['average_score'])): ?>
                                    <div class="mb-3">
                                        <h6>Average Score</h6>
                                        <h3 class="mb-0"><?= number_format($item['average_score'], 1) ?>/<?= $item['max_score'] ?></h3>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="mb-3">
                                    <h6>View Count</h6>
                                    <h3 class="mb-0"><?= $item['view_count'] ?? 0 ?></h3>
                                    <small class="text-muted">Total views</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteItem(itemId, courseId, moduleId) {
            if (confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                window.location.href = `/lms-frontend/public/instructor/courses/${courseId}/modules/${moduleId}/items/${itemId}/delete`;
            }
        }
    </script>
</body>
</html> 