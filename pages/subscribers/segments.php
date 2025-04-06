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

$listId = $_GET['list_id'] ?? null;
$segments = [];
$error = null;

try {
    // Get segments for the selected list
    $segments = $api->getContactSegments($listId);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    } else {
        $error = 'Failed to load segments. Please try again.';
    }
}

// Handle segment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'create':
                $api->createSegment([
                    'name' => $_POST['name'],
                    'listId' => $listId,
                    'rules' => [
                        [
                            'type' => $_POST['rule_type'],
                            'value' => $_POST['rule_value']
                        ]
                    ]
                ]);
                header('Location: segments.php?list_id=' . $listId);
                exit;
                break;
            case 'delete':
                $api->deleteSegment($_POST['segment_id']);
                header('Location: segments.php?list_id=' . $listId);
                exit;
                break;
        }
    } catch (Exception $e) {
        $error = 'Failed to perform action. Please try again.';
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Subscriber Segments</h1>
                <div>
                    <a href="list.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Subscribers
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSegmentModal">
                        <i class="fas fa-plus"></i> Create Segment
                    </button>
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
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($segments)): ?>
                        <p class="text-muted">No segments found. Create your first segment to get started.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Rules</th>
                                        <th>Subscribers</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($segments as $segment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($segment['name'] ?? 'Unnamed Segment'); ?></td>
                                            <td>
                                                <?php foreach ($segment['rules'] ?? [] as $rule): ?>
                                                    <span class="badge bg-info me-1">
                                                        <?php echo htmlspecialchars($rule['type'] . ': ' . $rule['value']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td><?php echo number_format($segment['contactCount'] ?? 0); ?></td>
                                            <td><?php echo isset($segment['dateCreated']) ? date('M j, Y', strtotime($segment['dateCreated'])) : 'N/A'; ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-segment" 
                                                            data-id="<?php echo $segment['segmentID']; ?>"
                                                            data-name="<?php echo htmlspecialchars($segment['name']); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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

<!-- Create Segment Modal -->
<div class="modal fade" id="createSegmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Segment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="name" class="form-label">Segment Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="rule_type" class="form-label">Rule Type</label>
                        <select class="form-select" id="rule_type" name="rule_type" required>
                            <option value="email">Email Contains</option>
                            <option value="name">Name Contains</option>
                            <option value="status">Status</option>
                            <option value="date">Date Added</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rule_value" class="form-label">Rule Value</label>
                        <input type="text" class="form-control" id="rule_value" name="rule_value" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Segment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Segment Modal -->
<div class="modal fade" id="deleteSegmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Segment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the segment "<span id="deleteSegmentName"></span>"?</p>
            </div>
            <div class="modal-footer">
                <form method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="segment_id" id="deleteSegmentId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Segment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle segment deletion
    document.querySelectorAll('.delete-segment').forEach(button => {
        button.addEventListener('click', function() {
            const segmentId = this.dataset.id;
            const segmentName = this.dataset.name;
            
            document.getElementById('deleteSegmentId').value = segmentId;
            document.getElementById('deleteSegmentName').textContent = segmentName;
            
            new bootstrap.Modal(document.getElementById('deleteSegmentModal')).show();
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 