<?php
require_once __DIR__ . '/../../includes/header.php';

// Check if API is available
if (!$api) {
    echo '<div class="alert alert-danger">';
    echo '<h5>Error:</h5>';
    echo '<p>Unable to connect to the email service. Please check your API configuration.</p>';
    echo '</div>';
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$success = false;
$error = null;

try {
    // Get subscriber lists
    $lists = $api->getLists();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    }
    $lists = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $listId = $_POST['list_id'] ?? null;

    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($listId)) {
        $error = 'Please select a subscriber list.';
    }

    if (!$error) {
        try {
            $contact = [
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'listIDs' => [$listId]
            ];

            // Add contact using API wrapper
            $api->addContact($contact);
            $success = true;
        } catch (Exception $e) {
            if (APP_DEBUG) {
                echo '<div class="alert alert-danger">';
                echo '<h5>Debug Information:</h5>';
                echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
                echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
                echo '</div>';
            } else {
                $error = 'Failed to add subscriber. Please try again.';
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Add Subscriber</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Subscriber added successfully!
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name">
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name">
                        </div>

                        <div class="mb-3">
                            <label for="list_id" class="form-label">Subscriber List</label>
                            <select class="form-select" id="list_id" name="list_id" required>
                                <option value="">Select a list</option>
                                <?php foreach ($lists as $list): ?>
                                    <option value="<?php echo $list['listID']; ?>">
                                        <?php echo htmlspecialchars($list['name'] ?? 'Unnamed List'); ?>
                                        <span class="badge bg-secondary ms-2"><?php echo number_format($list['contactCount'] ?? 0); ?> subscribers</span>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a list.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Subscriber
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 