<?php
/**
 * View for displaying a specific course item (assignment, quiz, etc.)
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title'] ?? 'Course Item') ?> - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-preview {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/lms-frontend/public/student/dashboard">LMS Student</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/lms-frontend/public/student/courses">Courses</a>
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

    <div class="container mt-5 pt-5">
        <div class="mb-3">
            <a href="/lms-frontend/public/student/courses/<?= htmlspecialchars($course['id'] ?? '') ?>/content" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Course
            </a>
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
                <div class="col-12">
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
                                        Module: <strong><?= htmlspecialchars($item['module']['title'] ?? '') ?></strong> |
                                        Course: <strong><?= htmlspecialchars($course['title'] ?? '') ?></strong>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($item['description'])): ?>
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p class="text-muted"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($item['submission'])): ?>
                                <div class="mb-4">
                                    <h5>Submission Status</h5>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <?php if ($item['submission']['status'] === 'graded'): ?>
                                            <span class="badge bg-success">Graded</span>
                                            <span class="text-muted">
                                                Score: <?= $item['submission']['score'] ?? 'N/A' ?> / <?= $item['max_score'] ?? 'N/A' ?>
                                                (<?= $item['submission']['grade'] ?? 'N/A' ?>%)
                                            </span>
                                        <?php elseif ($item['submission']['status'] === 'submitted'): ?>
                                            <span class="badge bg-info">Submitted</span>
                                            <span class="text-muted">
                                                Submitted on: <?= date('F j, Y g:i A', strtotime($item['submission']['submitted_at'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($item['submission']['status'] === 'graded' && !empty($item['submission']['submission_answers'])): ?>
                                        <div class="card mt-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Your Answers</h6>
                                            </div>
                                            <div class="card-body">
                                                <?php foreach ($item['submission']['submission_answers'] as $answer): ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-2"><?= htmlspecialchars($answer['question']['text']) ?></h6>
                                                        <div class="ms-3">
                                                            <p class="mb-1">
                                                                Your answer: <?= htmlspecialchars($answer['answer_text']) ?>
                                                                <?php if ($answer['question']['options'][0]['is_correct']): ?>
                                                                    <i class="fas fa-check text-success ms-1"></i>
                                                                <?php else: ?>
                                                                    <i class="fas fa-times text-danger ms-1"></i>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif (!empty($item['due_date'])): ?>
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
                                        <pre class="bg-light p-2 small border">DEBUG: video_url = <?= var_export($item['video_url'] ?? null, true) ?>\ncontent = <?= var_export($item['content'] ?? null, true) ?></pre>
                                        <?php
                                        $videoUrl = $item['video_url']['url'] ?? '';
                                        if ($videoUrl) {
                                            // Convert YouTube URL to embed format if needed
                                            if (strpos($videoUrl, 'youtube.com/watch?v=') !== false) {
                                                $videoId = substr($videoUrl, strpos($videoUrl, 'v=') + 2);
                                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                                            } elseif (strpos($videoUrl, 'youtu.be/') !== false) {
                                                $videoId = substr($videoUrl, strrpos($videoUrl, '/') + 1);
                                                $videoUrl = 'https://www.youtube.com/embed/' . $videoId;
                                            }
                                        }
                                        ?>
                                        <?php if (!empty($videoUrl)): ?>
                                            <div class="ratio ratio-16x9 mb-3">
                                                <iframe src="<?= htmlspecialchars($videoUrl) ?>?rel=0&modestbranding=1"
                                                        title="Video content"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen
                                                        loading="lazy"
                                                        referrerpolicy="origin"
                                                        sandbox="allow-same-origin allow-scripts allow-popups allow-forms">
                                                </iframe>
                                            </div>
                                            <ul class="list-unstyled small mb-3">
                                                <li><strong>Type:</strong> <?= htmlspecialchars($item['video_url']['type'] ?? 'Unknown') ?></li>
                                                <?php if (!empty($item['video_url']['size'])): ?>
                                                    <li><strong>Size:</strong> <?= htmlspecialchars($item['video_url']['size']) ?></li>
                                                <?php endif; ?>
                                            </ul>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-success" onclick="markComplete()">
                                                    <i class="fas fa-check me-1"></i> Mark as Complete
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">No video URL provided for this item.</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'document': ?>
                                    <div class="mb-4">
                                        <h5>Document</h5>
                                        <pre class="bg-light p-2 small border">DEBUG: document_url = <?= var_export($item['document_url'] ?? null, true) ?>\ncontent = <?= var_export($item['content'] ?? null, true) ?></pre>
                                        <?php if (!empty($item['document_url']['url'])): ?>
                                            <div class="d-flex gap-2 mb-3">
                                                <a href="<?= htmlspecialchars($item['document_url']['url']) ?>" class="btn btn-primary" target="_blank">
                                                    <i class="fas fa-file-download me-1"></i> Download Document
                                                </a>
                                                <a href="<?= htmlspecialchars($item['document_url']['url']) ?>" class="btn btn-outline-primary" target="_blank">
                                                    <i class="fas fa-eye me-1"></i> View Document
                                                </a>
                                            </div>
                                            <ul class="list-unstyled small mb-3">
                                                <li><strong>Type:</strong> <?= htmlspecialchars($item['document_url']['type'] ?? 'Unknown') ?></li>
                                                <?php if (!empty($item['document_url']['size'])): ?>
                                                    <li><strong>Size:</strong> <?= htmlspecialchars($item['document_url']['size']) ?></li>
                                                <?php endif; ?>
                                                <li><strong>Download Allowed:</strong> <?= !empty($item['document_url']['allow_download']) ? 'Yes' : 'No' ?></li>
                                            </ul>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-success" onclick="markComplete()">
                                                    <i class="fas fa-check me-1"></i> Mark as Complete
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">No document URL provided.</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'quiz': ?>
                                    <div class="mb-4">
                                        <h5>Quiz Content</h5>
                                        <?php if (!empty($item['questions'])): ?>
                                            <form id="quizForm" action="/lms-frontend/public/student/courses/<?= $course['id'] ?>/items/<?= $item['id'] ?>/submissions" method="POST">
                                                <div class="list-group">
                                                    <?php foreach ($item['questions'] as $index => $question): ?>
                                                        <div class="list-group-item">
                                                            <h6 class="mb-2">Question <?= $index + 1 ?></h6>
                                                            <p class="mb-2"><?= htmlspecialchars($question['text']) ?></p>
                                                            <?php if (!empty($question['options'])): ?>
                                                                <div class="ms-3">
                                                                    <?php foreach ($question['options'] as $option): ?>
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" 
                                                                                   name="answers[<?= $question['id'] ?>][selected_option_id]" 
                                                                                   value="<?= $option['id'] ?>" 
                                                                                   required>
                                                                            <label class="form-check-label">
                                                                                <?= htmlspecialchars($option['text']) ?>
                                                                            </label>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary">Submit Quiz</button>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-warning">No quiz questions available.</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php break;

                                case 'assignment': ?>
                                    <div class="mb-4">
                                        <h5>Assignment Details</h5>
                                        <pre class="bg-light p-2 small border">DEBUG: assignment_instructions = <?= var_export($item['assignment_instructions'] ?? null, true) ?>\ndue_date = <?= var_export($item['due_date'] ?? null, true) ?>\nmax_score = <?= var_export($item['max_score'] ?? null, true) ?>\nsubmission_type = <?= var_export($item['submission_type'] ?? null, true) ?>\ncontent = <?= var_export($item['content'] ?? null, true) ?></pre>
                                        <?php if (!empty($item['assignment_instructions'])): ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <h6>Instructions:</h6>
                                                    <div class="content-preview">
                                                        <?= nl2br(htmlspecialchars($item['assignment_instructions'])) ?>
                                                    </div>
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

                                        <!-- Submission Form -->
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>Submit Assignment</h6>
                                                <form id="assignmentForm" action="/lms-frontend/public/student/courses/<?= $course['id'] ?>/items/<?= $item['id'] ?>/submissions" method="POST" enctype="multipart/form-data">
                                                    <?php if ($item['submission_type'] === 'file'): ?>
                                                        <div class="mb-3">
                                                            <label for="submission_file" class="form-label">Upload File</label>
                                                            <input type="file" class="form-control" id="submission_file" name="file_path" required>
                                                            <div class="form-text">Accepted file types: PDF, DOC, DOCX, TXT (Max size: 10MB)</div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="mb-3">
                                                            <label for="submission_content" class="form-label">Your Submission</label>
                                                            <textarea class="form-control" id="submission_content" name="content" rows="10" required></textarea>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="mb-3">
                                                        <label for="comments" class="form-label">Comments (Optional)</label>
                                                        <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
                                                    </div>
                                                    <?php if (!empty($item['due_date'])): ?>
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-clock me-2"></i>
                                                            Due date: <?= date('F j, Y g:i A', strtotime($item['due_date'])) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <button type="submit" class="btn btn-primary">Submit Assignment</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php break;
                            endswitch; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handling
        document.addEventListener('DOMContentLoaded', function() {
            const quizForm = document.getElementById('quizForm');
            const assignmentForm = document.getElementById('assignmentForm');

            if (quizForm) {
                quizForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitQuiz(this);
                });
            }

            if (assignmentForm) {
                assignmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitAssignment(this);
                });
            }
        });

        async function submitQuiz(form) {
            try {
                const formData = new FormData(form);
                const answers = {};
                
                // Convert form data to the required format
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('answers[')) {
                        const questionId = key.match(/\[(\d+)\]/)[1];
                        if (!answers[questionId]) {
                            answers[questionId] = {
                                question_id: questionId,
                                selected_option_id: value
                            };
                        }
                    }
                }

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ answers: Object.values(answers) })
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Quiz submitted successfully!');
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to submit quiz');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
        }

        async function submitAssignment(form) {
            try {
                const formData = new FormData(form);
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Assignment submitted successfully!');
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to submit assignment');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
        }

        async function markComplete() {
            try {
                const response = await fetch(`/lms-frontend/public/student/courses/<?= $course['id'] ?>/items/<?= $item['id'] ?>/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message || 'Item marked as complete!');
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Failed to mark item as complete');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            }
        }
    </script>
</body>
</html> 