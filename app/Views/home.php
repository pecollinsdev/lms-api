<?php
\App\Core\Layout::setVariable('isHomePage', true);
\App\Core\Layout::start('main');

\App\Core\Layout::section('title');
echo 'LMS - Learning Management System';
\App\Core\Layout::endSection();

\App\Core\Layout::section('content');
?>
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Transform Your Learning Journey</h1>
                <p class="lead mb-4">Access quality education anytime, anywhere. Our Learning Management System provides a seamless learning experience for students and educators.</p>
                <div class="d-flex gap-3">
                    <a href="/lms-frontend/public/auth/register" class="btn btn-primary btn-lg">Get Started</a>
                    <a href="#features" class="btn btn-outline-primary btn-lg">Learn More</a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="/lms-frontend/public/images/undraw_online-learning_tgmv.svg" alt="Online Learning Illustration" class="img-fluid hero-image">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Our LMS?</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <i class="fas fa-book-open fa-3x mb-3 text-primary"></i>
                    <h3>Interactive Learning</h3>
                    <p>Engage with interactive content and real-time feedback to enhance your learning experience.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                    <h3>Collaborative Tools</h3>
                    <p>Connect with peers and instructors through our integrated communication tools.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center p-4">
                    <i class="fas fa-chart-line fa-3x mb-3 text-primary"></i>
                    <h3>Track Progress</h3>
                    <p>Monitor your learning journey with detailed analytics and progress tracking.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="mb-4">About Our Platform</h2>
                <p class="lead">We're committed to providing a modern, accessible, and effective learning environment for everyone.</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> User-friendly interface</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Mobile-responsive design</li>
                    <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> 24/7 support</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <img src="/lms-frontend/public/images/undraw_online-collaboration_xon8.svg" alt="Online Collaboration Illustration" class="img-fluid about-image">
            </div>
        </div>
    </div>
</section>
<?php
\App\Core\Layout::endSection();
\App\Core\Layout::end();
?>
