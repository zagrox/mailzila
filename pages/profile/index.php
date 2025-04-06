<?php
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$error = null;
$success = null;

// Get current user data
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($firstName) || empty($lastName)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Update basic info
        if ($auth->updateProfile($currentUser['id'], $firstName, $lastName)) {
            $success = 'Profile updated successfully.';
            $currentUser = $auth->getCurrentUser(); // Refresh user data
        } else {
            $error = 'Failed to update profile.';
        }

        // Update password if provided
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $error = 'Please enter your current password to change it.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } elseif (strlen($newPassword) < 8) {
                $error = 'New password must be at least 8 characters long.';
            } else {
                if ($auth->updatePassword($currentUser['id'], $currentPassword, $newPassword)) {
                    $success = 'Profile and password updated successfully.';
                } else {
                    $error = 'Failed to update password. Please check your current password.';
                }
            }
        }
    }
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Profile Settings</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="post" class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled>
                            <div class="form-text">Email cannot be changed.</div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Change Password</h5>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>

                    <?php if ($currentUser['provider']): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This account is linked with <?php echo ucfirst($currentUser['provider']); ?>. 
                            Some profile information is managed through your <?php echo ucfirst($currentUser['provider']); ?> account.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 