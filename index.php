<?php
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: pages/login.php');
    exit();
}

// Get user's posts and friends' posts
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.profile_picture 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ? OR p.user_id IN (
        SELECT friend_id 
        FROM friends 
        WHERE user_id = ? AND status = 'accepted'
    )
    ORDER BY p.created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="home-container">
    <!-- Create Post Section -->
    <div class="card create-post">
        <form action="includes/create_post.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <textarea name="content" class="form-control" placeholder="Share your thoughts or upload content..."></textarea>
            </div>
            <div class="form-group file-upload">
                <label for="file" class="btn btn-secondary">
                    <i class="fas fa-paperclip"></i> Attach File
                </label>
                <input type="file" id="file" name="file" accept="image/*,video/*,.pdf" style="display: none;">
            </div>
            <button type="submit" class="btn btn-primary">Post</button>
        </form>
    </div>

    <!-- Posts Feed -->
    <div class="posts-container">
        <?php foreach ($posts as $post): ?>
            <div class="card post">
                <div class="post-header">
                    <img src="<?php echo $post['profile_picture'] ? 'assets/images/profile/' . $post['profile_picture'] : 'assets/images/default-profile.png'; ?>" 
                         alt="<?php echo htmlspecialchars($post['username']); ?>" 
                         class="profile-picture">
                    <div class="post-info">
                        <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                        <span class="post-date"><?php echo date('M d, Y H:i', strtotime($post['created_at'])); ?></span>
                    </div>
                </div>
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
                <?php if ($post['file_path']): ?>
                    <div class="post-file">
                        <?php
                        $fileType = pathinfo($post['file_path'], PATHINFO_EXTENSION);
                        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo '<img src="' . htmlspecialchars($post['file_path']) . '" alt="Post image" class="post-image">';
                        } elseif (in_array($fileType, ['mp4', 'webm'])) {
                            echo '<video controls class="post-video">
                                    <source src="' . htmlspecialchars($post['file_path']) . '" type="video/' . $fileType . '">
                                    Your browser does not support the video tag.
                                  </video>';
                        } elseif ($fileType === 'pdf') {
                            echo '<div class="pdf-preview">
                                    <i class="fas fa-file-pdf"></i>
                                    <a href="' . htmlspecialchars($post['file_path']) . '" target="_blank">View PDF</a>
                                  </div>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div class="post-actions">
                    <button class="btn-like" data-post-id="<?php echo $post['id']; ?>">
                        <i class="fas fa-heart"></i> Like
                    </button>
                    <button class="btn-comment" data-post-id="<?php echo $post['id']; ?>">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Loading trigger for infinite scroll -->
    <div class="loading-trigger"></div>
</div>

<?php require_once 'includes/footer.php'; ?> 