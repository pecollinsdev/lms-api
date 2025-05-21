<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details - LMS</title>
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
    <div class="container mt-5 pt-4">
      <div class="row justify-content-center">
        <!-- Main Content -->
        <div class="col-lg-8 mb-4">
          <div class="card shadow-sm mb-4 border-0 rounded-4" style="padding: 0.5rem 0.5rem 0.5rem 0.5rem;">
            <div class="card-body p-4">
              <h4 class="fw-semibold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Course Overview</h4>
              <p class="text-muted fs-5 mb-0">
                <?= nl2br(htmlspecialchars($course['description'] ?? 'No description provided.')) ?>
              </p>
            </div>
          </div>

          <?php if (!empty($course['modules'])): ?>
          <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary text-white d-flex align-items-center rounded-top-4" style="padding: 1rem 1.5rem;">
              <i class="fas fa-layer-group me-2"></i>
              <h5 class="mb-0">Course Modules</h5>
            </div>
            <div class="card-body p-4">
              <div class="accordion" id="modulesAccordion">
                <?php foreach ($course['modules'] as $idx => $module): ?>
                  <div class="accordion-item mb-3 border-0 shadow-sm rounded-3">
                    <h2 class="accordion-header" id="heading<?= $idx ?>">
                      <button class="accordion-button collapsed fw-bold fs-5 rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>" aria-expanded="false" aria-controls="collapse<?= $idx ?>">
                        <i class="fas fa-book-open me-2 text-secondary"></i><?= htmlspecialchars($module['title']) ?>
                        <span class="badge bg-light text-dark ms-3">Items: <?= count($module['module_items'] ?? []) ?></span>
                      </button>
                    </h2>
                    <div id="collapse<?= $idx ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $idx ?>" data-bs-parent="#modulesAccordion">
                      <div class="accordion-body">
                        <p class="text-muted mb-2"> <?= htmlspecialchars($module['description'] ?? '') ?> </p>
                        <?php if (!empty($module['module_items'])): ?>
                          <ul class="list-group list-group-flush">
                            <?php foreach ($module['module_items'] as $item): ?>
                              <li class="list-group-item d-flex justify-content-between align-items-center bg-light border-0 mb-2 rounded-2 px-3 py-2" style="box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?>/module/<?= $module['id'] ?>/item/<?= $item['id'] ?>" class="text-decoration-none text-dark w-100">
                                  <div>
                                    <i class="<?= $item['type'] === 'assignment' ? 'fas fa-tasks text-success' : ($item['type'] === 'quiz' ? 'fas fa-question-circle text-warning' : 'fas fa-file-alt text-info') ?> me-2"></i>
                                    <span class="fw-semibold"> <?= htmlspecialchars($item['title']) ?> </span>
                                    <span class="badge bg-secondary ms-2"> <?= ucfirst($item['type']) ?> </span>
                                    <div class="small text-muted ms-4"> <?= htmlspecialchars($item['description'] ?? '') ?> </div>
                                  </div>
                                  <?php if (!empty($item['due_date'])): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Due: <?= date('M d, Y', strtotime($item['due_date'])) ?></span>
                                  <?php endif; ?>
                                </a>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        <?php else: ?>
                          <div class="alert alert-info mb-0 rounded-2">
                            <i class="fas fa-info-circle me-1"></i> No items in this module yet.
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($course['students'])): ?>
          <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-header bg-info text-white d-flex align-items-center rounded-top-4" style="padding: 1rem 1.5rem;">
              <i class="fas fa-user-graduate me-2"></i>
              <h5 class="mb-0">Enrolled Students</h5>
            </div>
            <div class="card-body p-4">
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>ID</th>
                      <th>Enrollment Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($course['students'] as $student): ?>
                      <tr>
                        <td>
                          <div class="d-flex align-items-center">
                            <?php if (!empty($student['avatar'])): ?>
                              <img src="<?= htmlspecialchars($student['avatar']) ?>" class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                            <?php else: ?>
                              <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <?= strtoupper(substr($student['name'], 0, 1)) ?>
                              </div>
                            <?php endif; ?>
                            <?= htmlspecialchars($student['name']) ?>
                          </div>
                        </td>
                        <td><?= htmlspecialchars($student['email']) ?></td>
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td>
                          <?php if (!empty($student['enrollment_date'])): ?>
                            <?= date('M d, Y', strtotime($student['enrollment_date'])) ?>
                          <?php else: ?>
                            N/A
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <!-- Sidebar -->
        <div class="col-lg-4">
          <div class="card shadow-sm mb-4 border-0 rounded-4" style="padding: 0.5rem 0.5rem 0.5rem 0.5rem;">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3"><i class="fas fa-cogs me-2 text-primary"></i>Quick Actions</h5>
              <div class="d-grid gap-2 mb-3">
                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/edit" class="btn btn-outline-primary"><i class="fas fa-edit me-1"></i> Edit Course</a>
                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/assignments" class="btn btn-outline-success"><i class="fas fa-tasks me-1"></i> Assignments</a>
                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/students" class="btn btn-outline-info"><i class="fas fa-user-graduate me-1"></i> Students</a>
                <a href="/lms-frontend/public/instructor/courses/<?= $course['id'] ?? '' ?>/progress" class="btn btn-outline-warning"><i class="fas fa-chart-line me-1"></i> Progress</a>
              </div>
              <hr>
              <h6 class="fw-semibold mb-2">Course Stats</h6>
              <ul class="list-unstyled mb-0">
                <li class="mb-2"><i class="fas fa-users me-2 text-secondary"></i> <strong><?= count($course['students'] ?? []) ?></strong> Students</li>
                <li class="mb-2"><i class="fas fa-layer-group me-2 text-secondary"></i> <strong><?= count($course['modules'] ?? []) ?></strong> Modules</li>
                <li class="mb-2"><i class="fas fa-calendar me-2 text-secondary"></i> <?= !empty($course['start_date']) ? date('M d, Y', strtotime($course['start_date'])) : 'N/A' ?> - <?= !empty($course['end_date']) ? date('M d, Y', strtotime($course['end_date'])) : 'N/A' ?></li>
                <li class="mb-2"><i class="fas fa-user me-2 text-secondary"></i> <?= htmlspecialchars($course['instructor']['name'] ?? 'Unknown') ?></li>
                <?php if (isset($course['progress'])): ?>
                  <li class="mb-2">
                    <div class="mb-1">Progress</div>
                    <div class="progress" style="height: 8px;">
                      <div class="progress-bar bg-success" role="progressbar" style="width: <?= $course['progress'] ?? 0 ?>%" aria-valuenow="<?= $course['progress'] ?? 0 ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">Progress: <?= $course['progress'] ?? 0 ?>%</small>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Modernized Layout -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 