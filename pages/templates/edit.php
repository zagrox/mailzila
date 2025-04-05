<?php
require_once __DIR__ . '/../../includes/header.php';

$templateId = $_GET['id'] ?? null;

if (!$templateId) {
    header('Location: ' . APP_URL . '/pages/templates/list.php');
    exit;
}

try {
    $templatesApi = new \ElasticEmail\Api\TemplatesApi(
        new GuzzleHttp\Client(),
        $config
    );
    $template = $templatesApi->templatesGetById($templateId);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
    header('Location: ' . APP_URL . '/pages/templates/list.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $templateData = [
            'name' => $_POST['name'],
            'subject' => $_POST['subject'],
            'bodyHtml' => $_POST['content'],
            'bodyText' => strip_tags($_POST['content'])
        ];

        $result = $templatesApi->templatesPut($templateId, $templateData);
        
        if ($result) {
            header('Location: ' . APP_URL . '/pages/templates/list.php');
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
            <h2 class="mb-4">Edit Template</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Template Details</h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($template['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Default Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   value="<?php echo htmlspecialchars($template['subject']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Template Content</label>
                            <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($template['bodyHtml']); ?></textarea>
                            <div class="form-text">
                                You can use HTML formatting. Available variables: {name}, {email}, {unsubscribe}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="<?php echo APP_URL; ?>/pages/templates/list.php" class="btn btn-secondary">
                        Cancel
                    </a>
                    <div>
                        <a href="<?php echo APP_URL; ?>/pages/templates/preview.php?id=<?php echo $templateId; ?>" 
                           class="btn btn-info me-2" target="_blank">
                            Preview
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
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