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
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Email Templates</h2>
                <a href="<?php echo APP_URL; ?>/pages/templates/create.php" class="btn btn-primary">
                    Create Template
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Created Date</th>
                                    <th>Last Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($template['name']); ?></td>
                                        <td><?php echo htmlspecialchars($template['subject']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($template['dateAdded'])); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($template['dateModified'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="<?php echo APP_URL; ?>/pages/templates/edit.php?id=<?php echo $template['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    Edit
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/pages/templates/preview.php?id=<?php echo $template['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info"
                                                   target="_blank">
                                                    Preview
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteTemplate('<?php echo $template['id']; ?>')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        // TODO: Implement AJAX call to delete template
        console.log('Delete template:', id);
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 