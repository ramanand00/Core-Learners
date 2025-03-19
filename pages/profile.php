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
           (SELECT COUNT(*) FROM posts WHERE user_id = u.id) as post_count,
           (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.id) as enrolled_courses_count,
           (SELECT COUNT(*) FROM user_achievements WHERE user_id = u.id) as achievements_count
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user's achievements
$stmt = $conn->prepare("SELECT * FROM user_achievements WHERE user_id = ? ORDER BY date_achieved DESC");
$stmt->execute([$_SESSION['user_id']]);
$achievements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's education
$stmt = $conn->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$education = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's skills
$stmt = $conn->prepare("SELECT * FROM user_skills WHERE user_id = ? ORDER BY proficiency DESC");
$stmt->execute([$_SESSION['user_id']]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's certificates
$stmt = $conn->prepare("SELECT * FROM user_certificates WHERE user_id = ? ORDER BY issue_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's projects
$stmt = $conn->prepare("SELECT * FROM user_projects WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, ce.progress, ce.enrolled_at
    FROM courses c
    JOIN course_enrollments ce ON c.id = ce.course_id
    WHERE ce.user_id = ?
    ORDER BY ce.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $address = $_POST['address'] ?? '';
    $study_skills = $_POST['study_skills'] ?? '';
    $education = $_POST['education'] ?? '';
    $interests = $_POST['interests'] ?? '';
    $social_links = $_POST['social_links'] ?? '';

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
        SET full_name = ?, bio = ?, address = ?, study_skills = ?, 
            education = ?, interests = ?, social_links = ?, profile_picture = ?
        WHERE id = ?
    ");
    
    if ($stmt->execute([$full_name, $bio, $address, $study_skills, 
                       $education, $interests, $social_links, $profile_picture, $_SESSION['user_id']])) {
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
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-cover">
                <img src="<?php echo $user['cover_photo'] ? '../assets/images/covers/' . $user['cover_photo'] : '../assets/images/default-cover.jpg'; ?>" 
                     alt="Cover Photo" 
                     class="cover-photo">
            </div>
            <div class="profile-info-container">
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
                        <span><i class="fas fa-book"></i> <?php echo $user['enrolled_courses_count']; ?> Courses</span>
                        <span><i class="fas fa-trophy"></i> <?php echo $user['achievements_count']; ?> Achievements</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Left Sidebar -->
            <div class="profile-sidebar">
                <!-- About Section -->
                <div class="card">
                    <div class="card-header">
                        <h3>About</h3>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(htmlspecialchars($user['bio'] ?? 'No bio yet')); ?></p>
                        <div class="about-details">
                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['address'] ?? 'No location set'); ?></p>
                            <p><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($user['education'] ?? 'No education set'); ?></p>
                            <p><i class="fas fa-star"></i> <?php echo htmlspecialchars($user['study_skills'] ?? 'No study skills set'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Skills Section -->
                <div class="card">
                    <div class="card-header">
                        <h3>Skills</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($skills)): ?>
                            <p>No skills added yet</p>
                        <?php else: ?>
                            <div class="skills-list">
                                <?php foreach ($skills as $skill): ?>
                                    <div class="skill-item">
                                        <span class="skill-name"><?php echo htmlspecialchars($skill['skill']); ?></span>
                                        <div class="skill-progress">
                                            <div class="progress-bar" style="width: <?php 
                                                echo $skill['proficiency'] === 'expert' ? '100%' : 
                                                    ($skill['proficiency'] === 'advanced' ? '75%' : 
                                                    ($skill['proficiency'] === 'intermediate' ? '50%' : '25%')); 
                                            ?>"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Certificates Section -->
                <div class="card">
                    <div class="card-header">
                        <h3>Certificates</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($certificates)): ?>
                            <p>No certificates earned yet</p>
                        <?php else: ?>
                            <div class="certificates-list">
                                <?php foreach ($certificates as $cert): ?>
                                    <div class="certificate-item">
                                        <i class="fas fa-certificate"></i>
                                        <div class="certificate-info">
                                            <h4><?php echo htmlspecialchars($cert['title']); ?></h4>
                                            <p><?php echo htmlspecialchars($cert['issuer']); ?></p>
                                            <small>Issued: <?php echo date('M Y', strtotime($cert['issue_date'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="profile-main">
                <!-- Profile Edit Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>Edit Profile</h3>
                    </div>
                    <div class="card-body">
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

                            <div class="form-group">
                                <label for="education">Education</label>
                                <textarea id="education" name="education" class="form-control" rows="3"><?php echo htmlspecialchars($user['education'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="interests">Interests</label>
                                <textarea id="interests" name="interests" class="form-control" rows="3"><?php echo htmlspecialchars($user['interests'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label for="social_links">Social Links (JSON format)</label>
                                <textarea id="social_links" name="social_links" class="form-control" rows="3"><?php echo htmlspecialchars($user['social_links'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Enrolled Courses -->
                <div class="card">
                    <div class="card-header">
                        <h3>My Courses</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($enrolled_courses)): ?>
                            <p>You haven't enrolled in any courses yet.</p>
                        <?php else: ?>
                            <div class="enrolled-courses-grid">
                                <?php foreach ($enrolled_courses as $course): ?>
                                    <div class="course-card">
                                        <img src="<?php echo $course['thumbnail'] ?? '../assets/images/default-course.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                             class="course-thumbnail">
                                        <div class="course-info">
                                            <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $course['progress']; ?>%"
                                                     aria-valuenow="<?php echo $course['progress']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo $course['progress']; ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Projects -->
                <div class="card">
                    <div class="card-header">
                        <h3>My Projects</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($projects)): ?>
                            <p>No projects added yet</p>
                        <?php else: ?>
                            <div class="projects-grid">
                                <?php foreach ($projects as $project): ?>
                                    <div class="project-card">
                                        <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($project['description']); ?></p>
                                        <div class="project-meta">
                                            <span><i class="fas fa-calendar"></i> <?php echo date('M Y', strtotime($project['start_date'])); ?></span>
                                            <?php if ($project['end_date']): ?>
                                                <span><i class="fas fa-flag-checkered"></i> <?php echo date('M Y', strtotime($project['end_date'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($project['project_url']): ?>
                                            <a href="<?php echo htmlspecialchars($project['project_url']); ?>" class="btn btn-primary" target="_blank">
                                                View Project
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Posts -->
                <div class="card">
                    <div class="card-header">
                        <h3>My Posts</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($posts)): ?>
                            <p>No posts yet</p>
                        <?php else: ?>
                            <div class="posts-container">
                                <?php foreach ($posts as $post): ?>
                                    <div class="post">
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview profile picture before upload
        document.querySelector('input[name="profile_picture"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-picture').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 