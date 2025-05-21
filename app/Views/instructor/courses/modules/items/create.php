<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Module Item - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Flatpickr CSS for date picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        <div class="mb-3">
            <a href="/lms-frontend/public/instructor/courses/<?php echo $courseId; ?>/modules/create" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Modules
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Create Module Item</h1>
        </div>

        <!-- Add Item Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="/lms-frontend/public/instructor/courses/<?php echo $courseId; ?>/modules/<?php echo $moduleId; ?>/items/create" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="itemTitle" class="form-label">Item Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemTitle" name="title" required>
                        <div class="invalid-feedback">
                            Please enter an item title.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="itemType" class="form-label">Item Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="itemType" name="type" required>
                            <option value="">Select Type</option>
                            <option value="video">Video</option>
                            <option value="document">Document</option>
                            <option value="quiz">Quiz</option>
                            <option value="assignment">Assignment</option>
                        </select>
                        <div class="invalid-feedback">
                            Please select an item type.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="itemDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="itemDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="itemDueDate" class="form-label">Due Date</label>
                            <input type="text" class="form-control datepicker" id="itemDueDate" name="due_date">
                        </div>
                        <div class="col-md-6">
                            <label for="itemOrder" class="form-label">Order</label>
                            <input type="number" class="form-control" id="itemOrder" name="order" value="0" min="0">
                        </div>
                    </div>

                    <!-- Video Fields -->
                    <div id="videoFields" class="type-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="videoUrl" class="form-label">Video URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="videoUrl" name="video_url">
                        </div>
                        <div class="mb-3">
                            <label for="videoProvider" class="form-label">Provider</label>
                            <select class="form-select" id="videoProvider" name="video_provider">
                                <option value="youtube">YouTube</option>
                                <option value="vimeo">Vimeo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="videoDuration" class="form-label">Duration (minutes)</label>
                            <input type="number" class="form-control" id="videoDuration" name="video_duration" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowDownload" name="allow_download">
                                <label class="form-check-label" for="allowDownload">
                                    Allow Download
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autoComplete" name="auto_complete">
                                <label class="form-check-label" for="autoComplete">
                                    Auto-complete after watching
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="requiredWatchTime" class="form-label">Required Watch Time (minutes)</label>
                            <input type="number" class="form-control" id="requiredWatchTime" name="required_watch_time" min="0">
                        </div>
                    </div>

                    <!-- Document Fields -->
                    <div id="documentFields" class="type-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="documentUrl" class="form-label">Document URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="documentUrl" name="document_url">
                        </div>
                        <div class="mb-3">
                            <label for="documentType" class="form-label">Document Type</label>
                            <select class="form-select" id="documentType" name="document_type">
                                <option value="pdf">PDF</option>
                                <option value="doc">DOC</option>
                                <option value="docx">DOCX</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="documentSize" class="form-label">Size (KB)</label>
                            <input type="number" class="form-control" id="documentSize" name="document_size" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="documentAllowDownload" name="allow_download">
                                <label class="form-check-label" for="documentAllowDownload">
                                    Allow Download
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="requiredReadTime" class="form-label">Required Read Time (minutes)</label>
                            <input type="number" class="form-control" id="requiredReadTime" name="required_read_time" min="0">
                        </div>
                    </div>

                    <!-- Quiz/Assignment Fields -->
                    <div id="quizAssignmentFields" class="type-fields" style="display: none;">
                        <div class="mb-3">
                            <label for="maxAttempts" class="form-label">Maximum Attempts</label>
                            <input type="number" class="form-control" id="maxAttempts" name="max_attempts" value="1" min="1">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowLateSubmission" name="allow_late_submission">
                                <label class="form-check-label" for="allowLateSubmission">
                                    Allow Late Submission
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="lateSubmissionPenalty" class="form-label">Late Submission Penalty (%)</label>
                            <input type="number" class="form-control" id="lateSubmissionPenalty" name="late_submission_penalty" value="0" min="0" max="100">
                        </div>
                        <div class="mb-3">
                            <label for="maxScore" class="form-label">Maximum Score</label>
                            <input type="number" class="form-control" id="maxScore" name="max_score" value="100" min="0">
                        </div>
                        <div class="mb-3">
                            <label for="passingScore" class="form-label">Passing Score</label>
                            <input type="number" class="form-control" id="passingScore" name="passing_score" value="60" min="0">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showCorrectAnswers" name="show_correct_answers">
                                <label class="form-check-label" for="showCorrectAnswers">
                                    Show Correct Answers
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="showFeedback" name="show_feedback">
                                <label class="form-check-label" for="showFeedback">
                                    Show Feedback
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Create Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS for date picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        // Initialize date pickers
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        // Handle item type change
        document.getElementById('itemType').addEventListener('change', function() {
            // Hide all type-specific fields
            document.querySelectorAll('.type-fields').forEach(field => {
                field.style.display = 'none';
            });

            // Show fields based on selected type
            const type = this.value;
            if (type === 'video') {
                document.getElementById('videoFields').style.display = 'block';
            } else if (type === 'document') {
                document.getElementById('documentFields').style.display = 'block';
            } else if (type === 'quiz' || type === 'assignment') {
                document.getElementById('quizAssignmentFields').style.display = 'block';
            }
        });

        // Form validation and submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const type = document.getElementById('itemType').value;
            const formData = new FormData(this);

            // Create content_data and settings objects
            const contentData = {};
            const settings = {};

            switch (type) {
                case 'video':
                    contentData.url = formData.get('video_url');
                    contentData.provider = formData.get('video_provider');
                    contentData.duration = formData.get('video_duration');
                    settings.allow_download = formData.get('allow_download') === 'on';
                    settings.auto_complete = formData.get('auto_complete') === 'on';
                    settings.required_watch_time = formData.get('required_watch_time');
                    break;

                case 'document':
                    contentData.url = formData.get('document_url');
                    contentData.type = formData.get('document_type');
                    contentData.size = formData.get('document_size');
                    settings.allow_download = formData.get('allow_download') === 'on';
                    settings.required_read_time = formData.get('required_read_time');
                    break;

                case 'quiz':
                case 'assignment':
                    settings.max_attempts = formData.get('max_attempts');
                    settings.allow_late_submission = formData.get('allow_late_submission') === 'on';
                    settings.late_submission_penalty = formData.get('late_submission_penalty');
                    settings.max_score = formData.get('max_score');
                    settings.passing_score = formData.get('passing_score');
                    settings.show_correct_answers = formData.get('show_correct_answers') === 'on';
                    settings.show_feedback = formData.get('show_feedback') === 'on';
                    break;
            }

            // Add hidden fields for content_data and settings
            const contentDataInput = document.createElement('input');
            contentDataInput.type = 'hidden';
            contentDataInput.name = 'content_data';
            contentDataInput.value = JSON.stringify(contentData);
            this.appendChild(contentDataInput);

            const settingsInput = document.createElement('input');
            settingsInput.type = 'hidden';
            settingsInput.name = 'settings';
            settingsInput.value = JSON.stringify(settings);
            this.appendChild(settingsInput);

            // Submit the form
            this.submit();
        });
    </script>
</body>
</html> 