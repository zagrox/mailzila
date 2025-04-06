<?php
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';

    if (empty($email) || empty($password) || empty($confirmPassword) || empty($firstName) || empty($lastName)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        if ($auth->register($email, $password, $firstName, $lastName)) {
            header('Location: /auth/login?registered=1');
            exit;
        } else {
            $error = 'Registration failed. Email might already be registered.';
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Register</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>

                    <div class="text-center mb-4">
                        <div class="divider">
                            <span>or</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="/auth/google" class="btn btn-outline-danger">
                            <i class="fab fa-google me-2"></i> Register with Google
                        </a>
                        <a href="/auth/github" class="btn btn-outline-dark">
                            <i class="fab fa-github me-2"></i> Register with GitHub
                        </a>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Already have an account? <a href="/auth/login">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.divider {
    display: flex;
    align-items: center;
    text-align: center;
    color: #6c757d;
    margin: 1rem 0;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
}

.divider span {
    padding: 0 1rem;
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 