<?php
require_once __DIR__ . '/../../includes/header.php';

// Get campaigns from ElasticEmail API
try {
    $campaigns = $apiInstance->campaignsGet();
} catch (Exception $e) {
    $campaigns = [];
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Campaigns</h2>
    <a href="<?php echo APP_URL; ?>/pages/campaigns/create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create New Campaign
    </a>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent</th>
                                <th>Opens</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($campaign['name']); ?></td>
                                <td><?php echo htmlspecialchars($campaign['subject']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $campaign['status'] === 'Active' ? 'success' : 
                                            ($campaign['status'] === 'Draft' ? 'secondary' : 
                                            ($campaign['status'] === 'Sent' ? 'info' : 'danger')); 
                                    ?>">
                                        <?php echo htmlspecialchars($campaign['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo number_format($campaign['sent'] ?? 0); ?></td>
                                <td><?php echo number_format($campaign['opens'] ?? 0); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($campaign['created'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo APP_URL; ?>/pages/campaigns/edit.php?id=<?php echo $campaign['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/pages/campaigns/view.php?id=<?php echo $campaign['id']; ?>" 
                                           class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/pages/campaigns/stats.php?id=<?php echo $campaign['id']; ?>" 
                                           class="btn btn-sm btn-outline-success" title="Statistics">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger delete-campaign" 
                                                data-id="<?php echo $campaign['id']; ?>"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle campaign deletion
    document.querySelectorAll('.delete-campaign').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this campaign?')) {
                const campaignId = this.dataset.id;
                // Add AJAX call to delete campaign
                // For now, just reload the page
                window.location.reload();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 