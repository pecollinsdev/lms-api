<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Register - LMS</title>
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <!-- Custom CSS -->
  <link href="/lms-frontend/public/css/style.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet"
  >
</head>
<body class="bg-light">
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/lms-frontend/public/">LMS</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="btn btn-outline-primary ms-2" href="/lms-frontend/public/auth/login">Login</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Registration Section -->
  <section class="registration-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header bg-white text-center py-4">
              <h3 class="fw-bold mb-0">Create Account</h3>
            </div>
            <div class="card-body p-4">
              <!-- Role Selection -->
              <div class="text-center mb-4">
                <div class="btn-group" role="group">
                  <input
                    type="radio"
                    class="btn-check"
                    name="role"
                    id="student"
                    value="student"
                    checked
                  >
                  <label class="btn btn-outline-primary" for="student">
                    <i class="fas fa-user-graduate me-2"></i>Student
                  </label>
                  <input
                    type="radio"
                    class="btn-check"
                    name="role"
                    id="instructor"
                    value="instructor"
                  >
                  <label class="btn btn-outline-primary" for="instructor">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Instructor
                  </label>
                </div>
              </div>

              <form
                method="POST"
                action="/lms-frontend/public/auth/register"
                id="registrationForm"
                novalidate
              >
                <input
                  type="hidden"
                  name="role"
                  id="roleInput"
                  value="student"
                >

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input
                      type="text"
                      class="form-control"
                      name="first_name"
                      required
                    >
                    <!-- no helper text here -->
                    <div class="feedback-container">
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input
                      type="text"
                      class="form-control"
                      name="last_name"
                      required
                    >
                    <div class="feedback-container">
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Email Address</label>
                  <input
                    type="email"
                    class="form-control"
                    name="email"
                    required
                  >
                  <div class="feedback-container">
                    <div class="invalid-feedback"></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Phone Number</label>
                  <input
                    type="tel"
                    class="form-control"
                    name="phone_number"
                    pattern="[0-9]{10,15}"
                  >
                  <div class="feedback-container">
                    <div class="form-text">
                      Enter a valid phone number (10-15 digits)
                    </div>
                    <div class="invalid-feedback"></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Password</label>
                  <div class="input-group">
                    <input
                      type="password"
                      class="form-control"
                      name="password"
                      id="password"
                      required
                    >
                    <button
                      class="btn btn-outline-secondary"
                      type="button"
                      id="togglePassword"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                  <div class="feedback-container">
                    <div class="form-text">
                      Password must be at least 8 characters long
                    </div>
                    <div class="invalid-feedback"></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Confirm Password</label>
                  <input
                    type="password"
                    class="form-control"
                    name="password_confirmation"
                    required
                  >
                  <div class="feedback-container">
                    <div class="invalid-feedback"></div>
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Bio</label>
                  <textarea
                    class="form-control"
                    name="bio"
                    rows="3"
                    maxlength="500"
                  ></textarea>
                  <div class="feedback-container">
                    <div class="form-text">
                      Tell us a bit about yourself (max 500 characters)
                    </div>
                    <div class="invalid-feedback"></div>
                  </div>
                </div>

                <!-- Instructor‐only fields -->
                <div id="instructorFields" style="display: none;">
                  <div class="mb-3">
                    <label class="form-label">Instructor Code</label>
                    <input
                      type="text"
                      class="form-control"
                      name="instructor_code"
                    >
                    <div class="feedback-container">
                      <div class="form-text">
                        Please enter the instructor registration code provided by your administrator
                      </div>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Academic Specialty</label>
                    <input
                      type="text"
                      class="form-control"
                      name="academic_specialty"
                    >
                    <div class="feedback-container">
                      <div class="form-text">Your field of expertise</div>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label">Qualifications</label>
                    <textarea
                      class="form-control"
                      name="qualifications"
                      rows="3"
                    ></textarea>
                    <div class="feedback-container">
                      <div class="form-text">
                        List your qualifications and certifications
                      </div>
                      <div class="invalid-feedback"></div>
                    </div>
                  </div>
                </div>

                <?php if (!empty($error)): ?>
                  <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                  </div>
                <?php endif; ?>

                <div class="d-grid gap-2 mt-4">
                  <button type="submit" class="btn btn-primary btn-lg">
                    Create Account
                  </button>
                </div>
              </form>
            </div>
            <div class="card-footer bg-white text-center py-3">
              <div class="small">
                Already have an account?
                <a href="/lms-frontend/public/auth/login" class="text-primary">
                  Sign in!
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bootstrap JS bundle -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <!-- Custom JS -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Role toggle
    const studentRadio    = document.getElementById('student');
    const instructorRadio = document.getElementById('instructor');
    const instructorFields= document.getElementById('instructorFields');
    const roleInput       = document.getElementById('roleInput');

    function toggleInstructorFields() {
      if (instructorRadio.checked) {
        instructorFields.style.display = 'block';
        roleInput.value = 'instructor';
        instructorFields.querySelectorAll('input, textarea')
          .forEach(i => i.required = true);
      } else {
        instructorFields.style.display = 'none';
        roleInput.value = 'student';
        instructorFields.querySelectorAll('input, textarea')
          .forEach(i => i.required = false);
      }
    }
    studentRadio.addEventListener('change', toggleInstructorFields);
    instructorRadio.addEventListener('change', toggleInstructorFields);

    // Password toggle
    const togglePassword = document.getElementById('togglePassword');
    const password       = document.getElementById('password');
    togglePassword.addEventListener('click', () => {
      const type = password.type === 'password' ? 'text' : 'password';
      password.type = type;
      togglePassword.querySelector('i')
        .classList.toggle('fa-eye-slash');
    });
  });
  </script>
  <script src="/lms-frontend/public/js/formValidator.js"></script>
</body>
</html>