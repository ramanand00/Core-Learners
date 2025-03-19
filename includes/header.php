<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Core Learners - E-Learning Platform</title>
    <link rel="stylesheet" href="/Core-Learners/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="/Core-Learners/index.php" class="logo">
                <img src="/Core-Learners/assets/images/logo1.webp" alt="Core Learners Logo">
            </a>
        </div>
        <div class="nav-middle">
            <ul class="nav-links">
                <li><a href="/Core-Learners/index.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="/Core-Learners/pages/friends.php"><i class="fas fa-users"></i> Friends</a></li>
                <li><a href="/Core-Learners/pages/courses.php"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="/Core-Learners/pages/notes.php"><i class="fas fa-sticky-note"></i> Notes</a></li>
                <li><a href="/Core-Learners/pages/videos.php"><i class="fas fa-video"></i> Videos</a></li>
                <li><a href="/Core-Learners/pages/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
            </ul>
        </div>
        <div class="nav-right">
            <div class="profile-dropdown">
                <a href="/Core-Learners/pages/profile.php" class="profile-link">
                    <img src="<?php echo isset($_SESSION['user_id']) ?>" alt="Profile">
                </a>
                <div class="dropdown-content">
                    <a href="/Core-Learners/pages/profile.php">Profile</a>
                    <a href="/Core-Learners/pages/settings.php">Settings</a>
                    <a href="/Core-Learners/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container"> 