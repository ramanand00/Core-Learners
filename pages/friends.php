<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's friends
$stmt = $conn->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as posts_count,
           (SELECT COUNT(*) FROM courses WHERE instructor_id = u.id) as courses_count
    FROM users u
    JOIN friends f ON (f.friend_id = u.id AND f.user_id = ?) OR (f.user_id = u.id AND f.friend_id = ?)
    WHERE f.status = 'accepted'
    ORDER BY u.username
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending friend requests
$stmt = $conn->prepare("
    SELECT u.*, f.id as request_id
    FROM users u
    JOIN friends f ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = 'pending'
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get suggested friends (users who are not friends and have common interests)
$stmt = $conn->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as posts_count,
           (SELECT COUNT(*) FROM courses WHERE instructor_id = u.id) as courses_count
    FROM users u
    WHERE u.id != ? 
    AND u.id NOT IN (
        SELECT CASE 
            WHEN user_id = ? THEN friend_id
            WHEN friend_id = ? THEN user_id
        END
        FROM friends
        WHERE (user_id = ? OR friend_id = ?)
    )
    AND EXISTS (
        SELECT 1 FROM user_interests ui1
        JOIN user_interests ui2 ON ui1.interest_id = ui2.interest_id
        WHERE ui1.user_id = ? AND ui2.user_id = u.id
    )
    ORDER BY RAND()
    LIMIT 10
");
$stmt->execute([
    $_SESSION['user_id'], 
    $_SESSION['user_id'], 
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id']
]);
$suggested_friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="friends-container">
        <!-- Pending Friend Requests -->
        <?php if (!empty($pending_requests)): ?>
            <div class="card">
                <h2>Friend Requests</h2>
                <div class="friend-requests">
                    <?php foreach ($pending_requests as $request): ?>
                        <div class="friend-request-item">
                            <div class="request-avatar">
                                <img src="<?php echo $request['profile_picture'] ? '../assets/images/profile/' . $request['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($request['username']); ?>">
                            </div>
                            <div class="request-info">
                                <h3><?php echo htmlspecialchars($request['username']); ?></h3>
                                <p><?php echo htmlspecialchars($request['bio'] ?? 'No bio yet'); ?></p>
                            </div>
                            <div class="request-actions">
                                <button class="btn btn-primary accept-request" data-request-id="<?php echo $request['request_id']; ?>">
                                    Accept
                                </button>
                                <button class="btn btn-secondary reject-request" data-request-id="<?php echo $request['request_id']; ?>">
                                    Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Friends List -->
        <div class="card">
            <h2>My Friends</h2>
            <?php if (empty($friends)): ?>
                <div class="no-friends">
                    <i class="fas fa-users"></i>
                    <p>You haven't added any friends yet</p>
                </div>
            <?php else: ?>
                <div class="friends-grid">
                    <?php foreach ($friends as $friend): ?>
                        <div class="friend-card">
                            <div class="friend-avatar">
                                <img src="<?php echo $friend['profile_picture'] ? '../assets/images/profile/' . $friend['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($friend['username']); ?>">
                            </div>
                            <div class="friend-info">
                                <h3><?php echo htmlspecialchars($friend['username']); ?></h3>
                                <p><?php echo htmlspecialchars($friend['bio'] ?? 'No bio yet'); ?></p>
                                <div class="friend-stats">
                                    <span><i class="fas fa-file-alt"></i> <?php echo $friend['posts_count']; ?> posts</span>
                                    <span><i class="fas fa-book"></i> <?php echo $friend['courses_count']; ?> courses</span>
                                </div>
                                <a href="profile.php?id=<?php echo $friend['id']; ?>" class="btn btn-primary">View Profile</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Suggested Friends -->
        <?php if (!empty($suggested_friends)): ?>
            <div class="card">
                <h2>Suggested Friends</h2>
                <div class="suggested-friends">
                    <?php foreach ($suggested_friends as $suggested): ?>
                        <div class="friend-card">
                            <div class="friend-avatar">
                                <img src="<?php echo $suggested['profile_picture'] ? '../assets/images/profile/' . $suggested['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($suggested['username']); ?>">
                            </div>
                            <div class="friend-info">
                                <h3><?php echo htmlspecialchars($suggested['username']); ?></h3>
                                <p><?php echo htmlspecialchars($suggested['bio'] ?? 'No bio yet'); ?></p>
                                <div class="friend-stats">
                                    <span><i class="fas fa-file-alt"></i> <?php echo $suggested['posts_count']; ?> posts</span>
                                    <span><i class="fas fa-book"></i> <?php echo $suggested['courses_count']; ?> courses</span>
                                </div>
                                <button class="btn btn-primary send-friend-request" data-user-id="<?php echo $suggested['id']; ?>">
                                    Add Friend
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle friend request actions
            document.querySelectorAll('.accept-request, .reject-request').forEach(button => {
                button.addEventListener('click', function() {
                    const requestId = this.dataset.requestId;
                    const action = this.classList.contains('accept-request') ? 'accept' : 'reject';
                    
                    fetch('../includes/handle_friend_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            request_id: requestId,
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('.friend-request-item').remove();
                        }
                    });
                });
            });

            // Handle sending friend requests
            document.querySelectorAll('.send-friend-request').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    
                    fetch('../includes/send_friend_request.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            user_id: userId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.textContent = 'Request Sent';
                            this.disabled = true;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html> 