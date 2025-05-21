<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Module - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Flatpickr CSS for date picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .module-container {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .module-items {
            margin-top: 1rem;
        }
        .module-item {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }
        .drag-handle {
            cursor: move;
        }
    </style>
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
            <a href="/lms-frontend/public/instructor/courses/<?php echo $courseId; ?>/edit" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Course
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Create Module</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                    <i class="fas fa-plus me-1"></i> Add Module
                </button>
            </div>
        </div>

        <!-- Modules List -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="modulesContainer">
                    <?php if (!empty($modules)): ?>
                        <?php foreach ($modules as $module): ?>
                            <div class="module-container">
                                <div class="module-header">
                                    <h5 class="mb-0"><?= htmlspecialchars($module['title']) ?></h5>
                                    <div class="btn-group">
                                        <a href="/lms-frontend/public/instructor/courses/<?php echo $courseId; ?>/modules/<?php echo $module['id']; ?>/items/create" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Item
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editModule(<?php echo $module['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteModule(<?php echo $module['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <p class="text-muted mb-2"><?= htmlspecialchars($module['description'] ?? '') ?></p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= $module['start_date'] ? date('M d, Y', strtotime($module['start_date'])) : '' ?> - 
                                                <?= $module['end_date'] ? date('M d, Y', strtotime($module['end_date'])) : '' ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($module['items'])): ?>
                                    <div class="module-items">
                                        <?php foreach ($module['items'] as $item): ?>
                                            <div class="module-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['title']) ?></h6>
                                                        <div class="text-muted small">
                                                            <div>
                                                                <i class="fas fa-tag me-1"></i><?= ucfirst($item['type']) ?>
                                                                <?php if (isset($item['max_score'])): ?>
                                                                    <span class="ms-2">(Max Score: <?= $item['max_score'] ?>)</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if (!empty($item['due_date'])): ?>
                                                                <div class="mt-1">
                                                                    <i class="fas fa-calendar-alt me-1"></i>Due: <?= date('M d, Y', strtotime($item['due_date'])) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($item['description'])): ?>
                                                                <div class="mt-1">
                                                                    <i class="fas fa-info-circle me-1"></i><?= htmlspecialchars(substr($item['description'], 0, 100)) ?><?= strlen($item['description']) > 100 ? '...' : '' ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php
                                                            $hasAssignmentInfo = isset($item['content']['max_attempts']) || isset($item['content']['allow_late_submission']);
                                                            ?>
                                                            <?php if ($item['type'] === 'assignment' && isset($item['content']) && $hasAssignmentInfo): ?>
                                                                <div class="mt-1">
                                                                    <i class="fas fa-tasks me-1"></i>
                                                                    <?php if (isset($item['content']['max_attempts'])): ?>
                                                                        Max Attempts: <?= $item['content']['max_attempts'] ?>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($item['content']['allow_late_submission'])): ?>
                                                                        <span class="ms-2">Late Submission: <?= $item['content']['allow_late_submission'] ? 'Allowed' : 'Not Allowed' ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editItem(<?php echo $item['id']; ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?php echo $item['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <p class="text-muted mb-0">No items in this module yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <p class="text-muted mb-0">No modules created yet. Click "Add Module" to create one.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Add Module Modal -->
        <div class="modal fade" id="addModuleModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Module</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addModuleForm" action="/lms-frontend/public/instructor/courses/<?php echo $courseId; ?>/modules/create" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="moduleTitle" class="form-label">Module Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="moduleTitle" name="title" required>
                                <div class="invalid-feedback">
                                    Please enter a module title.
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="moduleDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="moduleDescription" name="description" rows="3"></textarea>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="moduleStartDate" class="form-label">Start Date</label>
                                    <input type="text" class="form-control datepicker" id="moduleStartDate" name="start_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="moduleEndDate" class="form-label">End Date</label>
                                    <input type="text" class="form-control datepicker" id="moduleEndDate" name="end_date">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add Module</button>
                            </div>
                        </form>
                    </div>
                </div>
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

        // Form validation
        document.getElementById('addModuleForm').addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html> 