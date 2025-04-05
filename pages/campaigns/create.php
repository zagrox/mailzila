<?php
require_once __DIR__ . '/../../includes/header.php';

// Get templates from ElasticEmail API
try {
    $templatesApi = new \ElasticEmail\Api\TemplatesApi(
        new GuzzleHttp\Client(),
        $config
    );
    $templates = $templatesApi->templatesGet();
} catch (Exception $e) {
    $templates = [];
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
}

// Get subscribers lists
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
        $campaignData = [
            'name' => $_POST['name'],
            'subject' => $_POST['subject'],
            'content' => $_POST['content'],
            'from' => $_POST['from_email'],
            'from_name' => $_POST['from_name'],
            'template_id' => $_POST['template_id'] ?: null,
            'list_id' => $_POST['list_id'],
            'scheduled_at' => $_POST['schedule_date'] ? date('Y-m-d H:i:s', strtotime($_POST['schedule_date'])) : null
        ];

        $result = $apiInstance->campaignsPost($campaignData);
        
        if ($result) {
            header('Location: ' . APP_URL . '/pages/campaigns/list.php');
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
            <h2 class="mb-4">Create New Campaign</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Campaign Details</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Email Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_email" class="form-label">From Email</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="from_name" class="form-label">From Name</label>
                                    <input type="text" class="form-control" id="from_name" name="from_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="template_id" class="form-label">Template</label>
                            <select class="form-select" id="template_id" name="template_id">
                                <option value="">No Template</option>
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?php echo $template['id']; ?>">
                                        <?php echo htmlspecialchars($template['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
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

                        <div class="mb-3">
                            <label for="content" class="form-label">Email Content</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="schedule_date" class="form-label">Schedule Date (Optional)</label>
                            <input type="datetime-local" class="form-control" id="schedule_date" name="schedule_date">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?php echo APP_URL; ?>/pages/campaigns/list.php" class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Create Campaign
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