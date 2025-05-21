<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Students - LMS</title>
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

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1"><?= htmlspecialchars($course['title'] ?? 'Course Students') ?></h1>
                <p class="text-muted mb-0">Manage enrolled students and view their progress</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Course
                </a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#enrollStudentModal">
                    <i class="fas fa-user-plus me-1"></i> Enroll Student
                </button>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Students</h6>
                        <h3 class="mb-0"><?= $progress['student_count'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Completion Rate</h6>
                        <h3 class="mb-0">
                            <?php
                            $totalCompletion = 0;
                            $studentCount = count($students);
                            foreach ($students as $student) {
                                $totalCompletion += $student['progress'];
                            }
                            echo $studentCount > 0 ? number_format($totalCompletion / $studentCount, 1) : 0;
                            ?>%
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Average Grade</h6>
                        <h3 class="mb-0">
                            <?php
                            $totalGrade = 0;
                            $studentCount = count($students);
                            foreach ($students as $student) {
                                $totalGrade += $student['average_grade'];
                            }
                            echo $studentCount > 0 ? number_format($totalGrade / $studentCount, 1) : 0;
                            ?>%
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Items</h6>
                        <h3 class="mb-0"><?= $progress['total_items'] ?? 0 ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Calculate grade distribution
        $gradeDistribution = [
            'A' => 0,
            'B' => 0,
            'C' => 0,
            'D' => 0,
            'F' => 0
        ];

        foreach ($students as $student) {
            $gradeLetter = $student['letter_grade'] ?? 'F';
            $gradeDistribution[$gradeLetter]++;
        }
        ?>

        <!-- Grade Distribution -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Grade Distribution</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $grades = ['A', 'B', 'C', 'D', 'F'];
                    $colors = ['success', 'info', 'primary', 'warning', 'danger'];
                    foreach ($grades as $index => $grade):
                        $count = $gradeDistribution[$grade];
                        $percentage = count($students) > 0 ? ($count / count($students)) * 100 : 0;
                    ?>
                        <div class="col">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-<?= $colors[$index] ?> fs-6"><?= $grade ?></span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-<?= $colors[$index] ?>" 
                                             role="progressbar" 
                                             style="width: <?= $percentage ?>%"
                                             aria-valuenow="<?= $percentage ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ms-3">
                                    <span class="text-muted"><?= $count ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Enrolled Students</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Enrolled Date</th>
                                <th>Progress</th>
                                <th>Average Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No students enrolled in this course</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($student['name'] ?? '') ?>" 
                                                     class="rounded-circle me-2" 
                                                     width="32" 
                                                     height="32" 
                                                     alt="Student Avatar">
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($student['name'] ?? 'Unknown Student') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($student['email'] ?? 'No email') ?></td>
                                        <td><?= isset($student['enrolled_at']) ? date('M d, Y', strtotime($student['enrolled_at'])) : 'N/A' ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?= $student['progress'] ?>%"
                                                         aria-valuenow="<?= $student['progress'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <span class="ms-2 small"><?= number_format($student['progress'], 1) ?>%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $student['letter_grade'] === 'A' ? 'success' : ($student['letter_grade'] === 'B' ? 'info' : ($student['letter_grade'] === 'C' ? 'primary' : ($student['letter_grade'] === 'D' ? 'warning' : 'danger'))) ?>">
                                                <?= $student['letter_grade'] ?> (<?= number_format($student['average_grade'], 1) ?>%)
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/students/<?= $student['id'] ?>/progress" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#unenrollModal"
                                                        data-student-id="<?= $student['id'] ?>"
                                                        data-student-name="<?= htmlspecialchars($student['name'] ?? 'Unknown Student') ?>">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
                <div class="card-footer bg-white py-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enroll Student Modal -->
    <div class="modal fade" id="enrollStudentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/students/enroll" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Enroll Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php if (isset($enrollError)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($enrollError) ?></div>
                        <?php endif; ?>
                        <?php if (isset($enrollSuccess)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($enrollSuccess) ?></div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Student Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="form-text">Enter the email address of the student to enroll</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Enroll Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Unenroll Student Modal -->
    <div class="modal fade" id="unenrollModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/unenroll" method="POST">
                    <input type="hidden" name="student_id" id="unenrollStudentId">
                    <div class="modal-header">
                        <h5 class="modal-title">Unenroll Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to unenroll <span id="unenrollStudentName" class="fw-bold"></span> from this course?</p>
                        <p class="text-danger mb-0">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Unenroll Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle unenroll modal
        document.getElementById('unenrollModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const studentId = button.getAttribute('data-student-id');
            const studentName = button.getAttribute('data-student-name');
            
            document.getElementById('unenrollStudentId').value = studentId;
            document.getElementById('unenrollStudentName').textContent = studentName;
        });
    </script>
</body>
</html> 