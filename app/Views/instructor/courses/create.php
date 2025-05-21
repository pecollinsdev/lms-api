<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - LMS</title>
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="course-creation-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Create New Course</h4>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>

                            <form action="/lms-frontend/public/instructor/courses/create" method="POST" class="needs-validation" novalidate>
                                <!-- Course Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           required 
                                           maxlength="255"
                                           value="<?php echo isset($formData['title']) ? htmlspecialchars($formData['title']) : ''; ?>"
                                           placeholder="Enter course title">
                                    <div class="invalid-feedback">
                                        Please enter a valid course title (max 255 characters).
                                    </div>
                                </div>

                                <!-- Course Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Course Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              placeholder="Enter course description"><?php echo isset($formData['description']) ? htmlspecialchars($formData['description']) : ''; ?></textarea>
                                </div>

                                <!-- Course Dates -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control datepicker" 
                                               id="start_date" 
                                               name="start_date" 
                                               required
                                               value="<?php echo isset($formData['start_date']) ? htmlspecialchars($formData['start_date']) : ''; ?>"
                                               placeholder="Select start date">
                                        <div class="invalid-feedback">
                                            Please select a valid start date.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               class="form-control datepicker" 
                                               id="end_date" 
                                               name="end_date" 
                                               required
                                               value="<?php echo isset($formData['end_date']) ? htmlspecialchars($formData['end_date']) : ''; ?>"
                                               placeholder="Select end date">
                                        <div class="invalid-feedback">
                                            Please select a valid end date (must be after or equal to start date).
                                        </div>
                                    </div>
                                </div>

                                <!-- Course Code and Credits -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="code" class="form-label">Course Code</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="code" 
                                               name="code"
                                               value="<?php echo isset($formData['code']) ? htmlspecialchars($formData['code']) : ''; ?>"
                                               placeholder="Enter course code">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="credits" class="form-label">Credits</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="credits" 
                                               name="credits"
                                               min="0"
                                               step="0.5"
                                               value="<?php echo isset($formData['credits']) ? htmlspecialchars($formData['credits']) : ''; ?>"
                                               placeholder="Enter number of credits">
                                    </div>
                                </div>

                                <!-- Course Status -->
                                <div class="mb-3">
                                    <label for="status" class="form-label">Course Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft" <?php echo (isset($formData['status']) && $formData['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="active" <?php echo (isset($formData['status']) && $formData['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="archived" <?php echo (isset($formData['status']) && $formData['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                                    </select>
                                </div>

                                <!-- Cover Image -->
                                <div class="mb-3">
                                    <label for="cover_image" class="form-label">Cover Image URL</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="cover_image" 
                                           name="cover_image"
                                           value="<?php echo isset($formData['cover_image']) ? htmlspecialchars($formData['cover_image']) : ''; ?>"
                                           placeholder="Enter cover image URL">
                                </div>

                                <!-- Published Status -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="is_published" 
                                               name="is_published"
                                               <?php echo isset($formData['is_published']) && $formData['is_published'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_published">Publish Course</label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/lms-frontend/public/instructor/courses" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Create Course
                                    </button>
                                </div>
                            </form>
                        </div>
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
        const startDatePicker = flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
            onChange: function(selectedDates, dateStr) {
                endDatePicker.set('minDate', dateStr);
                validateDates();
            }
        });

        const endDatePicker = flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
            onChange: function() {
                validateDates();
            }
        });

        // Function to validate dates
        function validateDates() {
            const startDate = startDatePicker.selectedDates[0];
            const endDate = endDatePicker.selectedDates[0];
            const endDateInput = document.getElementById('end_date');
            
            if (startDate && endDate && endDate < startDate) {
                endDateInput.setCustomValidity('End date must be after or equal to start date');
            } else {
                endDateInput.setCustomValidity('');
            }
        }

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