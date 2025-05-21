<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - LMS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .module-item {
        transition: all 0.2s;
    }
    .module-item:hover {
        background-color: #f8f9fa;
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
            <a href="/lms-frontend/public/instructor/courses" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Courses
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/edit" method="POST" class="needs-validation" novalidate>
            <!-- Course Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Course Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($course['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cover_image" class="form-label">Cover Image URL</label>
                            <input type="url" class="form-control" id="cover_image" name="cover_image" value="<?= htmlspecialchars($course['cover_image'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($course['start_date'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($course['end_date'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="level" class="form-label">Level</label>
                            <select class="form-select" id="level" name="level" required>
                                <option value="">Select Level</option>
                                <option value="beginner" <?= ($course['level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                                <option value="intermediate" <?= ($course['level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                                <option value="advanced" <?= ($course['level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="programming" <?= ($course['category'] ?? '') === 'programming' ? 'selected' : '' ?>>Programming</option>
                                <option value="design" <?= ($course['category'] ?? '') === 'design' ? 'selected' : '' ?>>Design</option>
                                <option value="business" <?= ($course['category'] ?? '') === 'business' ? 'selected' : '' ?>>Business</option>
                                <option value="marketing" <?= ($course['category'] ?? '') === 'marketing' ? 'selected' : '' ?>>Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published" <?= ($course['is_published'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_published">
                                Publish Course
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modules Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Course Modules</h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                        <i class="fas fa-plus me-1"></i> Add Module
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($modules)): ?>
                        <?php foreach ($modules as $module): ?>
                            <div class="module-container mb-4 border rounded p-3 bg-light" data-module-id="<?= $module['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0"><?= htmlspecialchars($module['title']) ?></h5>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editModule(<?= $module['id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteModule(<?= $module['id'] ?>)">
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
                                                <?= date('M d, Y', strtotime($module['start_date'])) ?> - 
                                                <?= date('M d, Y', strtotime($module['end_date'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Module Items -->
                                <div class="module-items">
                                    <?php if (!empty($module['module_items'])): ?>
                                        <?php 
                                        // Debug information
                                        echo "<!-- Debug: Module ID: " . $module['id'] . " -->";
                                        echo "<!-- Debug: Items count: " . count($module['module_items']) . " -->";
                                        echo "<!-- Debug: Items data: " . json_encode($module['module_items']) . " -->";
                                        ?>
                                        <?php foreach ($module['module_items'] as $item): ?>
                                            <div class="module-item p-2 border rounded mb-2 bg-white" data-item-id="<?= $item['id'] ?>">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-grip-vertical drag-handle me-2 text-muted"></i>
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($item['title']) ?></h6>
                                                            <small class="text-muted"><?= ucfirst($item['type']) ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editItem(<?= $item['id'] ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteItem(<?= $item['id'] ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addItem(<?= $module['id'] ?>)">
                                        <i class="fas fa-plus me-1"></i> Add Item
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No modules added yet. Click "Add Module" to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Add Module Modal -->
    <div class="modal fade" id="addModuleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= isset($editingModule) ? 'Edit Module' : 'Add New Module' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addModuleForm" action="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/edit" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?= isset($editingModule) ? 'edit_module' : 'create_module' ?>">
                        <?php if (isset($editingModule)): ?>
                            <input type="hidden" name="module_id" value="<?= $editingModule['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="moduleTitle" class="form-label">Module Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="moduleTitle" name="title" value="<?= $editingModule['title'] ?? '' ?>" required>
                            <div class="invalid-feedback">
                                Please enter a module title.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="moduleDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="moduleDescription" name="description" rows="3"><?= $editingModule['description'] ?? '' ?></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="moduleStartDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="moduleStartDate" name="start_date" value="<?= $editingModule['start_date'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="moduleEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="moduleEndDate" name="end_date" value="<?= $editingModule['end_date'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><?= isset($editingModule) ? 'Update Module' : 'Add Module' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= isset($editingItem) ? 'Edit Module Item' : 'Add Module Item' ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addItemForm" action="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/edit" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?= isset($editingItem) ? 'edit_item' : 'create_item' ?>">
                        <input type="hidden" id="itemModuleId" name="module_id" value="<?= $editingItem['module_id'] ?? '' ?>">
                        <?php if (isset($editingItem)): ?>
                            <input type="hidden" name="item_id" value="<?= $editingItem['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="itemTitle" class="form-label">Item Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="itemTitle" name="title" value="<?= $editingItem['title'] ?? '' ?>" required>
                            <div class="invalid-feedback">
                                Please enter an item title.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="itemType" class="form-label">Item Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="itemType" name="type" required>
                                <option value="">Select Type</option>
                                <option value="video" <?= ($editingItem['type'] ?? '') === 'video' ? 'selected' : '' ?>>Video</option>
                                <option value="document" <?= ($editingItem['type'] ?? '') === 'document' ? 'selected' : '' ?>>Document</option>
                                <option value="quiz" <?= ($editingItem['type'] ?? '') === 'quiz' ? 'selected' : '' ?>>Quiz</option>
                                <option value="assignment" <?= ($editingItem['type'] ?? '') === 'assignment' ? 'selected' : '' ?>>Assignment</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select an item type.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="itemDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="itemDescription" name="description" rows="3"><?= $editingItem['description'] ?? '' ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="itemContent" class="form-label">Content</label>
                            <input type="text" class="form-control" id="itemContent" name="content" value="<?= $editingItem['content'] ?? '' ?>">
                        </div>

                        <!-- Assignment-specific fields -->
                        <div id="assignmentFields" style="display: none;">
                            <div class="mb-3">
                                <label for="assignmentInstructions" class="form-label">Assignment Instructions</label>
                                <textarea class="form-control" id="assignmentInstructions" name="assignment_instructions" rows="3"><?= $editingItem['assignment_instructions'] ?? '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="maxScore" class="form-label">Maximum Score</label>
                                <input type="number" class="form-control" id="maxScore" name="max_score" min="1" max="100" value="<?= $editingItem['max_score'] ?? '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="submissionType" class="form-label">Submission Type</label>
                                <select class="form-select" id="submissionType" name="submission_type">
                                    <option value="">Select submission type</option>
                                    <option value="file" <?= ($editingItem['submission_type'] ?? '') === 'file' ? 'selected' : '' ?>>File Upload</option>
                                    <option value="essay" <?= ($editingItem['submission_type'] ?? '') === 'essay' ? 'selected' : '' ?>>Essay</option>
                                    <option value="quiz" <?= ($editingItem['submission_type'] ?? '') === 'quiz' ? 'selected' : '' ?>>Quiz</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="itemDueDate" class="form-label">Due Date</label>
                            <input type="datetime-local" class="form-control" id="itemDueDate" name="due_date" value="<?= $editingItem['due_date'] ?? '' ?>">
                        </div>
                        <div class="mb-3">
                            <label for="itemOrder" class="form-label">Order</label>
                            <input type="number" class="form-control" id="itemOrder" name="order" value="<?= $editingItem['order'] ?? '0' ?>" min="0">
                            <div class="form-text">The position of this item in the module (0 for first position)</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><?= isset($editingItem) ? 'Update Item' : 'Add Item' ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sortable.js for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
    // Get course ID from PHP
    const courseId = <?= $course['id'] ?? 'null' ?>;
    
    // Check for edit parameters and show appropriate modal
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded');
        const urlParams = new URLSearchParams(window.location.search);
        const editModuleId = urlParams.get('edit_module');
        const editItemId = urlParams.get('edit_item');

        console.log('Edit Module ID:', editModuleId);
        console.log('Edit Item ID:', editItemId);

        // Handle module editing
        if (editModuleId) {
            const module = <?= json_encode($editingModule ?? null) ?>;
            console.log('Module data:', module);
            if (module) {
                // Populate form fields
                document.getElementById('moduleTitle').value = module.title || '';
                document.getElementById('moduleDescription').value = module.description || '';
                document.getElementById('moduleStartDate').value = module.start_date || '';
                document.getElementById('moduleEndDate').value = module.end_date || '';
                
                // Add module_id field if it doesn't exist
                let moduleIdInput = document.querySelector('input[name="module_id"]');
                if (!moduleIdInput) {
                    moduleIdInput = document.createElement('input');
                    moduleIdInput.type = 'hidden';
                    moduleIdInput.name = 'module_id';
                    document.getElementById('addModuleForm').appendChild(moduleIdInput);
                }
                moduleIdInput.value = module.id;
                
                // Update form action and button text
                document.querySelector('#addModuleForm input[name="action"]').value = 'edit_module';
                document.querySelector('#addModuleModal .modal-title').textContent = 'Edit Module';
                document.querySelector('#addModuleModal .modal-footer .btn-primary').textContent = 'Update Module';
                
                // Show the modal
                const moduleModal = new bootstrap.Modal(document.getElementById('addModuleModal'));
                moduleModal.show();
                console.log('Module modal shown');
            }
        }

        // Handle item editing
        if (editItemId) {
            const item = <?= json_encode($editingItem ?? null) ?>;
            console.log('Item data:', item);
            if (item) {
                // Populate form fields
                document.getElementById('itemTitle').value = item.title || '';
                document.getElementById('itemType').value = item.type || '';
                document.getElementById('itemDescription').value = item.description || '';
                document.getElementById('itemContent').value = item.content || '';
                document.getElementById('itemDueDate').value = item.due_date || '';
                document.getElementById('itemOrder').value = item.order || '0';
                document.getElementById('itemModuleId').value = item.module_id || '';
                
                // Add item_id field if it doesn't exist
                let itemIdInput = document.querySelector('input[name="item_id"]');
                if (!itemIdInput) {
                    itemIdInput = document.createElement('input');
                    itemIdInput.type = 'hidden';
                    itemIdInput.name = 'item_id';
                    document.getElementById('addItemForm').appendChild(itemIdInput);
                }
                itemIdInput.value = item.id;
                
                // Update form action and button text
                document.querySelector('#addItemForm input[name="action"]').value = 'edit_item';
                document.querySelector('#addItemModal .modal-title').textContent = 'Edit Module Item';
                document.querySelector('#addItemModal .modal-footer .btn-primary').textContent = 'Update Item';
                
                // Show the modal
                const itemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
                itemModal.show();
                console.log('Item modal shown');
                
                // Update form fields based on item type
                const typeSelect = document.getElementById('itemType');
                typeSelect.dispatchEvent(new Event('change'));
            }
        }
    });

    // Initialize drag and drop for module items
    document.querySelectorAll('.module-items').forEach(moduleItems => {
        new Sortable(moduleItems, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function(evt) {
                const moduleId = evt.to.closest('.module-container').dataset.moduleId;
                const items = Array.from(evt.to.children)
                    .filter(el => el.dataset.itemId)
                    .map((el, index) => ({
                        id: el.dataset.itemId,
                        order: index + 1
                    }));
                
                // Send reorder request
                fetch(`/lms-frontend/public/instructor/modules/${moduleId}/reorder`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ items })
                }).then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to reorder items');
                    }
                }).catch(error => {
                    console.error('Error reordering items:', error);
                    alert('Failed to reorder items. Please try again.');
                    location.reload();
                });
            }
        });
    });

    // Module functions
    function addModule() {
        document.getElementById('addModuleForm').reset();
        // Reset form action and hidden fields
        const form = document.getElementById('addModuleForm');
        form.querySelector('input[name="action"]').value = 'create_module';
        const moduleIdInput = form.querySelector('input[name="module_id"]');
        if (moduleIdInput) moduleIdInput.remove();
        
        // Update modal title and button
        document.querySelector('#addModuleModal .modal-title').textContent = 'Add New Module';
        document.querySelector('#addModuleModal .modal-footer .btn-primary').textContent = 'Add Module';
        
        new bootstrap.Modal(document.getElementById('addModuleModal')).show();
    }

    function editModule(moduleId) {
        if (!courseId) {
            console.error('Course ID is not defined');
            return;
        }
        console.log('Editing module:', moduleId);
        // Redirect to the same page with edit_module parameter
        window.location.href = `/lms-frontend/public/instructor/courses/${courseId}/edit?edit_module=${moduleId}`;
    }

    function deleteModule(moduleId) {
        if (!courseId) {
            console.error('Course ID is not defined');
            return;
        }
        if (confirm('Are you sure you want to delete this module? This will also delete all items within it.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/lms-frontend/public/instructor/courses/${courseId}/edit`;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_module';
            
            const moduleIdInput = document.createElement('input');
            moduleIdInput.type = 'hidden';
            moduleIdInput.name = 'module_id';
            moduleIdInput.value = moduleId;
            
            form.appendChild(actionInput);
            form.appendChild(moduleIdInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Item functions
    function addItem(moduleId) {
        document.getElementById('addItemForm').reset();
        document.getElementById('itemModuleId').value = moduleId;
        
        // Reset form action and hidden fields
        const form = document.getElementById('addItemForm');
        form.querySelector('input[name="action"]').value = 'create_item';
        const itemIdInput = form.querySelector('input[name="item_id"]');
        if (itemIdInput) itemIdInput.remove();
        
        // Reset modal title and button
        document.querySelector('#addItemModal .modal-title').textContent = 'Add Module Item';
        document.querySelector('#addItemModal .modal-footer .btn-primary').textContent = 'Add Item';
        
        new bootstrap.Modal(document.getElementById('addItemModal')).show();
    }

    function editItem(itemId) {
        if (!courseId) {
            console.error('Course ID is not defined');
            return;
        }
        console.log('Editing item:', itemId);
        // Redirect to the same page with edit_item parameter
        window.location.href = `/lms-frontend/public/instructor/module-items/${itemId}`;
    }

    function deleteItem(itemId) {
        if (!courseId) {
            console.error('Course ID is not defined');
            return;
        }
        if (confirm('Are you sure you want to delete this item?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/lms-frontend/public/instructor/module-items/${itemId}/delete`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Update form fields based on item type
    document.getElementById('itemType').addEventListener('change', function() {
        const type = this.value;
        const assignmentFields = document.getElementById('assignmentFields');
        const quizFields = document.getElementById('quizFields');
        const documentFields = document.getElementById('documentFields');
        const videoFields = document.getElementById('videoFields');

        // Hide all type-specific fields first
        [assignmentFields, quizFields, documentFields, videoFields].forEach(field => {
            if (field) field.style.display = 'none';
        });

        // Show relevant fields based on type
        switch (type) {
            case 'assignment':
                if (assignmentFields) assignmentFields.style.display = 'block';
                break;
            case 'quiz':
                if (quizFields) quizFields.style.display = 'block';
                break;
            case 'document':
                if (documentFields) documentFields.style.display = 'block';
                break;
            case 'video':
                if (videoFields) videoFields.style.display = 'block';
                break;
        }
    });

    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
