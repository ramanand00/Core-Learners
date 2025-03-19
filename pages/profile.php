<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user profile data
$stmt = $conn->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM friends WHERE user_id = u.id AND status = 'accepted') as friend_count,
           (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $address = $_POST['address'] ?? '';
    $study_skills = $_POST['study_skills'] ?? '';

    // Handle profile picture upload
    $profile_picture = $user['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = '../assets/images/profile/' . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if ($profile_picture && file_exists('../assets/images/profile/' . $profile_picture)) {
                    unlink('../assets/images/profile/' . $profile_picture);
                }
                $profile_picture = $new_filename;
            }
        }
    }

    // Update user profile
    $stmt = $conn->prepare("
        UPDATE users 
        SET full_name = ?, bio = ?, address = ?, study_skills = ?, profile_picture = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$full_name, $bio, $address, $study_skills, $profile_picture, $_SESSION['user_id']])) {
        $_SESSION['profile_picture'] = $profile_picture;
        $success = 'Profile updated successfully!';
        // Refresh user data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = 'Failed to update profile. Please try again.';
    }
}

// Get user's posts
$stmt = $conn->prepare("
    SELECT p.*, 
           COUNT(DISTINCT pl.id) as like_count,
           COUNT(DISTINCT pc.id) as comment_count
    FROM posts p
    LEFT JOIN post_likes pl ON p.id = pl.post_id
    LEFT JOIN post_comments pc ON p.id = pc.post_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-picture-container">
                <img src="<?php echo $user['profile_picture'] ? '../assets/images/profile/' . $user['profile_picture'] : '../assets/images/default-profile.png'; ?>" 
                     alt="Profile Picture" 
                     class="profile-picture">
                <form method="POST" enctype="multipart/form-data" class="profile-picture-form">
                    <label for="profile_picture" class="btn btn-secondary">
                        <i class="fas fa-camera"></i> Change Picture
                    </label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                </form>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="profile-stats">
                    <span><i class="fas fa-users"></i> <?php echo $user['friend_count']; ?> Friends</span>
                    <span><i class="fas fa-file-alt"></i> <?php echo $user['post_count']; ?> Posts</span>
                </p>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-section">
                <h2>Profile Information</h2>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" class="form-control" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="study_skills">Study Skills</label>
                        <textarea id="study_skills" name="study_skills" class="form-control" rows="3"><?php echo htmlspecialchars($user['study_skills'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>My Posts</h2>
                <div class="posts-container">
                    <?php foreach ($posts as $post): ?>
                        <div class="card post">
                            <div class="post-header">
                                <div class="post-info">
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
                                <span><i class="fas fa-heart"></i> <?php echo $post['like_count']; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $post['comment_count']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html> 