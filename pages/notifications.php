<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's notifications
$stmt = $conn->prepare("
    SELECT n.*, u.username, u.profile_picture,
           CASE 
               WHEN n.type = 'friend_request' THEN 'sent you a friend request'
               WHEN n.type = 'friend_accept' THEN 'accepted your friend request'
               WHEN n.type = 'course_enroll' THEN 'enrolled in your course'
               WHEN n.type = 'course_comment' THEN 'commented on your course'
               WHEN n.type = 'post_like' THEN 'liked your post'
               WHEN n.type = 'post_comment' THEN 'commented on your post'
               ELSE 'interacted with your content'
           END as action_text
    FROM notifications n
    JOIN users u ON n.from_user_id = u.id
    WHERE n.to_user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 50
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark notifications as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE to_user_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="notifications-container">
        <div class="card">
            <h2>Notifications</h2>
            <?php if (empty($notifications)): ?>
                <div class="no-notifications">
                    <i class="fas fa-bell-slash"></i>
                    <p>No notifications yet</p>
                </div>
            <?php else: ?>
                <div class="notifications-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                            <div class="notification-avatar">
                                <img src="<?php echo $notification['profile_picture'] ? '../assets/images/profile/' . $notification['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($notification['username']); ?>">
                            </div>
                            <div class="notification-content">
                                <p>
                                    <strong><?php echo htmlspecialchars($notification['username']); ?></strong>
                                    <?php echo $notification['action_text']; ?>
                                </p>
                                <span class="notification-time">
                                    <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                </span>
                            </div>
                            <?php if ($notification['type'] === 'friend_request' && !$notification['is_read']): ?>
                                <div class="notification-actions">
                                    <button class="btn btn-primary accept-friend" data-notification-id="<?php echo $notification['id']; ?>">
                                        Accept
                                    </button>
                                    <button class="btn btn-secondary reject-friend" data-notification-id="<?php echo $notification['id']; ?>">
                                        Reject
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle friend request actions
            document.querySelectorAll('.accept-friend').forEach(button => {
                button.addEventListener('click', function() {
                    const notificationId = this.dataset.notificationId;
                    fetch('../includes/handle_friend_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            notification_id: notificationId,
                            action: 'accept'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.notification-item').remove();
                        }
                    });
                });
            });

            document.querySelectorAll('.reject-friend').forEach(button => {
                button.addEventListener('click', function() {
                    const notificationId = this.dataset.notificationId;
                    fetch('../includes/handle_friend_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            notification_id: notificationId,
                            action: 'reject'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.notification-item').remove();
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 