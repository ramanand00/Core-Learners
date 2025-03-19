<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's uploaded videos
$stmt = $conn->prepare("
    SELECT v.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) as likes_count,
           (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) as comments_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.user_id = ?
    ORDER BY v.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended videos
$stmt = $conn->prepare("
    SELECT v.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) as likes_count,
           (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) as comments_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.user_id != ?
    AND v.id NOT IN (
        SELECT video_id FROM video_views WHERE user_id = ?
    )
    ORDER BY likes_count DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recommended_videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recently viewed videos
$stmt = $conn->prepare("
    SELECT v.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) as likes_count,
           (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) as comments_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    JOIN video_views vv ON v.id = vv.video_id
    WHERE vv.user_id = ?
    ORDER BY vv.viewed_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recently_viewed = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="videos-container">
        <!-- Upload Video Section -->
        <div class="card">
            <h2>Upload Video</h2>
            <form action="../includes/upload_video.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="video">Video File</label>
                    <input type="file" id="video" name="video" class="form-control" accept="video/*" required>
                </div>
                <div class="form-group">
                    <label for="thumbnail">Thumbnail (optional)</label>
                    <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Upload Video</button>
            </form>
        </div>

        <!-- My Videos Section -->
        <div class="card">
            <h2>My Videos</h2>
            <?php if (empty($my_videos)): ?>
                <div class="no-videos">
                    <i class="fas fa-video-slash"></i>
                    <p>You haven't uploaded any videos yet</p>
                </div>
            <?php else: ?>
                <div class="videos-grid">
                    <?php foreach ($my_videos as $video): ?>
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <img src="<?php echo $video['thumbnail_path'] ? '../assets/images/thumbnails/' . $video['thumbnail_path'] : '../assets/images/default-thumbnail.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>">
                            </div>
                            <div class="video-info">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p><?php echo htmlspecialchars($video['description']); ?></p>
                                <div class="video-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $video['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $video['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $video['comments_count']; ?></span>
                                </div>
                                <div class="video-actions">
                                    <a href="video-player.php?id=<?php echo $video['id']; ?>" class="btn btn-primary">Watch</a>
                                    <button class="btn btn-secondary edit-video" data-video-id="<?php echo $video['id']; ?>">Edit</button>
                                    <button class="btn btn-danger delete-video" data-video-id="<?php echo $video['id']; ?>">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recently Viewed Videos -->
        <?php if (!empty($recently_viewed)): ?>
            <div class="card">
                <h2>Recently Viewed</h2>
                <div class="videos-grid">
                    <?php foreach ($recently_viewed as $video): ?>
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <img src="<?php echo $video['thumbnail_path'] ? '../assets/images/thumbnails/' . $video['thumbnail_path'] : '../assets/images/default-thumbnail.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>">
                            </div>
                            <div class="video-info">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p>By <?php echo htmlspecialchars($video['username']); ?></p>
                                <div class="video-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $video['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $video['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $video['comments_count']; ?></span>
                                </div>
                                <a href="video-player.php?id=<?php echo $video['id']; ?>" class="btn btn-primary">Watch Again</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recommended Videos -->
        <?php if (!empty($recommended_videos)): ?>
            <div class="card">
                <h2>Recommended Videos</h2>
                <div class="videos-grid">
                    <?php foreach ($recommended_videos as $video): ?>
                        <div class="video-card">
                            <div class="video-thumbnail">
                                <img src="<?php echo $video['thumbnail_path'] ? '../assets/images/thumbnails/' . $video['thumbnail_path'] : '../assets/images/default-thumbnail.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($video['title']); ?>">
                            </div>
                            <div class="video-info">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p>By <?php echo htmlspecialchars($video['username']); ?></p>
                                <div class="video-stats">
                                    <span><i class="fas fa-eye"></i> <?php echo $video['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $video['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $video['comments_count']; ?></span>
                                </div>
                                <a href="video-player.php?id=<?php echo $video['id']; ?>" class="btn btn-primary">Watch</a>
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
            // Handle video deletion
            document.querySelectorAll('.delete-video').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this video?')) {
                        const videoId = this.dataset.videoId;
                        fetch('../includes/delete_video.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                video_id: videoId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.video-card').remove();
                            }
                        });
                    }
                });
            });

            // Handle video editing
            document.querySelectorAll('.edit-video').forEach(button => {
                button.addEventListener('click', function() {
                    const videoId = this.dataset.videoId;
                    window.location.href = `edit-video.php?id=${videoId}`;
                });
            });
        });
    </script>
</body>
</html> 