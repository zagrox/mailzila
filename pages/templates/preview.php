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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?php echo htmlspecialchars($template['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .preview-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .preview-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .preview-content {
            margin-bottom: 20px;
        }
        .preview-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="preview-container">
        <div class="preview-header">
            <h2><?php echo htmlspecialchars($template['name']); ?></h2>
            <p class="text-muted">Subject: <?php echo htmlspecialchars($template['subject']); ?></p>
        </div>

        <div class="preview-content">
            <?php echo $template['bodyHtml']; ?>
        </div>

        <div class="preview-footer">
            <p>This is a preview of your email template. The actual email may look different depending on the email client.</p>
            <p>Last modified: <?php echo date('Y-m-d H:i', strtotime($template['dateModified'])); ?></p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="<?php echo APP_URL; ?>/pages/templates/list.php" class="btn btn-secondary">
            Back to Templates
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 