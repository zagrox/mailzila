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

$subscriberId = $_GET['id'] ?? null;
$subscriber = null;
$activity = [];
$error = null;

if (!$subscriberId) {
    $error = 'Subscriber ID is required.';
} else {
    try {
        // Get subscriber details
        $subscriber = $api->getContact($subscriberId);
        
        // Get subscriber activity
        $activity = $api->getContactActivity($subscriberId);
    } catch (Exception $e) {
        if (APP_DEBUG) {
            echo '<div class="alert alert-danger">';
            echo '<h5>Debug Information:</h5>';
            echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
            echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
            echo '</div>';
        } else {
            $error = 'Failed to load subscriber activity. Please try again.';
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Subscriber Activity</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Subscribers
                </a>
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
    <?php elseif ($subscriber): ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title"><?php echo htmlspecialchars($subscriber['email'] ?? 'N/A'); ?></h5>
                                <p class="text-muted mb-0">
                                    <?php echo htmlspecialchars($subscriber['firstName'] ?? '') . ' ' . htmlspecialchars($subscriber['lastName'] ?? ''); ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="badge bg-<?php echo isset($subscriber['status']) && $subscriber['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                    <?php echo htmlspecialchars($subscriber['status'] ?? 'Unknown'); ?>
                                </span>
                                <?php
                                try {
                                    $score = $api->getContactScore($subscriberId);
                                    echo '<span class="badge bg-' . ($score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger')) . ' ms-2">';
                                    echo 'Score: ' . number_format($score);
                                    echo '</span>';
                                } catch (Exception $e) {
                                    echo '<span class="badge bg-secondary ms-2">Score: N/A</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Activity History</h5>
                        <?php if (empty($activity)): ?>
                            <p class="text-muted">No activity found for this subscriber.</p>
                        <?php else: ?>
                            <div class="timeline">
                                <?php foreach ($activity as $event): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-<?php
                                            echo match($event['type'] ?? '') {
                                                'open' => 'success',
                                                'click' => 'primary',
                                                'unsubscribe' => 'danger',
                                                'bounce' => 'warning',
                                                default => 'secondary'
                                            };
                                        ?>"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">
                                                <?php
                                                echo match($event['type'] ?? '') {
                                                    'open' => 'Opened Email',
                                                    'click' => 'Clicked Link',
                                                    'unsubscribe' => 'Unsubscribed',
                                                    'bounce' => 'Email Bounced',
                                                    default => 'Other Activity'
                                                };
                                                ?>
                                            </h6>
                                            <p class="text-muted mb-0">
                                                <?php if (isset($event['campaignName'])): ?>
                                                    Campaign: <?php echo htmlspecialchars($event['campaignName']); ?><br>
                                                <?php endif; ?>
                                                <?php if (isset($event['url'])): ?>
                                                    URL: <?php echo htmlspecialchars($event['url']); ?><br>
                                                <?php endif; ?>
                                                <?php if (isset($event['date'])): ?>
                                                    Date: <?php echo date('M j, Y g:i A', strtotime($event['date'])); ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 9px;
    top: 20px;
    bottom: -30px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 