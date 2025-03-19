<?php
session_start();
require_once '../config/database.php';

// Fetch videos from database
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($searchQuery)) {
    $stmt = $conn->prepare("
        SELECT v.*, u.username, u.profile_picture,
               (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) AS likes_count,
               (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) AS comments_count
        FROM videos v
        JOIN users u ON v.user_id = u.id
        WHERE v.title LIKE ? OR v.description LIKE ?
        ORDER BY v.created_at DESC
    ");
    $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
} else {
    $stmt = $conn->prepare("
        SELECT v.*, u.username, u.profile_picture,
               (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) AS likes_count,
               (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) AS comments_count
        FROM videos v
        JOIN users u ON v.user_id = u.id
        ORDER BY v.created_at DESC
    ");
    $stmt->execute();
}

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Videos - VideoHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="container">
        <h2>Find Videos</h2>

        <!-- Search Bar -->
        <form method="GET" action="videos.php" class="search-form">
            <input type="text" name="search" placeholder="Search videos..." value="<?php echo htmlspecialchars($searchQuery); ?>" required>
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>

        <!-- Video Grid -->
        <div class="videos-grid">
            <?php if (empty($videos)): ?>
                <p>No videos found.</p>
            <?php else: ?>
                <?php foreach ($videos as $video): ?>
                    <div class="video-card">
                        <a href="video-player.php?id=<?php echo $video['id']; ?>">
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
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
