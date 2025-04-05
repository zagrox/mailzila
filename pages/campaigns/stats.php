<?php
require_once __DIR__ . '/../../includes/header.php';

$campaignId = $_GET['id'] ?? null;

if (!$campaignId) {
    header('Location: ' . APP_URL . '/pages/campaigns/list.php');
    exit;
}

try {
    // Get campaign details
    $campaign = $apiInstance->campaignsGetById($campaignId);
    
    // Get campaign statistics
    $statsApi = new \ElasticEmail\Api\StatisticsApi(
        new GuzzleHttp\Client(),
        $config
    );
    $stats = $statsApi->statisticsCampaignsGet($campaignId);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
    header('Location: ' . APP_URL . '/pages/campaigns/list.php');
    exit;
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Campaign Statistics</h2>
                <a href="<?php echo APP_URL; ?>/pages/campaigns/list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Campaigns
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($campaign['name']); ?></h5>
                    <p class="text-muted">Subject: <?php echo htmlspecialchars($campaign['subject']); ?></p>
                    <p>Sent: <?php echo date('Y-m-d H:i', strtotime($campaign['created'])); ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Total Sent</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['sent'] ?? 0); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Opens</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['opens'] ?? 0); ?></h2>
                            <small>
                                <?php 
                                $openRate = ($stats['sent'] > 0) ? 
                                    round(($stats['opens'] / $stats['sent']) * 100, 1) : 0;
                                echo "({$openRate}% open rate)";
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Clicks</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['clicks'] ?? 0); ?></h2>
                            <small>
                                <?php 
                                $clickRate = ($stats['opens'] > 0) ? 
                                    round(($stats['clicks'] / $stats['opens']) * 100, 1) : 0;
                                echo "({$clickRate}% click rate)";
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Unsubscribes</h6>
                            <h2 class="mb-0"><?php echo number_format($stats['unsubscribes'] ?? 0); ?></h2>
                            <small>
                                <?php 
                                $unsubRate = ($stats['sent'] > 0) ? 
                                    round(($stats['unsubscribes'] / $stats['sent']) * 100, 1) : 0;
                                echo "({$unsubRate}% unsubscribe rate)";
                                ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
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
                                        <td><?php echo htmlspecialchars($link['url']); ?></td>
                                        <td><?php echo number_format($link['clicks']); ?></td>
                                        <td>
                                            <?php 
                                            $linkClickRate = ($stats['clicks'] > 0) ? 
                                                round(($link['clicks'] / $stats['clicks']) * 100, 1) : 0;
                                            echo "{$linkClickRate}%";
                                            ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data for charts
const opensData = <?php echo json_encode($stats['opensByDate'] ?? []); ?>;
const clicksData = <?php echo json_encode($stats['clicksByDate'] ?? []); ?>;

// Opens Chart
new Chart(document.getElementById('opensChart'), {
    type: 'line',
    data: {
        labels: opensData.map(d => d.date),
        datasets: [{
            label: 'Opens',
            data: opensData.map(d => d.count),
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
        labels: clicksData.map(d => d.date),
        datasets: [{
            label: 'Clicks',
            data: clicksData.map(d => d.count),
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
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 