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
    } else {
        echo '<div class="alert alert-danger">Failed to load templates. Please try again.</div>';
    }
    $templates = [];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Email Templates</h1>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Template
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($templates)): ?>
                        <p class="text-muted">No templates found. Create your first template to get started.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Subject</th>
                                        <th>Created</th>
                                        <th>Last Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($templates as $template): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($template['name'] ?? 'Unnamed Template'); ?></td>
                                            <td><?php echo htmlspecialchars($template['subject'] ?? 'No Subject'); ?></td>
                                            <td><?php echo isset($template['dateCreated']) ? date('M j, Y', strtotime($template['dateCreated'])) : 'N/A'; ?></td>
                                            <td><?php echo isset($template['dateModified']) ? date('M j, Y', strtotime($template['dateModified'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if (isset($template['templateID'])): ?>
                                                    <div class="btn-group">
                                                        <a href="edit.php?id=<?php echo $template['templateID']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="preview.php?id=<?php echo $template['templateID']; ?>" class="btn btn-sm btn-outline-info" title="Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-template" data-id="<?php echo $template['templateID']; ?>" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle template deletion
    document.querySelectorAll('.delete-template').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this template?')) {
                const templateId = this.dataset.id;
                // TODO: Implement template deletion
                console.log('Delete template:', templateId);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 