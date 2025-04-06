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
$subscribers = [];
$lists = [];
$error = null;

try {
    // Get all lists
    $lists = $api->getLists();
    
    // Get subscribers for the selected list
    if ($listId) {
        $subscribers = $api->getContacts($listId);
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<div class="alert alert-danger">';
        echo '<h5>Debug Information:</h5>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p>Line: ' . htmlspecialchars($e->getLine()) . '</p>';
        echo '</div>';
    } else {
        $error = 'Failed to load subscribers. Please try again.';
    }
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['subscriber_ids'])) {
    try {
        switch ($_POST['action']) {
            case 'delete':
                $api->bulkDeleteContacts($_POST['subscriber_ids']);
                header('Location: list.php?list_id=' . $listId);
                exit;
                break;
            case 'move':
                $api->bulkMoveContacts($_POST['subscriber_ids'], $_POST['target_list_id']);
                header('Location: list.php?list_id=' . $listId);
                exit;
                break;
        }
    } catch (Exception $e) {
        $error = 'Failed to perform bulk action. Please try again.';
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Subscribers</h1>
                <div>
                    <a href="segments.php?list_id=<?php echo $listId; ?>" class="btn btn-info me-2">
                        <i class="fas fa-filter"></i> Manage Segments
                    </a>
                    <a href="import.php" class="btn btn-success me-2">
                        <i class="fas fa-file-import"></i> Import Subscribers
                    </a>
                    <a href="add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Subscriber
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
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <select name="list_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select a List</option>
                                    <?php foreach ($lists as $list): ?>
                                        <option value="<?php echo $list['listID']; ?>" <?php echo $listId == $list['listID'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($list['name']); ?> (<?php echo number_format($list['contactCount'] ?? 0); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search subscribers..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Search</button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($subscribers)): ?>
                        <p class="text-muted">No subscribers found. Import subscribers or add them manually to get started.</p>
                    <?php else: ?>
                        <form method="post" id="bulkActionForm">
                            <div class="mb-3">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Delete Selected
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#moveModal">
                                        <i class="fas fa-exchange-alt"></i> Move to List
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Score</th>
                                            <th>Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscribers as $subscriber): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="subscriber_ids[]" value="<?php echo $subscriber['contactID']; ?>" class="form-check-input subscriber-checkbox">
                                                </td>
                                                <td><?php echo htmlspecialchars($subscriber['email'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($subscriber['firstName'] ?? '') . ' ' . htmlspecialchars($subscriber['lastName'] ?? ''); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo isset($subscriber['status']) && $subscriber['status'] === 'Active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo htmlspecialchars($subscriber['status'] ?? 'Unknown'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    try {
                                                        $score = $api->getContactScore($subscriber['contactID']);
                                                        echo '<span class="badge bg-' . ($score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger')) . '">';
                                                        echo number_format($score);
                                                        echo '</span>';
                                                    } catch (Exception $e) {
                                                        echo '<span class="badge bg-secondary">N/A</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo isset($subscriber['dateAdded']) ? date('M j, Y', strtotime($subscriber['dateAdded'])) : 'N/A'; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="edit.php?id=<?php echo $subscriber['contactID']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="activity.php?id=<?php echo $subscriber['contactID']; ?>" class="btn btn-sm btn-outline-info" title="Activity History">
                                                            <i class="fas fa-history"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-subscriber" 
                                                                data-id="<?php echo $subscriber['contactID']; ?>"
                                                                data-email="<?php echo htmlspecialchars($subscriber['email']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Subscribers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the selected subscribers?</p>
            </div>
            <div class="modal-footer">
                <form method="post">
                    <input type="hidden" name="action" value="delete">
                    <div id="deleteSubscriberIds"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Move Modal -->
<div class="modal fade" id="moveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Move Subscribers</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="move">
                    <div id="moveSubscriberIds"></div>
                    <div class="mb-3">
                        <label for="target_list_id" class="form-label">Target List</label>
                        <select class="form-select" id="target_list_id" name="target_list_id" required>
                            <option value="">Select a List</option>
                            <?php foreach ($lists as $list): ?>
                                <?php if ($list['listID'] != $listId): ?>
                                    <option value="<?php echo $list['listID']; ?>">
                                        <?php echo htmlspecialchars($list['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Move</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.subscriber-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Handle bulk delete
    document.getElementById('deleteModal').addEventListener('show.bs.modal', function() {
        const selectedIds = Array.from(document.querySelectorAll('.subscriber-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        const container = document.getElementById('deleteSubscriberIds');
        container.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'subscriber_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    });

    // Handle bulk move
    document.getElementById('moveModal').addEventListener('show.bs.modal', function() {
        const selectedIds = Array.from(document.querySelectorAll('.subscriber-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        const container = document.getElementById('moveSubscriberIds');
        container.innerHTML = '';
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'subscriber_ids[]';
            input.value = id;
            container.appendChild(input);
        });
    });

    // Handle individual subscriber deletion
    document.querySelectorAll('.delete-subscriber').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this subscriber?')) {
                const subscriberId = this.dataset.id;
                const form = document.createElement('form');
                form.method = 'post';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="subscriber_ids[]" value="${subscriberId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 