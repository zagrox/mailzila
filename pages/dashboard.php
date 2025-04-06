<?php
require_once __DIR__ . '/../includes/header.php';

// Get campaign statistics
try {
    // Debug: Print API key (first few characters only)
    if (APP_DEBUG) {
        echo "<!-- API Key: " . substr($_ENV['ELASTICEMAIL_API_KEY'], 0, 4) . "..." . " -->";
    }

    $campaigns = $api->getCampaigns();
    
    // Debug: Print raw API response
    if (APP_DEBUG) {
        echo "<!-- API Response: " . print_r($campaigns, true) . " -->";
    }

    $totalCampaigns = count($campaigns);
    $activeCampaigns = 0;
    $totalSent = 0;
    $totalOpens = 0;
    $totalClicks = 0;

    foreach ($campaigns as $campaign) {
        // Check if status exists and is Active
        if (isset($campaign['status']) && $campaign['status'] === 'Active') {
            $activeCampaigns++;
        }
        // Use null coalescing operator for safer array access
        $totalSent += $campaign['recipientsCount'] ?? 0;
        $totalOpens += $campaign['openedCount'] ?? 0;
        $totalClicks += $campaign['clickedCount'] ?? 0;
    }

    // Get recent campaigns
    $recentCampaigns = array_slice($campaigns, 0, 5);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    }
    $totalCampaigns = 0;
    $activeCampaigns = 0;
    $totalSent = 0;
    $totalOpens = 0;
    $totalClicks = 0;
    $recentCampaigns = [];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Dashboard</h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Campaigns</h5>
                    <h2 class="card-text"><?php echo number_format($totalCampaigns); ?></h2>
                    <p class="text-muted mb-0">Active: <?php echo number_format($activeCampaigns); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Sent</h5>
                    <h2 class="card-text"><?php echo number_format($totalSent); ?></h2>
                    <p class="text-muted mb-0">Emails delivered</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Opens</h5>
                    <h2 class="card-text"><?php echo number_format($totalOpens); ?></h2>
                    <p class="text-muted mb-0">Open rate: <?php echo $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 1) : 0; ?>%</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Clicks</h5>
                    <h2 class="card-text"><?php echo number_format($totalClicks); ?></h2>
                    <p class="text-muted mb-0">Click rate: <?php echo $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 1) : 0; ?>%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Campaigns</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentCampaigns)): ?>
                        <p class="text-muted">No campaigns found.</p>
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
                                    <?php foreach ($recentCampaigns as $campaign): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($campaign['name'] ?? 'Unnamed Campaign'); ?></td>
                                            <td><?php echo htmlspecialchars($campaign['subject'] ?? 'No Subject'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo (isset($campaign['status']) && $campaign['status'] === 'Active') ? 'success' : 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($campaign['status'] ?? 'Unknown'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($campaign['recipientsCount'] ?? 0); ?></td>
                                            <td><?php echo number_format($campaign['openedCount'] ?? 0); ?></td>
                                            <td><?php echo isset($campaign['dateCreated']) ? date('M j, Y', strtotime($campaign['dateCreated'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php if (isset($campaign['campaignID'])): ?>
                                                    <a href="campaigns/edit.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="campaigns/view.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="campaigns/stats.php?id=<?php echo $campaign['campaignID']; ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-chart-bar"></i>
                                                    </a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 