<?php
require_once __DIR__ . '/../../includes/header.php';

// Get subscribers lists from ElasticEmail API
try {
    $listsApi = new \ElasticEmail\Api\ListsApi(
        new GuzzleHttp\Client(),
        $config
    );
    $lists = $listsApi->listsGet();
} catch (Exception $e) {
    $lists = [];
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
}

$imported = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $listId = $_POST['list_id'];
        
        // Read CSV file
        if (($handle = fopen($file, "r")) !== FALSE) {
            // Get headers
            $headers = fgetcsv($handle);
            $headerCount = count($headers);
            
            // Map CSV columns to contact fields
            $mapping = [
                'email' => array_search('email', $headers) !== false ? array_search('email', $headers) : null,
                'first_name' => array_search('first_name', $headers) !== false ? array_search('first_name', $headers) : null,
                'last_name' => array_search('last_name', $headers) !== false ? array_search('last_name', $headers) : null
            ];
            
            // Process rows
            $contactsApi = new \ElasticEmail\Api\ContactsApi(
                new GuzzleHttp\Client(),
                $config
            );
            
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) === $headerCount) {
                    try {
                        $contactData = [
                            'email' => $row[$mapping['email']],
                            'firstName' => $mapping['first_name'] !== null ? $row[$mapping['first_name']] : null,
                            'lastName' => $mapping['last_name'] !== null ? $row[$mapping['last_name']] : null,
                            'lists' => [$listId]
                        ];
                        
                        $result = $contactsApi->contactsPost($contactData);
                        if ($result) {
                            $imported++;
                        }
                    } catch (Exception $e) {
                        $errors[] = "Error importing {$row[$mapping['email']]}: " . $e->getMessage();
                    }
                }
            }
            
            fclose($handle);
        }
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Import Subscribers</h2>
            
            <?php if ($imported > 0): ?>
                <div class="alert alert-success">
                    Successfully imported <?php echo $imported; ?> subscribers.
                    <?php if (!empty($errors)): ?>
                        <br>There were <?php echo count($errors); ?> errors during import.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5>Import Errors:</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Import from CSV</h5>
                    <p class="text-muted">
                        Upload a CSV file with the following columns: email, first_name (optional), last_name (optional)
                    </p>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="list_id" class="form-label">Subscriber List</label>
                            <select class="form-select" id="list_id" name="list_id" required>
                                <option value="">Select a list</option>
                                <?php foreach ($lists as $list): ?>
                                    <option value="<?php echo $list['id']; ?>">
                                        <?php echo htmlspecialchars($list['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <div class="form-text">
                                The CSV file should have headers: email, first_name (optional), last_name (optional)
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?php echo APP_URL; ?>/pages/subscribers/list.php" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Import Subscribers
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Sample CSV Format</h5>
                    <pre class="bg-light p-3 rounded">
email,first_name,last_name
john@example.com,John,Doe
jane@example.com,Jane,Smith
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 