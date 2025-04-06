<?php
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$error = null;
$success = null;

// Get current user data
$currentUser = $auth->getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $campaignNotifications = isset($_POST['campaign_notifications']) ? 1 : 0;
    $subscriberNotifications = isset($_POST['subscriber_notifications']) ? 1 : 0;
    $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
    $timezone = $_POST['timezone'] ?? 'UTC';

    try {
        // Update user settings
        $db = Database::getInstance();
        $db->query(
            "UPDATE users SET 
                email_notifications = ?,
                campaign_notifications = ?,
                subscriber_notifications = ?,
                dark_mode = ?,
                timezone = ?
            WHERE id = ?",
            [
                $emailNotifications,
                $campaignNotifications,
                $subscriberNotifications,
                $darkMode,
                $timezone,
                $currentUser['id']
            ]
        );
        $success = 'Settings updated successfully.';
        $currentUser = $auth->getCurrentUser(); // Refresh user data
    } catch (PDOException $e) {
        $error = 'Failed to update settings.';
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log("Settings update error: " . $e->getMessage());
        }
    }
}

// Get available timezones
$timezones = DateTimeZone::listIdentifiers();

// Set default values if settings don't exist yet
$currentUser['email_notifications'] = $currentUser['email_notifications'] ?? 1;
$currentUser['campaign_notifications'] = $currentUser['campaign_notifications'] ?? 1;
$currentUser['subscriber_notifications'] = $currentUser['subscriber_notifications'] ?? 1;
$currentUser['dark_mode'] = $currentUser['dark_mode'] ?? 0;
$currentUser['timezone'] = $currentUser['timezone'] ?? 'UTC';
?>

<!-- Add dark mode styles -->
<style>
[data-theme="dark"] {
    --bg-color: #1a1a1a;
    --text-color: #ffffff;
    --card-bg: #2d2d2d;
    --border-color: #404040;
    --input-bg: #333333;
    --input-text: #ffffff;
    --input-border: #404040;
}

[data-theme="dark"] body {
    background-color: var(--bg-color);
    color: var(--text-color);
}

[data-theme="dark"] .card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
}

[data-theme="dark"] .form-control,
[data-theme="dark"] .form-select {
    background-color: var(--input-bg);
    border-color: var(--input-border);
    color: var(--input-text);
}

[data-theme="dark"] .form-check-input {
    background-color: var(--input-bg);
    border-color: var(--input-border);
}

[data-theme="dark"] .form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

[data-theme="dark"] .alert-info {
    background-color: #1c3a4a;
    border-color: #1c4f6e;
    color: #9ccef4;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Application Settings</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="post" class="mb-4">
                        <h5 class="mb-3">Email Notifications</h5>
                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="email_notifications" 
                                       name="email_notifications" <?php echo $currentUser['email_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="email_notifications">
                                    Receive email notifications
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" id="campaign_notifications" 
                                       name="campaign_notifications" <?php echo $currentUser['campaign_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="campaign_notifications">
                                    Get notified about campaign status changes
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="subscriber_notifications" 
                                       name="subscriber_notifications" <?php echo $currentUser['subscriber_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="subscriber_notifications">
                                    Get notified about subscriber activity
                                </label>
                            </div>
                        </div>

                        <h5 class="mb-3">Display Settings</h5>
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="dark_mode" 
                                       name="dark_mode" <?php echo $currentUser['dark_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="dark_mode">
                                    Enable dark mode
                                </label>
                            </div>
                        </div>

                        <h5 class="mb-3">Time Zone</h5>
                        <div class="mb-4">
                            <div class="form-group">
                                <select class="form-select" id="timezone" name="timezone">
                                    <?php foreach ($timezones as $tz): ?>
                                        <option value="<?php echo htmlspecialchars($tz); ?>" 
                                                <?php echo $currentUser['timezone'] === $tz ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tz); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>

                    <?php if ($currentUser['provider']): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Some settings may be managed through your <?php echo ucfirst($currentUser['provider']); ?> account.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add dark mode JavaScript -->
<script>
// Function to set dark mode
function setDarkMode(enabled) {
    if (enabled) {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
}

// Initialize dark mode based on user preference
setDarkMode(<?php echo $currentUser['dark_mode'] ? 'true' : 'false'; ?>);

// Handle dark mode toggle
document.getElementById('dark_mode').addEventListener('change', function(e) {
    setDarkMode(e.target.checked);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 