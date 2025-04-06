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
    // Get campaigns
    $campaigns = $api->getCampaigns();
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">Failed to load campaigns. Please try again.</div>';
    }
    $campaigns = [];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Campaigns</h1>
                <a href="create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Campaign
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($campaigns)): ?>
                        <p class="text-muted">No campaigns found. Create your first campaign to get started.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
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
                                            <td><?php echo htmlspecialchars($campaign['name'] ?? 'Unnamed Campaign'); ?></td>
                                            <td><?php echo htmlspecialchars($campaign['subject'] ?? 'No Subject'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo isset($campaign['status']) && $campaign['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($campaign['status'] ?? 'Unknown'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($campaign['recipientsCount'] ?? 0); ?></td>
                                            <td><?php echo number_format($campaign['openedCount'] ?? 0); ?></td>
                                            <td><?php echo isset($campaign['dateCreated']) ? date('M j, Y', strtotime($campaign['dateCreated'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if (isset($campaign['campaignID'])): ?>
                                                    <div class="btn-group">
                                                        <a href="edit.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="view.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="stats.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-success" title="Statistics">
                                                            <i class="fas fa-chart-bar"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-campaign" data-id="<?php echo $campaign['campaignID']; ?>" title="Delete">
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
    // Handle campaign deletion
    document.querySelectorAll('.delete-campaign').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this campaign?')) {
                const campaignId = this.dataset.id;
                // TODO: Implement campaign deletion
                console.log('Delete campaign:', campaignId);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 