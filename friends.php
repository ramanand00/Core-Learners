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

// Handle friend request actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_request'])) {
        $request_id = $_POST['request_id'];
        try {
            $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$request_id]);
            $success_message = "Friend request accepted!";
        } catch (PDOException $e) {
            $error_message = "Error accepting friend request: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['reject_request'])) {
        $request_id = $_POST['request_id'];
        try {
            $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$request_id]);
            $success_message = "Friend request rejected.";
        } catch (PDOException $e) {
            $error_message = "Error rejecting friend request: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['send_request'])) {
        $friend_id = $_POST['friend_id'];
        try {
            $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user['id'], $friend_id]);
            $success_message = "Friend request sent!";
        } catch (PDOException $e) {
            $error_message = "Error sending friend request: " . $e->getMessage();
        }
    }
}

// Get friend requests
try {
    $stmt = $pdo->prepare("
        SELECT fr.*, u.name, u.email, u.avatar 
        FROM friend_requests fr 
        JOIN users u ON fr.sender_id = u.id 
        WHERE fr.receiver_id = ? AND fr.status = 'pending'
    ");
    $stmt->execute([$user['id']]);
    $friend_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching friend requests: " . $e->getMessage();
}

// Get friends list
try {
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM users u 
        JOIN friend_requests fr ON (fr.sender_id = u.id OR fr.receiver_id = u.id)
        WHERE (fr.sender_id = ? OR fr.receiver_id = ?) 
        AND fr.status = 'accepted' 
        AND u.id != ?
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id']]);
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching friends: " . $e->getMessage();
}

// Get friend suggestions
try {
    $stmt = $pdo->prepare("
        SELECT u.* 
        FROM users u 
        WHERE u.id NOT IN (
            SELECT CASE 
                WHEN fr.sender_id = ? THEN fr.receiver_id 
                ELSE fr.sender_id 
            END
            FROM friend_requests fr 
            WHERE fr.sender_id = ? OR fr.receiver_id = ?
        )
        AND u.id != ?
        LIMIT 10
    ");
    $stmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
    $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching friend suggestions: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Core Learners</title>
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
            <!-- Friend Requests -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Friend Requests</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($friend_requests)): ?>
                            <p>No pending friend requests</p>
                        <?php else: ?>
                            <?php foreach ($friend_requests as $request): ?>
                                <div class="friend-request-item">
                                    <img src="<?php echo $request['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                         alt="Profile Picture" 
                                         class="friend-avatar">
                                    <div class="friend-info">
                                        <h6><?php echo htmlspecialchars($request['name']); ?></h6>
                                        <p><?php echo htmlspecialchars($request['email']); ?></p>
                                    </div>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <button type="submit" name="accept_request" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="submit" name="reject_request" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Friends List -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>My Friends</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($friends)): ?>
                            <p>No friends yet</p>
                        <?php else: ?>
                            <div class="friends-grid">
                                <?php foreach ($friends as $friend): ?>
                                    <div class="friend-card">
                                        <img src="<?php echo $friend['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                             alt="Profile Picture" 
                                             class="friend-avatar">
                                        <h6><?php echo htmlspecialchars($friend['name']); ?></h6>
                                        <p><?php echo htmlspecialchars($friend['email']); ?></p>
                                        <div class="friend-actions">
                                            <a href="profile.php?id=<?php echo $friend['id']; ?>" class="btn btn-primary btn-sm">
                                                View Profile
                                            </a>
                                            <a href="messages.php?user=<?php echo $friend['id']; ?>" class="btn btn-secondary btn-sm">
                                                Message
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Friend Suggestions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Suggested Friends</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($suggestions)): ?>
                            <p>No friend suggestions available</p>
                        <?php else: ?>
                            <?php foreach ($suggestions as $suggestion): ?>
                                <div class="friend-suggestion-item">
                                    <img src="<?php echo $suggestion['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                         alt="Profile Picture" 
                                         class="friend-avatar">
                                    <div class="friend-info">
                                        <h6><?php echo htmlspecialchars($suggestion['name']); ?></h6>
                                        <p><?php echo htmlspecialchars($suggestion['email']); ?></p>
                                    </div>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="friend_id" value="<?php echo $suggestion['id']; ?>">
                                        <button type="submit" name="send_request" class="btn btn-primary btn-sm">
                                            Add Friend
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Friend Search -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>Find Friends</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>

                <?php if (isset($_GET['search'])): ?>
                    <?php
                    $search = $_GET['search'];
                    try {
                        $stmt = $pdo->prepare("
                            SELECT * FROM users 
                            WHERE (name LIKE ? OR email LIKE ?) 
                            AND id != ?
                        ");
                        $search_term = "%$search%";
                        $stmt->execute([$search_term, $search_term, $user['id']]);
                        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $error_message = "Error searching users: " . $e->getMessage();
                    }
                    ?>

                    <?php if (!empty($search_results)): ?>
                        <div class="search-results">
                            <?php foreach ($search_results as $result): ?>
                                <div class="search-result-item">
                                    <img src="<?php echo $result['avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                                         alt="Profile Picture" 
                                         class="friend-avatar">
                                    <div class="friend-info">
                                        <h6><?php echo htmlspecialchars($result['name']); ?></h6>
                                        <p><?php echo htmlspecialchars($result['email']); ?></p>
                                    </div>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="friend_id" value="<?php echo $result['id']; ?>">
                                        <button type="submit" name="send_request" class="btn btn-primary btn-sm">
                                            Add Friend
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No users found matching your search.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 