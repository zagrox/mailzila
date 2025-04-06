<?php
require_once __DIR__ . '/../../config/init.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL);
    exit;
}

$auth = new Auth();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($auth->login($email, $password)) {
            header('Location: ' . APP_URL);
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Login</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" class="mb-4">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>

                    <div class="text-center mb-4">
                        <div class="divider">
                            <span>or</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="<?php echo APP_URL; ?>/auth/google" class="btn btn-outline-danger">
                            <i class="fab fa-google me-2"></i> Login with Google
                        </a>
                        <a href="<?php echo APP_URL; ?>/auth/github" class="btn btn-outline-dark">
                            <i class="fab fa-github me-2"></i> Login with GitHub
                        </a>
                    </div>

                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register">Register</a></p>
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