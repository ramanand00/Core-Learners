<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's posts and friends' posts
$stmt = $conn->prepare("
    SELECT p.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as likes_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
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

// Get recommended courses
$stmt = $conn->prepare("
    SELECT c.*, u.username as instructor_name,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    WHERE c.id NOT IN (
        SELECT course_id FROM course_enrollments WHERE user_id = ?
    )
    ORDER BY enrolled_count DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recommended_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="home-container">
        <div class="home-sidebar">
            <div class="card">
                <h3>Quick Actions</h3>
                <ul class="quick-actions">
                    <li><a href="courses.php"><i class="fas fa-book"></i> Browse Courses</a></li>
                    <li><a href="notes.php"><i class="fas fa-sticky-note"></i> My Notes</a></li>
                    <li><a href="videos.php"><i class="fas fa-video"></i> Learning Videos</a></li>
                    <li><a href="friends.php"><i class="fas fa-users"></i> Find Friends</a></li>
                </ul>
            </div>

            <div class="card">
                <h3>Recommended Courses</h3>
                <div class="recommended-courses">
                    <?php foreach ($recommended_courses as $course): ?>
                        <div class="course-card">
                            <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                            <p>By <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                            <p><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> enrolled</p>
                            <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="home-main">
            <!-- Create Post Section -->
            <div class="card create-post">
                <form action="../includes/create_post.php" method="POST" enctype="multipart/form-data">
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
                            <img src="<?php echo $post['profile_picture'] ? '../assets/images/profile/' . $post['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
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
                                <i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?>
                            </button>
                            <button class="btn-comment" data-post-id="<?php echo $post['id']; ?>">
                                <i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Loading trigger for infinite scroll -->
            <div class="loading-trigger"></div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html> 