<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details - LMS</title>
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
        <div class="mb-4">
            <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $moduleItem['id'] ?>/submissions" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Submissions
            </a>
        </div>

        <!-- Header -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($moduleItem['title'] ?? 'Submission Details') ?></h2>
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
            </div>
        </div>

        <!-- Student Information -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Student Information
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img src="<?= isset($submission['user']['profile_picture']) && !empty($submission['user']['profile_picture']) ? $submission['user']['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($submission['user']['name'] ?? 'Student') ?>" 
                             class="rounded-circle" 
                             width="80" 
                             height="80" 
                             alt="Student Avatar">
                    </div>
                    <div class="flex-grow-1 ms-4">
                        <h4 class="mb-1"><?= htmlspecialchars($submission['user']['name'] ?? 'Unknown Student') ?></h4>
                        <p class="text-muted mb-2"><?= htmlspecialchars($submission['user']['email'] ?? '') ?></p>
                        <?php if (!empty($submission['user']['bio'])): ?>
                            <p class="mb-0"><?= htmlspecialchars($submission['user']['bio']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i>Submission Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Submission Status</h6>
                        <?php
                        $status = $submission['status'] ?? '';
                        $statusClass = match($status) {
                            'graded' => 'success',
                            'late' => 'warning',
                            'submitted' => 'info',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge bg-<?= $statusClass ?> fs-6">
                            <?= ucfirst(htmlspecialchars($status)) ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Submitted On</h6>
                        <p class="mb-0">
                            <?= !empty($submission['submitted_at']) ? date('F d, Y h:i A', strtotime($submission['submitted_at'])) : 'Not submitted' ?>
                        </p>
                    </div>
                </div>

                <?php if ($moduleItem['type'] === 'quiz'): ?>
                    <!-- Quiz Answers -->
                    <h6 class="text-muted mb-3">Quiz Answers</h6>
                    <?php
                    $content = json_decode($submission['content'] ?? '{}', true);
                    $answers = $content['answers'] ?? [];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Question</th>
                                    <th>Selected Answer</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $questions = $moduleItem['questions'] ?? [];
                                if (empty($questions)) {
                                    echo '<tr><td colspan="3" class="text-center">No questions found in the quiz</td></tr>';
                                } else {
                                    foreach ($questions as $question): 
                                        $answer = array_filter($answers, function($a) use ($question) {
                                            return isset($a['question_id']) && $a['question_id'] == $question['id'];
                                        });
                                        $answer = reset($answer);
                                        $selectedOption = null;
                                        if ($answer && isset($answer['selected_option_id'])) {
                                            $selectedOption = array_filter($question['options'] ?? [], function($o) use ($answer) {
                                                return $o['id'] == $answer['selected_option_id'];
                                            });
                                            $selectedOption = reset($selectedOption);
                                        }
                                    ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                if (isset($question['text'])) {
                                                    echo htmlspecialchars($question['text']);
                                                } else {
                                                    echo '<span class="text-danger">Question text missing (ID: ' . htmlspecialchars($question['id']) . ')</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($selectedOption) {
                                                    echo htmlspecialchars($selectedOption['text']);
                                                } else {
                                                    echo '<span class="text-muted">No answer provided</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($answer && $answer['is_correct']): ?>
                                                    <span class="badge bg-success">Correct</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Incorrect</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Assignment Content -->
                    <?php if (!empty($submission['file_path'])): ?>
                        <div class="mb-3">
                            <h6 class="text-muted mb-2">Submitted File</h6>
                            <a href="<?= htmlspecialchars($submission['file_path']) ?>" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-file me-1"></i> View File
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($submission['content'])): ?>
                        <div>
                            <h6 class="text-muted mb-2">Submission Content</h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(htmlspecialchars($submission['content'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($status === 'graded'): ?>
                    <!-- Grade Information -->
                    <div class="mt-4 pt-4 border-top">
                        <h6 class="text-muted mb-3">Grade Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Score:</strong>
                                    <?php
                                    $score = $submission['score'] ?? $submission['grade'] ?? 0;
                                    $maxScore = $submission['max_score'] ?? $moduleItem['max_score'] ?? 0;
                                    ?>
                                    <span class="fw-bold"><?= $score ?>/<?= $maxScore ?></span>
                                    (<?= $maxScore > 0 ? round(($score / $maxScore) * 100) : 0 ?>%)
                                </p>
                            </div>
                            <?php if (!empty($submission['feedback'])): ?>
                                <div class="col-12 mt-3">
                                    <h6 class="text-muted mb-2">Feedback</h6>
                                    <div class="p-3 bg-light rounded">
                                        <?= nl2br(htmlspecialchars($submission['feedback'])) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <?php if ($status !== 'graded'): ?>
                <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $moduleItem['id'] ?>/submissions/<?= $submission['id'] ?>/grade" 
                   class="btn btn-primary">
                    <i class="fas fa-check me-1"></i> Grade Submission
                </a>
            <?php else: ?>
                <a href="/lms-frontend/public/instructor/courses/<?= $courseId ?>/items/<?= $moduleItem['id'] ?>/submissions/<?= $submission['id'] ?>/grade" 
                   class="btn btn-outline-primary">
                    <i class="fas fa-edit me-1"></i> Update Grade
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 