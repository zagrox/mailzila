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

$importedCount = 0;
$errors = [];

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
    $listId = $_POST['list_id'] ?? null;
    $file = $_FILES['csv_file'] ?? null;

    if (!$listId) {
        $errors[] = 'Please select a subscriber list.';
    }

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please upload a valid CSV file.';
    }

    if (empty($errors)) {
        try {
            // Read CSV file
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle !== false) {
                // Get headers
                $headers = fgetcsv($handle);
                if ($headers !== false) {
                    // Map headers to contact fields
                    $emailIndex = array_search('email', array_map('strtolower', $headers));
                    $firstNameIndex = array_search('first name', array_map('strtolower', $headers));
                    $lastNameIndex = array_search('last name', array_map('strtolower', $headers));

                    // Process each row
                    while (($row = fgetcsv($handle)) !== false) {
                        try {
                            $contact = [
                                'email' => $row[$emailIndex] ?? '',
                                'firstName' => $row[$firstNameIndex] ?? '',
                                'lastName' => $row[$lastNameIndex] ?? '',
                                'listIDs' => [$listId]
                            ];

                            // Add contact using API wrapper
                            $api->addContact($contact);
                            $importedCount++;
                        } catch (Exception $e) {
                            $errors[] = 'Error importing contact: ' . htmlspecialchars($e->getMessage());
                        }
                    }
                }
                fclose($handle);
            }
        } catch (Exception $e) {
            if (APP_DEBUG) {
                echo '<div class="alert alert-danger">';
                echo '<h5>Debug Information:</h5>';
                echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
                echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
                echo '</div>';
            } else {
                $errors[] = 'Failed to process the CSV file. Please try again.';
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Import Subscribers</h1>
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
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h5>Errors:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($importedCount > 0): ?>
                        <div class="alert alert-success">
                            Successfully imported <?php echo $importedCount; ?> subscribers.
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="list_id" class="form-label">Select List</label>
                            <select class="form-select" id="list_id" name="list_id" required>
                                <option value="">Choose a list</option>
                                <?php foreach ($lists as $list): ?>
                                    <option value="<?php echo $list['listID']; ?>">
                                        <?php echo htmlspecialchars($list['name'] ?? 'Unnamed List'); ?>
                                        <span class="badge bg-secondary ms-2"><?php echo number_format($list['contactCount'] ?? 0); ?> subscribers</span>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a list.</div>
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="invalid-feedback">Please upload a CSV file.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">CSV Format</label>
                            <pre class="bg-light p-3 rounded"><code>email,first name,last name
john@example.com,John,Doe
jane@example.com,Jane,Smith</code></pre>
                            <div class="form-text">
                                The CSV file should have headers and contain at least an email column.
                                First name and last name are optional.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import Subscribers
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