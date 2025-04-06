<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/login.php');
}

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM notifications WHERE user_id = ?";
$totalResult = $db->select($countSql, [$_SESSION['user_id']]);
$total = $totalResult[0]['total'];
$totalPages = ceil($total / $perPage);

// Get paginated notifications
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$notifications = $db->select($sql, [$_SESSION['user_id'], $perPage, $offset]);

// Update notifications to mark as read
$updateSql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
$db->query($updateSql, [$_SESSION['user_id']]);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Notifications</h2>
                    <p class="text-muted mb-0"><?php echo $total; ?> total notifications</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light" onclick="refreshNotifications()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Mark all as read
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="notification-list-container">
                    <ul class="notification-list" id="notificationList">
                        <?php if (empty($notifications)): ?>
                            <li class="notification-item text-center py-4">
                                <div class="empty-state">
                                    <i class="fas fa-bell fa-2x mb-3 text-muted"></i>
                                    <p class="text-muted mb-0">No notifications yet</p>
                                </div>
                            </li>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <li class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                                    data-id="<?php echo $notification['id']; ?>">
                                    <div class="notification-icon">
                                        <i class="<?php echo getNotificationIcon($notification['type']); ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-title">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </div>
                                        <div class="notification-message">
                                            <?php echo htmlspecialchars($notification['message']); ?>
                                        </div>
                                        <div class="notification-time">
                                            <?php echo timeAgo($notification['created_at']); ?>
                                        </div>
                                    </div>
                                    <button class="btn btn-link text-muted delete-notification" 
                                            onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav aria-label="Notifications pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function markAllAsRead() {
    const button = event.target.closest('button');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Marking...';

    fetch('<?php echo APP_URL; ?>/api/notifications/mark-all-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo csrf_token(); ?>'
        },
        body: JSON.stringify({
            user_id: <?php echo $_SESSION['user_id']; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            const badges = document.querySelectorAll('.notification-badge');
            badges.forEach(badge => badge.style.display = 'none');
        }
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-check-double"></i> Mark all as read';
    });
}

function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) return;

    const item = document.querySelector(`[data-id="${id}"]`);
    item.style.opacity = '0.5';

    fetch('<?php echo APP_URL; ?>/api/notifications/delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo csrf_token(); ?>'
        },
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            item.style.height = item.offsetHeight + 'px';
            setTimeout(() => {
                item.style.height = '0';
                item.style.padding = '0';
                item.style.margin = '0';
                setTimeout(() => item.remove(), 300);
            }, 100);
        }
    })
    .catch(() => {
        item.style.opacity = '1';
        alert('Failed to delete notification');
    });
}

function refreshNotifications() {
    const button = event.target.closest('button');
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    window.location.reload();
}

// Add smooth scroll for pagination
document.querySelectorAll('.pagination .page-link').forEach(link => {
    link.addEventListener('click', (e) => {
        if (!link.parentElement.classList.contains('disabled')) {
            document.querySelector('.notification-list-container').style.opacity = '0.5';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

function getNotificationIcon($type) {
    switch ($type) {
        case 'campaign':
            return 'fas fa-paper-plane';
        case 'subscriber':
            return 'fas fa-user';
        case 'system':
            return 'fas fa-cog';
        default:
            return 'fas fa-bell';
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?> 