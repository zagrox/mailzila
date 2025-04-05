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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $contactData = [
            'email' => $_POST['email'],
            'firstName' => $_POST['first_name'],
            'lastName' => $_POST['last_name'],
            'lists' => [$_POST['list_id']]
        ];

        $contactsApi = new \ElasticEmail\Api\ContactsApi(
            new GuzzleHttp\Client(),
            $config
        );
        
        $result = $contactsApi->contactsPost($contactData);
        
        if ($result) {
            header('Location: ' . APP_URL . '/pages/subscribers/list.php?list_id=' . $_POST['list_id']);
            exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Add New Subscriber</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Subscriber Details</h5>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name">
                                </div>
                            </div>
                        </div>

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
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?php echo APP_URL; ?>/pages/subscribers/list.php" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Add Subscriber
                    </button>
                </div>
            </form>
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