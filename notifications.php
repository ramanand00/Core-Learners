<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$success_message = '';
$error_message = '';

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mark_read'])) {
        $notification_id = $_POST['notification_id'];
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user['id']]);
            $success_message = "Notification marked as read!";
        } catch (PDOException $e) {
            $error_message = "Error marking notification as read: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['mark_all_read'])) {
        try {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $success_message = "All notifications marked as read!";
        } catch (PDOException $e) {
            $error_message = "Error marking all notifications as read: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_notification'])) {
        $notification_id = $_POST['notification_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user['id']]);
            $success_message = "Notification deleted!";
        } catch (PDOException $e) {
            $error_message = "Error deleting notification: " . $e->getMessage();
        }
    }
}

// Get notifications
try {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching notifications: " . $e->getMessage();
}

// Get unread count
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->execute([$user['id']]);
    $unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (PDOException $e) {
    $error_message = "Error fetching unread count: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Core Learners</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Notifications</h5>
                        <?php if ($unread_count > 0): ?>
                            <form method="POST" class="d-inline">
                                <button type="submit" name="mark_all_read" class="btn btn-primary btn-sm">
                                    Mark All as Read
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="text-center">No notifications</p>
                        <?php else: ?>
                            <div class="notifications-list">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                        <div class="notification-icon">
                                            <?php
                                            $icon = 'fas fa-bell';
                                            switch ($notification['type']) {
                                                case 'friend_request':
                                                    $icon = 'fas fa-user-plus';
                                                    break;
                                                case 'course_update':
                                                    $icon = 'fas fa-book';
                                                    break;
                                                case 'message':
                                                    $icon = 'fas fa-envelope';
                                                    break;
                                                case 'achievement':
                                                    $icon = 'fas fa-trophy';
                                                    break;
                                                case 'system':
                                                    $icon = 'fas fa-info-circle';
                                                    break;
                                            }
                                            ?>
                                            <i class="<?php echo $icon; ?>"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="notification-actions">
                                            <?php if (!$notification['is_read']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" name="mark_read" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" name="delete_notification" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Real-time notifications using WebSocket
        const ws = new WebSocket('ws://' + window.location.hostname + ':8080');
        
        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.type === 'notification') {
                // Reload page to show new notification
                window.location.reload();
            }
        };
        
        ws.onclose = function() {
            // Attempt to reconnect after 5 seconds
            setTimeout(function() {
                window.location.reload();
            }, 5000);
        };
    </script>
</body>
</html> 