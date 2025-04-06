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

try {
    // Get templates
    $templates = $api->getTemplates();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    }
    $templates = [];
}

try {
    // Get lists
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
    try {
        $campaignData = [
            'name' => $_POST['name'],
            'subject' => $_POST['subject'],
            'templateId' => $_POST['template_id'],
            'listId' => $_POST['list_id'],
            'fromEmail' => $_POST['from_email'],
            'fromName' => $_POST['from_name'],
            'replyTo' => $_POST['reply_to'],
            'status' => 'Draft'
        ];

        $result = $api->createCampaign($campaignData);
        
        if ($result) {
            header('Location: list.php');
            exit;
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
            echo '<div class="alert alert-danger">Failed to create campaign. Please try again.</div>';
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Create Campaign</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Campaigns
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Campaign Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Email Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="template_id" class="form-label">Template</label>
                                    <select class="form-select" id="template_id" name="template_id" required>
                                        <option value="">Select a template</option>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo $template['templateID']; ?>">
                                                <?php echo htmlspecialchars($template['name'] ?? 'Unnamed Template'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="list_id" class="form-label">Subscriber List</label>
                                    <select class="form-select" id="list_id" name="list_id" required>
                                        <option value="">Select a list</option>
                                        <?php foreach ($lists as $list): ?>
                                            <option value="<?php echo $list['listID']; ?>">
                                                <?php echo htmlspecialchars($list['name'] ?? 'Unnamed List'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
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
                            <label for="reply_to" class="form-label">Reply-To Email</label>
                            <input type="email" class="form-control" id="reply_to" name="reply_to" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 