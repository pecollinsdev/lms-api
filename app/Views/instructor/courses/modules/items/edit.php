<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= ucfirst($module_item['type']) ?> - LMS</title>
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
                        <a class="nav-link" href="/lms-frontend/public/instructor/courses">Courses</a>
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

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Edit <?= ucfirst($module_item['type']) ?></h5>
                            <a href="/lms-frontend/public/instructor/courses/<?= $course_id ?>/modules/<?= $module_id ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Module
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="/lms-frontend/public/instructor/courses/<?= $course_id ?>/modules/<?= $module_id ?>/items/<?= $module_item['id'] ?>/update" 
                              method="POST" 
                              enctype="multipart/form-data"
                              id="moduleItemForm">
                            
                            <!-- Basic Information -->
                            <div class="mb-4">
                                <h6 class="mb-3">Basic Information</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="title" 
                                                   name="title" 
                                                   value="<?= htmlspecialchars($module_item['title']) ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Type</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="<?= ucfirst($module_item['type']) ?>" 
                                                   disabled>
                                            <input type="hidden" 
                                                   name="type" 
                                                   value="<?= $module_item['type'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="3"><?= htmlspecialchars($module_item['description']) ?></textarea>
                                </div>
                            </div>

                            <!-- Type-Specific Content -->
                            <?php if ($module_item['type'] === 'video'): ?>
                                <div class="mb-4">
                                    <h6 class="mb-3">Video Details</h6>
                                    <div class="mb-3">
                                        <label for="video_url" class="form-label">Video URL</label>
                                        <input type="url" class="form-control" id="video_url" name="video_url" value="<?= htmlspecialchars($module_item['content_data']['url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="provider" class="form-label">Provider</label>
                                        <input type="text" class="form-control" id="provider" name="provider" value="<?= htmlspecialchars($module_item['content_data']['provider'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (seconds)</label>
                                        <input type="number" class="form-control" id="duration" name="duration" value="<?= htmlspecialchars($module_item['content_data']['duration'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="allow_download" name="allow_download" value="1" <?= !empty($module_item['content_data']['allow_download']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allow_download">Allow Download</label>
                                    </div>
                                    <div class="mb-3">
                                        <label for="thumbnail" class="form-label">Thumbnail URL</label>
                                        <input type="url" class="form-control" id="thumbnail" name="thumbnail" value="<?= htmlspecialchars($module_item['content_data']['thumbnail'] ?? '') ?>">
                                    </div>
                                </div>
                            <?php elseif ($module_item['type'] === 'document'): ?>
                                <div class="mb-4">
                                    <h6 class="mb-3">Document Details</h6>
                                    <div class="mb-3">
                                        <label for="document_url" class="form-label">Document URL</label>
                                        <input type="url" class="form-control" id="document_url" name="document_url" value="<?= htmlspecialchars($module_item['content_data']['url'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="file_name" class="form-label">File Name</label>
                                        <input type="text" class="form-control" id="file_name" name="file_name" value="<?= htmlspecialchars($module_item['content_data']['file_name'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="type" class="form-label">File Type</label>
                                        <input type="text" class="form-control" id="type" name="type" value="<?= htmlspecialchars($module_item['content_data']['type'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="size" class="form-label">File Size (bytes)</label>
                                        <input type="number" class="form-control" id="size" name="size" value="<?= htmlspecialchars($module_item['content_data']['size'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="allow_download_doc" name="allow_download_doc" value="1" <?= !empty($module_item['content_data']['allow_download']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allow_download_doc">Allow Download</label>
                                    </div>
                                </div>
                            <?php elseif ($module_item['type'] === 'assignment'): ?>
                                <!-- Assignment Specific Fields -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Assignment Details</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="due_date" class="form-label">Due Date</label>
                                                <input type="datetime-local" 
                                                       class="form-control" 
                                                       id="due_date" 
                                                       name="due_date" 
                                                       value="<?= date('Y-m-d\TH:i', strtotime($module_item['content_data']['due_date'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_score" class="form-label">Maximum Score</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="max_score" 
                                                       name="max_score" 
                                                       value="<?= $module_item['content_data']['max_score'] ?? 100 ?>" 
                                                       min="0" 
                                                       max="100">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructions" class="form-label">Instructions</label>
                                        <textarea class="form-control" 
                                                  id="instructions" 
                                                  name="instructions" 
                                                  rows="5"><?= htmlspecialchars($module_item['content_data']['instructions'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachment" class="form-label">Attachment</label>
                                        <?php if (!empty($module_item['content_data']['attachment'])): ?>
                                            <div class="mb-2">
                                                <a href="<?= $module_item['content_data']['attachment'] ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file me-1"></i> View Current Attachment
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" 
                                               class="form-control" 
                                               id="attachment" 
                                               name="attachment">
                                        <small class="text-muted">Leave empty to keep the current attachment</small>
                                    </div>
                                </div>

                            <?php elseif ($module_item['type'] === 'quiz'): ?>
                                <!-- Quiz Specific Fields -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Quiz Details</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="due_date" class="form-label">Due Date</label>
                                                <input type="datetime-local" 
                                                       class="form-control" 
                                                       id="due_date" 
                                                       name="due_date" 
                                                       value="<?= date('Y-m-d\TH:i', strtotime($module_item['content_data']['due_date'] ?? '')) ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="time_limit" class="form-label">Time Limit (minutes)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="time_limit" 
                                                       name="time_limit" 
                                                       value="<?= $module_item['content_data']['time_limit'] ?? 30 ?>" 
                                                       min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructions" class="form-label">Instructions</label>
                                        <textarea class="form-control" 
                                                  id="instructions" 
                                                  name="instructions" 
                                                  rows="3"><?= htmlspecialchars($module_item['content_data']['instructions'] ?? '') ?></textarea>
                                    </div>

                                    <!-- Questions Section (AJAX CRUD) -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Questions</h6>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="showAddQuestionModal()">
                                                <i class="fas fa-plus me-1"></i> Add Question
                                            </button>
                                        </div>
                                        <div id="questions-container"></div>
                                    </div>
                                </div>

                            <?php elseif ($module_item['type'] === 'resource'): ?>
                                <!-- Resource Specific Fields -->
                                <div class="mb-4">
                                    <h6 class="mb-3">Resource Details</h6>
                                    <div class="mb-3">
                                        <label for="resource_type" class="form-label">Resource Type</label>
                                        <select class="form-select" 
                                                id="resource_type" 
                                                name="resource_type" 
                                                onchange="toggleResourceFields(this.value)">
                                            <option value="file" <?= ($module_item['content_data']['resource_type'] ?? '') === 'file' ? 'selected' : '' ?>>
                                                File
                                            </option>
                                            <option value="url" <?= ($module_item['content_data']['resource_type'] ?? '') === 'url' ? 'selected' : '' ?>>
                                                URL
                                            </option>
                                            <option value="text" <?= ($module_item['content_data']['resource_type'] ?? '') === 'text' ? 'selected' : '' ?>>
                                                Text Content
                                            </option>
                                        </select>
                                    </div>

                                    <!-- File Upload -->
                                    <div id="file-fields" class="mb-3" style="display: <?= ($module_item['content_data']['resource_type'] ?? '') === 'file' ? 'block' : 'none' ?>;">
                                        <?php if (!empty($module_item['content_data']['file_url'])): ?>
                                            <div class="mb-2">
                                                <a href="<?= $module_item['content_data']['file_url'] ?>" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file me-1"></i> View Current File
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" 
                                               class="form-control" 
                                               id="file" 
                                               name="file">
                                        <small class="text-muted">Leave empty to keep the current file</small>
                                    </div>

                                    <!-- URL Input -->
                                    <div id="url-fields" class="mb-3" style="display: <?= ($module_item['content_data']['resource_type'] ?? '') === 'url' ? 'block' : 'none' ?>;">
                                        <label for="url" class="form-label">URL</label>
                                        <input type="url" 
                                               class="form-control" 
                                               id="url" 
                                               name="url" 
                                               value="<?= htmlspecialchars($module_item['content_data']['url'] ?? '') ?>">
                                    </div>

                                    <!-- Text Content -->
                                    <div id="text-fields" class="mb-3" style="display: <?= ($module_item['content_data']['resource_type'] ?? '') === 'text' ? 'block' : 'none' ?>;">
                                        <label for="content" class="form-label">Content</label>
                                        <textarea class="form-control" 
                                                  id="content" 
                                                  name="content" 
                                                  rows="10"><?= htmlspecialchars($module_item['content_data']['content'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Quiz Question Management
        let questionCount = <?= count($module_item['content_data']['questions'] ?? []) ?>;

        function addQuestion() {
            const container = document.getElementById('questions-container');
            const questionIndex = questionCount++;
            
            const questionHtml = `
                <div class="card mb-3 question-card" data-question-index="${questionIndex}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="mb-0">Question ${questionIndex + 1}</h6>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(${questionIndex})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Text</label>
                            <textarea class="form-control" 
                                      name="questions[${questionIndex}][text]" 
                                      required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Type</label>
                            <select class="form-select" 
                                    name="questions[${questionIndex}][type]" 
                                    onchange="updateOptionsField(this.value)">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="true_false">True/False</option>
                                <option value="short_answer">Short Answer</option>
                            </select>
                        </div>
                        <div class="options-container" id="options-${questionIndex}">
                            <div class="mb-2 option-row">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input type="radio" 
                                               name="questions[${questionIndex}][correct_option]" 
                                               value="0" checked>
                                    </div>
                                    <input type="text" 
                                           class="form-control" 
                                           name="questions[${questionIndex}][options][]" 
                                           required>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="removeOption(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" 
                                class="btn btn-outline-primary btn-sm" 
                                onclick="addOption(${questionIndex})">
                            <i class="fas fa-plus me-1"></i> Add Option
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', questionHtml);
        }

        function removeQuestion(index) {
            const question = document.querySelector(`.question-card[data-question-index="${index}"]`);
            if (question) {
                question.remove();
                updateQuestionNumbers();
            }
        }

        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-card');
            questions.forEach((question, index) => {
                question.querySelector('h6').textContent = `Question ${index + 1}`;
                question.dataset.questionIndex = index;
                
                // Update all input names
                const inputs = question.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    if (input.name) {
                        input.name = input.name.replace(/questions\[\d+\]/, `questions[${index}]`);
                    }
                });
            });
        }

        function addOption(questionIndex) {
            const optionsContainer = document.getElementById(`options-${questionIndex}`);
            const optionCount = optionsContainer.querySelectorAll('.option-row').length;
            
            const optionHtml = `
                <div class="mb-2 option-row">
                    <div class="input-group">
                        <div class="input-group-text">
                            <input type="radio" name="questions[${questionIndex}][correct_option]" value="${optionCount}">
                        </div>
                        <input type="text" class="form-control" name="questions[${questionIndex}][options][]" required>
                        <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            optionsContainer.insertAdjacentHTML('beforeend', optionHtml);
        }

        function removeOption(button) {
            const optionRow = button.closest('.option-row');
            if (optionRow) {
                optionRow.remove();
            }
        }

        function updateOptionsField(questionIndex, type) {
            const optionsContainer = document.getElementById(`options-${questionIndex}`);
            const addOptionButton = optionsContainer.nextElementSibling;
            
            if (type === 'multiple_choice') {
                optionsContainer.innerHTML = `
                    <div class="mb-2 option-row">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input type="radio" name="questions[${questionIndex}][correct_option]" value="0" checked>
                            </div>
                            <input type="text" class="form-control" name="questions[${questionIndex}][options][]" required>
                            <button type="button" class="btn btn-outline-danger" onclick="removeOption(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                addOptionButton.style.display = 'inline-block';
            } else if (type === 'true_false') {
                optionsContainer.innerHTML = `
                    <div class="mb-2">
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="questions[${questionIndex}][correct_option]" value="true" checked>
                            <label class="form-check-label">True</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="questions[${questionIndex}][correct_option]" value="false">
                            <label class="form-check-label">False</label>
                        </div>
                    </div>
                `;
                addOptionButton.style.display = 'none';
            } else {
                optionsContainer.innerHTML = '';
                addOptionButton.style.display = 'none';
            }
        }

        // Resource Type Management
        function toggleResourceFields(type) {
            document.getElementById('file-fields').style.display = type === 'file' ? 'block' : 'none';
            document.getElementById('url-fields').style.display = type === 'url' ? 'block' : 'none';
            document.getElementById('text-fields').style.display = type === 'text' ? 'block' : 'none';
        }

        // Form Validation
        document.getElementById('moduleItemForm').addEventListener('submit', function(e) {
            const type = document.querySelector('input[name="type"]').value;
            
            if (type === 'quiz') {
                // Only check for questions if we're actually submitting the form
                if (e.submitter && e.submitter.type === 'submit') {
                    const questions = document.querySelectorAll('.question-card');
                    if (questions.length === 0) {
                        e.preventDefault();
                        alert('Please add at least one question to the quiz.');
                        return;
                    }
                }
            }
        });
    </script>

    <!-- Question Modal -->
    <div class="modal fade" id="questionModal" tabindex="-1" aria-labelledby="questionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="questionForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="questionModalLabel">Add/Edit Question</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="questionId" name="questionId">
                        <div class="mb-3">
                            <label class="form-label">Question Prompt</label>
                            <textarea class="form-control" id="questionPrompt" name="prompt" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" id="questionType" name="type" onchange="toggleOptionsFields(this.value)">
                                <option value="multiple_choice">Multiple Choice</option>
                                <option value="text">Text</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Points</label>
                            <input type="number" class="form-control" id="questionPoints" name="points" min="0" value="1" required>
                        </div>
                        <div id="optionsFields" style="display:none">
                            <label class="form-label">Options</label>
                            <div id="optionsList"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addOptionField()">Add Option</button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Option Modal -->
    <div class="modal fade" id="optionModal" tabindex="-1" aria-labelledby="optionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="optionForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="optionModalLabel">Add/Edit Option</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="optionId" name="optionId">
                        <input type="hidden" id="optionQuestionId" name="questionId">
                        <div class="mb-3">
                            <label class="form-label">Option Text</label>
                            <input type="text" class="form-control" id="optionText" name="text" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
    const API_BASE = 'http://localhost/lms-api/api/public/api';
    const moduleItemId = <?= json_encode($module_item['id']) ?>;

    // Fetch and render questions on page load
    function fetchQuestions() {
        fetch(`${API_BASE}/module-items/${moduleItemId}/questions`)
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                console.log('Questions API response:', data);
                
                // Handle different possible data structures
                let questions = [];
                if (data.data) {
                    // If data is nested under data.data
                    questions = Array.isArray(data.data) ? data.data : 
                              (data.data.data ? data.data.data : []);
                } else if (Array.isArray(data)) {
                    // If data is directly an array
                    questions = data;
                }
                
                console.log('Processed questions:', questions);
                
                if (questions.length === 0) {
                    console.log('No questions found in the response');
                }
                
                questions.forEach(q => {
                    console.log(`Question ${q.id} data:`, q);
                    if (q.options) {
                        console.log(`Question ${q.id} options:`, q.options);
                    }
                });
                
                renderQuestions(questions);
            })
            .catch(error => {
                console.error('Error fetching questions:', error);
                // Show error to user
                const container = document.getElementById('questions-container');
                if (container) {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            Failed to load questions. Please try refreshing the page.
                            <br>
                            Error: ${error.message}
                        </div>
                    `;
                }
            });
    }

    document.addEventListener('DOMContentLoaded', fetchQuestions);

    function renderQuestions(questions) {
        if (!Array.isArray(questions)) {
            questions = [];
        }
        const container = document.getElementById('questions-container');
        container.innerHTML = '';
        
        questions.forEach(q => {
            const questionDiv = document.createElement('div');
            questionDiv.className = 'card mb-3 question-card';
            questionDiv.setAttribute('data-question-id', q.id);
            questionDiv.innerHTML = `
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-2">
                                <h6 class="mb-0 me-2">Question ${q.id}</h6>
                                <span class="badge bg-secondary me-2">${q.type.replace('_', ' ')}</span>
                                <span class="badge bg-info">${q.points} points</span>
                            </div>
                            <p class="mb-0">${q.prompt}</p>
                        </div>
                        <div class="ms-3">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editQuestion(${q.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${q.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-3" id="options-list-${q.id}"></div>
                    ${q.type === 'multiple_choice' ? `
                        <button type="button" class="btn btn-sm btn-success mt-2" onclick="showAddOptionModal(${q.id})">
                            <i class="fas fa-plus me-1"></i> Add Option
                        </button>
                    ` : ''}
                </div>
            `;
            container.appendChild(questionDiv);
            
            // If options are included in the question data, render them directly
            if (q.options && Array.isArray(q.options)) {
                renderOptions(q.id, q.options);
            } else if (q.type === 'multiple_choice') {
                // Only fetch options for multiple choice questions
                fetchOptions(q.id, moduleItemId);
            }
        });
    }

    function fetchOptions(questionId, moduleItemId) {
        console.log(`Fetching options for question ${questionId}`);
        fetch(`${API_BASE}/questions/${questionId}/options`)
            .then(res => {
                console.log(`Response status: ${res.status}`);
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                return res.text().then(text => {
                    console.log('Raw response:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Invalid JSON text:', text);
                        throw new Error('Invalid JSON response from server');
                    }
                });
            })
            .then(data => {
                console.log('Parsed data:', data);
                renderOptions(questionId, Array.isArray(data.data) ? data.data : []);
            })
            .catch(error => {
                console.error('Error fetching options:', error);
                // Render empty options list on error
                renderOptions(questionId, []);
            });
    }

    function renderOptions(questionId, options) {
        const list = document.getElementById(`options-list-${questionId}`);
        if (!list) return;
        
        // Get the question type from the parent card
        const questionCard = list.closest('.question-card');
        const questionType = questionCard ? questionCard.querySelector('.badge.bg-secondary').textContent.trim() : '';
        
        if (!Array.isArray(options) || options.length === 0) {
            // Only show "No options available" for multiple choice questions
            if (questionType === 'multiple choice') {
                list.innerHTML = '<div class="text-muted small">No options available</div>';
            } else {
                list.innerHTML = ''; // Empty for non-multiple choice questions
            }
            return;
        }
        
        list.innerHTML = `
            <div class="list-group">
                ${options.map(opt => `
                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span>${opt.text}</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editOption(${opt.id}, ${questionId})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteOption(${opt.id}, ${questionId})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // CRUD for Questions
    function showAddQuestionModal() {
        document.getElementById('questionForm').reset();
        document.getElementById('questionId').value = '';
        document.getElementById('questionModalLabel').textContent = 'Add Question';
        document.getElementById('optionsFields').style.display = 'none';
        resetOptionsFields();
        new bootstrap.Modal(document.getElementById('questionModal')).show();
    }

    function editQuestion(questionId) {
        fetch(`${API_BASE}/questions/${questionId}`)
            .then(res => res.json())
            .then(data => {
                const q = data.data;
                document.getElementById('questionId').value = q.id;
                document.getElementById('questionPrompt').value = q.prompt;
                document.getElementById('questionType').value = q.type;
                document.getElementById('questionPoints').value = q.points || 1;
                document.getElementById('questionModalLabel').textContent = 'Edit Question';
                toggleOptionsFields(q.type);
                resetOptionsFields();
                if (q.type === 'multiple_choice' && Array.isArray(q.options)) {
                    q.options.forEach(opt => addOptionField(opt.text, !!opt.is_correct));
                }
                new bootstrap.Modal(document.getElementById('questionModal')).show();
            });
    }

    document.getElementById('questionForm').onsubmit = function(e) {
        e.preventDefault();
        const id = document.getElementById('questionId').value;
        const prompt = document.getElementById('questionPrompt').value;
        const type = document.getElementById('questionType').value;
        const points = parseInt(document.getElementById('questionPoints').value, 10) || 1;
        let payload = { type, prompt, points };
        if (type === 'multiple_choice') {
            const options = [];
            const optionDivs = document.querySelectorAll('#optionsList .input-group');
            optionDivs.forEach(div => {
                const text = div.querySelector('input[name="optionText"]').value;
                const is_correct = div.querySelector('input[name="optionCorrect"]').checked;
                options.push({ text, is_correct });
            });
            if (options.length < 2) {
                alert('Multiple choice questions require at least 2 options.');
                return;
            }
            payload.options = options;
        }
        if (id) {
            fetch(`${API_BASE}/questions/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                console.log('Update question response:', data);
                fetchQuestions();
                bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
            })
            .catch(err => {
                console.error('Update question error:', err);
                alert('Failed to update question.');
            });
        } else {
            fetch(`${API_BASE}/module-items/${moduleItemId}/questions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                console.log('Create question response:', data);
                fetchQuestions();
                bootstrap.Modal.getInstance(document.getElementById('questionModal')).hide();
            })
            .catch(err => {
                console.error('Create question error:', err);
                alert('Failed to create question.');
            });
        }
    };

    function deleteQuestion(id) {
        if (confirm('Are you sure you want to delete this question?')) {
            fetch(`${API_BASE}/questions/${id}`, { 
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to delete question');
                }
                // Don't try to parse empty responses
                if (response.status === 204 || response.headers.get('content-length') === '0') {
                    return null;
                }
                return response.json();
            })
            .then(() => {
                // Remove the question card from the DOM
                const questionCard = document.querySelector(`.question-card[data-question-id="${id}"]`);
                if (questionCard) {
                    questionCard.remove();
                }
                // Refresh the questions list
                fetchQuestions();
            })
            .catch(error => {
                console.error('Error deleting question:', error);
                // Only show error if the question wasn't actually deleted
                const questionCard = document.querySelector(`.question-card[data-question-id="${id}"]`);
                if (questionCard) {
                    alert('Failed to delete question. Please try again.');
                }
            });
        }
    }

    // CRUD for Options
    function showAddOptionModal(questionId) {
        document.getElementById('optionForm').reset();
        document.getElementById('optionId').value = '';
        document.getElementById('optionQuestionId').value = questionId;
        document.getElementById('optionModalLabel').textContent = 'Add Option';
        new bootstrap.Modal(document.getElementById('optionModal')).show();
    }

    function editOption(optionId, questionId) {
        fetch(`${API_BASE}/options/${optionId}`)
            .then(res => res.json())
            .then(data => {
                const opt = data.data;
                document.getElementById('optionId').value = opt.id;
                document.getElementById('optionQuestionId').value = questionId;
                document.getElementById('optionText').value = opt.text;
                document.getElementById('optionModalLabel').textContent = 'Edit Option';
                new bootstrap.Modal(document.getElementById('optionModal')).show();
            });
    }

    document.getElementById('optionForm').onsubmit = function(e) {
        e.preventDefault();
        const id = document.getElementById('optionId').value;
        const questionId = document.getElementById('optionQuestionId').value;
        const text = document.getElementById('optionText').value;
        const payload = { text };
        if (id) {
            fetch(`${API_BASE}/options/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(() => { fetchOptions(questionId, moduleItemId); bootstrap.Modal.getInstance(document.getElementById('optionModal')).hide(); });
        } else {
            fetch(`${API_BASE}/module-items/${moduleItemId}/questions/${questionId}/options`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).then(() => { fetchOptions(questionId, moduleItemId); bootstrap.Modal.getInstance(document.getElementById('optionModal')).hide(); });
        }
    };

    function deleteOption(id, questionId) {
        if (confirm('Delete this option?')) {
            fetch(`${API_BASE}/options/${id}`, { method: 'DELETE' })
                .then(() => fetchOptions(questionId, moduleItemId));
        }
    }

    function toggleOptionsFields(type) {
        const optionsFields = document.getElementById('optionsFields');
        const addOptionButton = optionsFields.querySelector('button');
        if (type === 'multiple_choice') {
            optionsFields.style.display = 'block';
            if (addOptionButton) addOptionButton.style.display = 'inline-block';
        } else {
            optionsFields.style.display = 'none';
            if (addOptionButton) addOptionButton.style.display = 'none';
        }
    }

    function addOptionField(text = '', isCorrect = false) {
        const optionsList = document.getElementById('optionsList');
        const idx = optionsList.children.length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'input-group mb-2';
        optionDiv.innerHTML = `
            <input type="text" class="form-control" name="optionText" placeholder="Option text" value="${text}" required>
            <div class="input-group-text">
                <input type="checkbox" name="optionCorrect" ${isCorrect ? 'checked' : ''}> Correct
            </div>
            <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">&times;</button>
        `;
        optionsList.appendChild(optionDiv);
    }

    function resetOptionsFields() {
        document.getElementById('optionsList').innerHTML = '';
    }
    </script>
</body>
</html> 