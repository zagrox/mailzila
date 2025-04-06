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

$campaignId = $_GET['id'] ?? null;
$campaign = null;
$stats = null;
$error = null;

if (!$campaignId) {
    $error = 'Campaign ID is required.';
} else {
    try {
        // Get campaign details
        $campaign = $api->getCampaign($campaignId);
        
        // Get campaign statistics
        $stats = $api->getCampaignStats($campaignId);
    } catch (Exception $e) {
        if (APP_DEBUG) {
            echo '<div class="alert alert-danger">';
            echo '<h5>Debug Information:</h5>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
            echo '</div>';
        } else {
            $error = 'Failed to load campaign statistics. Please try again.';
        }
    }
}

// Handle export request
if (isset($_GET['export']) && $stats) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="campaign_stats_' . $campaignId . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    fputcsv($output, ['Metric', 'Value']);
    
    // Write data
    fputcsv($output, ['Total Sent', $stats['recipientsCount'] ?? 0]);
    fputcsv($output, ['Opens', $stats['openedCount'] ?? 0]);
    fputcsv($output, ['Clicks', $stats['clickedCount'] ?? 0]);
    fputcsv($output, ['Bounces', $stats['bouncedCount'] ?? 0]);
    fputcsv($output, ['Unsubscribes', $stats['unsubscribedCount'] ?? 0]);
    fputcsv($output, ['Complaints', $stats['complainedCount'] ?? 0]);
    
    fclose($output);
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Campaign Statistics</h1>
                <div>
                    <?php if ($stats): ?>
                        <a href="?id=<?php echo $campaignId; ?>&export=1" class="btn btn-success me-2">
                            <i class="fas fa-download"></i> Export Report
                        </a>
                    <?php endif; ?>
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Campaigns
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
        </div>
    <?php elseif ($campaign && $stats): ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($campaign['name'] ?? 'Unnamed Campaign'); ?></h5>
                        <p class="text-muted mb-0">
                            Subject: <?php echo htmlspecialchars($campaign['subject'] ?? 'No Subject'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Key Metrics -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Sent</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['recipientsCount'] ?? 0); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Opens</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['openedCount'] ?? 0); ?></h2>
                        <small class="text-muted">
                            <?php echo number_format(($stats['openedCount'] ?? 0) / ($stats['recipientsCount'] ?? 1) * 100, 1); ?>% open rate
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Clicks</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['clickedCount'] ?? 0); ?></h2>
                        <small class="text-muted">
                            <?php echo number_format(($stats['clickedCount'] ?? 0) / ($stats['recipientsCount'] ?? 1) * 100, 1); ?>% click rate
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Bounces</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['bouncedCount'] ?? 0); ?></h2>
                        <small class="text-muted">
                            <?php echo number_format(($stats['bouncedCount'] ?? 0) / ($stats['recipientsCount'] ?? 1) * 100, 1); ?>% bounce rate
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Engagement Charts -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Opens Over Time</h5>
                        <canvas id="opensChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Clicks Over Time</h5>
                        <canvas id="clicksChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Clicked Links -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Clicked Links</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>URL</th>
                                        <th>Clicks</th>
                                        <th>Click Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats['clickedLinks'] ?? [] as $link): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($link['url'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($link['clicks'] ?? 0); ?></td>
                                            <td>
                                                <?php echo number_format(($link['clicks'] ?? 0) / ($stats['recipientsCount'] ?? 1) * 100, 1); ?>%
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
    <?php endif; ?>
</div>

<?php if ($stats): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Opens Chart
    new Chart(document.getElementById('opensChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($stats['opensByDate'] ?? [], 'date')); ?>,
            datasets: [{
                label: 'Opens',
                data: <?php echo json_encode(array_column($stats['opensByDate'] ?? [], 'count')); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Clicks Chart
    new Chart(document.getElementById('clicksChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($stats['clicksByDate'] ?? [], 'date')); ?>,
            datasets: [{
                label: 'Clicks',
                data: <?php echo json_encode(array_column($stats['clicksByDate'] ?? [], 'count')); ?>,
                borderColor: 'rgb(54, 162, 235)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 