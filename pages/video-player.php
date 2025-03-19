<?php
session_start();
require_once '../config/database.php';

// Check if video ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid video ID.");
}

$videoId = $_GET['id'];

// Fetch video details
$stmt = $conn->prepare("
    SELECT v.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM video_likes WHERE video_id = v.id) AS likes_count,
           (SELECT COUNT(*) FROM video_comments WHERE video_id = v.id) AS comments_count
    FROM videos v
    JOIN users u ON v.user_id = u.id
    WHERE v.id = ?
");
$stmt->execute([$videoId]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    die("Video not found.");
}

// Increment video views
$conn->prepare("UPDATE videos SET views_count = views_count + 1 WHERE id = ?")->execute([$videoId]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?> - VideoHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="video-container">
        <h2><?php echo htmlspecialchars($video['title']); ?></h2>
        <video controls>
            <source src="../assets/videos/<?php echo $video['video_path']; ?>" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <p>Uploaded by: <?php echo htmlspecialchars($video['username']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>

        <div class="video-stats">
            <span><i class="fas fa-eye"></i> <?php echo $video['views_count']; ?></span>
            <span><i class="fas fa-heart"></i> <?php echo $video['likes_count']; ?></span>
            <span><i class="fas fa-comment"></i> <?php echo $video['comments_count']; ?></span>
        </div>

        <a href="videos.php" class="btn btn-secondary">Back to Videos</a>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html>
