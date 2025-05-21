<?php
$isLoggedIn = isset($_SESSION['user_id']);
$isInstructor = isset($_SESSION['role']) && $_SESSION['role'] === 'instructor';
$isStudent = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$isHomePage = isset($isHomePage) && $isHomePage;
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/lms-frontend/public/">LMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (!$isLoggedIn): ?>
                    <?php if ($isHomePage): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#features">Features</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary ms-2" href="/lms-frontend/public/auth/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/lms-frontend/public/auth/register">Register</a>
                    </li>
                <?php else: ?>
                    <?php if ($isInstructor): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/lms-frontend/public/instructor/dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/lms-frontend/public/instructor/courses">My Courses</a>
                        </li>
                    <?php elseif ($isStudent): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/lms-frontend/public/student/dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/lms-frontend/public/student/courses">My Courses</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger ms-2" href="/lms-frontend/public/auth/logout">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 