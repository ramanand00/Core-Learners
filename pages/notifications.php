<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /Core-Learners/pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all notifications
$stmt = $conn->prepare("
    (SELECT 
        'friend_request' as type,
        f.created_at as date,
        u.username,
        u.profile_picture,
        NULL as title,
        NULL as content,
        NULL as link,
        f.id as reference_id
    FROM friends f
    JOIN users u ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = 'pending')
    
    UNION ALL
    
    (SELECT 
        'friend_suggestion' as type,
        u.created_at as date,
        u.username,
        u.profile_picture,
        NULL as title,
        NULL as content,
        NULL as link,
        u.id as reference_id
    FROM users u
    JOIN friends f1 ON (f1.user_id = u.id OR f1.friend_id = u.id)
    JOIN friends f2 ON (f2.user_id = f1.user_id OR f2.friend_id = f1.user_id)
    WHERE f1.status = 'accepted' 
    AND f2.status = 'accepted'
    AND u.id != ?
    AND u.id NOT IN (
        SELECT CASE 
            WHEN user_id = ? THEN friend_id
            WHEN friend_id = ? THEN user_id
        END
        FROM friends
        WHERE (user_id = ? OR friend_id = ?)
    )
    LIMIT 5)
    
    UNION ALL
    
    (SELECT 
        'new_video' as type,
        v.created_at as date,
        NULL as username,
        NULL as profile_picture,
        v.title,
        v.description as content,
        CONCAT('/Core-Learners/pages/video.php?id=', v.id) as link,
        v.id as reference_id
    FROM videos v
    WHERE v.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY v.created_at DESC
    LIMIT 5)
    
    UNION ALL
    
    (SELECT 
        'new_note' as type,
        n.created_at as date,
        u.username,
        u.profile_picture,
        n.title,
        n.content,
        CONCAT('/Core-Learners/pages/note.php?id=', n.id) as link,
        n.id as reference_id
    FROM notes n
    JOIN users u ON n.user_id = u.id
    WHERE n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY n.created_at DESC
    LIMIT 5)
    
    UNION ALL
    
    (SELECT 
        'new_course' as type,
        c.created_at as date,
        NULL as username,
        NULL as profile_picture,
        c.title,
        c.description as content,
        CONCAT('/Core-Learners/pages/course.php?id=', c.id) as link,
        c.id as reference_id
    FROM courses c
    WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY c.created_at DESC
    LIMIT 5)
    
    ORDER BY date DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Notifications</h2>
        
        <?php if (empty($notifications)): ?>
            <div class="card">
                <p class="text-center">No notifications</p>
            </div>
        <?php else: ?>
            <div class="notifications-list">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['type']; ?>">
                        <?php if ($notification['type'] === 'friend_request'): ?>
                            <div class="notification-content">
                                <img src="<?php echo $notification['profile_picture'] ? '/Core-Learners/assets/images/profile/' . $notification['profile_picture'] : '/Core-Learners/assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($notification['username']); ?>" 
                                     class="profile-picture">
                                <div class="notification-text">
                                    <p><strong><?php echo htmlspecialchars($notification['username']); ?></strong> sent you a friend request</p>
                                    <span class="notification-date"><?php echo date('M d, Y H:i', strtotime($notification['date'])); ?></span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-primary accept-friend" data-user-id="<?php echo $notification['reference_id']; ?>">
                                        Accept
                                    </button>
                                    <button class="btn btn-danger reject-friend" data-user-id="<?php echo $notification['reference_id']; ?>">
                                        Reject
                                    </button>
                                </div>
                            </div>
                        <?php elseif ($notification['type'] === 'friend_suggestion'): ?>
                            <div class="notification-content">
                                <img src="<?php echo $notification['profile_picture'] ? '/Core-Learners/assets/images/profile/' . $notification['profile_picture'] : '/Core-Learners/assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($notification['username']); ?>" 
                                     class="profile-picture">
                                <div class="notification-text">
                                    <p>Suggested friend: <strong><?php echo htmlspecialchars($notification['username']); ?></strong></p>
                                    <span class="notification-date"><?php echo date('M d, Y H:i', strtotime($notification['date'])); ?></span>
                                </div>
                                <button class="btn btn-primary send-friend-request" data-user-id="<?php echo $notification['reference_id']; ?>">
                                    Add Friend
                                </button>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="notification-content">
                                <div class="notification-icon">
                                    <?php
                                    switch ($notification['type']) {
                                        case 'new_video':
                                            echo '<i class="fas fa-video"></i>';
                                            break;
                                        case 'new_note':
                                            echo '<i class="fas fa-sticky-note"></i>';
                                            break;
                                        case 'new_course':
                                            echo '<i class="fas fa-graduation-cap"></i>';
                                            break;
                                    }
                                    ?>
                                </div>
                                <div class="notification-text">
                                    <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                    <p><?php echo htmlspecialchars(substr($notification['content'], 0, 100)) . '...'; ?></p>
                                    <span class="notification-date"><?php echo date('M d, Y H:i', strtotime($notification['date'])); ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
    .notifications-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .notification-item {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .notification-content {
        display: flex;
        align-items: center;
        padding: 1rem;
        gap: 1rem;
        text-decoration: none;
        color: inherit;
    }

    .notification-content:hover {
        background-color: #f8f9fa;
    }

    .profile-picture {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .notification-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #6c757d;
    }

    .notification-text {
        flex: 1;
    }

    .notification-text h4 {
        margin: 0;
        font-size: 1.1rem;
    }

    .notification-text p {
        margin: 0.5rem 0;
        color: #6c757d;
    }

    .notification-date {
        font-size: 0.9rem;
        color: #adb5bd;
    }

    .notification-actions {
        display: flex;
        gap: 0.5rem;
    }

    .friend_request .notification-content {
        background-color: #e3f2fd;
    }

    .friend_suggestion .notification-content {
        background-color: #f3e5f5;
    }

    .new_video .notification-content {
        background-color: #e8f5e9;
    }

    .new_note .notification-content {
        background-color: #fff3e0;
    }

    .new_course .notification-content {
        background-color: #e0f2f1;
    }

    @media (max-width: 768px) {
        .notification-content {
            flex-direction: column;
            text-align: center;
        }

        .notification-actions {
            width: 100%;
            justify-content: center;
        }
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle friend request acceptance
        document.querySelectorAll('.accept-friend').forEach(button => {
            button.addEventListener('click', async function() {
                const userId = this.dataset.userId;
                try {
                    const response = await fetch('/Core-Learners/includes/accept_friend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.closest('.notification-item').remove();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Handle friend request rejection
        document.querySelectorAll('.reject-friend').forEach(button => {
            button.addEventListener('click', async function() {
                const userId = this.dataset.userId;
                try {
                    const response = await fetch('/Core-Learners/includes/reject_friend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.closest('.notification-item').remove();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Handle sending friend requests
        document.querySelectorAll('.send-friend-request').forEach(button => {
            button.addEventListener('click', async function() {
                const userId = this.dataset.userId;
                try {
                    const response = await fetch('/Core-Learners/includes/send_friend_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.textContent = 'Request Sent';
                        this.disabled = true;
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });
    });
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html> 