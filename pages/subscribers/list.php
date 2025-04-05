<?php
require_once __DIR__ . '/../../includes/header.php';

// Get subscribers lists from ElasticEmail API
try {
    $listsApi = new \ElasticEmail\Api\ListsApi(
        new GuzzleHttp\Client(),
        $config
    );
    $lists = $listsApi->listsGet();
} catch (Exception $e) {
    $lists = [];
    if (APP_DEBUG) {
        echo "Error: " . $e->getMessage();
    }
}

// Get selected list's subscribers
$selectedListId = $_GET['list_id'] ?? null;
$subscribers = [];

if ($selectedListId) {
    try {
        $contactsApi = new \ElasticEmail\Api\ContactsApi(
            new GuzzleHttp\Client(),
            $config
        );
        $subscribers = $contactsApi->contactsByListGet($selectedListId);
    } catch (Exception $e) {
        if (APP_DEBUG) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Subscribers</h2>
                <div>
                    <a href="<?php echo APP_URL; ?>/pages/subscribers/import.php" class="btn btn-info me-2">
                        <i class="fas fa-file-import"></i> Import Subscribers
                    </a>
                    <a href="<?php echo APP_URL; ?>/pages/subscribers/create.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Subscriber
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="list_id" class="form-label">Select List</label>
                            <select class="form-select" id="list_id" name="list_id" onchange="this.form.submit()">
                                <option value="">Choose a list</option>
                                <?php foreach ($lists as $list): ?>
                                    <option value="<?php echo $list['id']; ?>" <?php echo $selectedListId == $list['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($list['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($selectedListId): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Subscribers List</h5>
                            <span class="badge bg-primary">
                                <?php echo count($subscribers); ?> subscribers
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Email</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Status</th>
                                        <th>Added Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                            <td><?php echo htmlspecialchars($subscriber['firstName'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($subscriber['lastName'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $subscriber['status'] === 'Active' ? 'success' : 'danger'; ?>">
                                                    <?php echo htmlspecialchars($subscriber['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($subscriber['dateAdded'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="<?php echo APP_URL; ?>/pages/subscribers/edit.php?id=<?php echo $subscriber['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="deleteSubscriber('<?php echo $subscriber['id']; ?>')"
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
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Please select a list to view subscribers.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteSubscriber(id) {
    if (confirm('Are you sure you want to delete this subscriber?')) {
        // TODO: Implement AJAX call to delete subscriber
        console.log('Delete subscriber:', id);
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 