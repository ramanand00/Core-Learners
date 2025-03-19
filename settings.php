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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, bio = ? WHERE id = ?");
            $stmt->execute([$name, $email, $bio, $user['id']]);
            $success_message = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating profile: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_password, $user['id']]);
                    $success_message = "Password updated successfully!";
                } catch (PDOException $e) {
                    $error_message = "Error updating password: " . $e->getMessage();
                }
            } else {
                $error_message = "New passwords do not match!";
            }
        } else {
            $error_message = "Current password is incorrect!";
        }
    }
    
    if (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
        $course_updates = isset($_POST['course_updates']) ? 1 : 0;
        $friend_requests = isset($_POST['friend_requests']) ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("UPDATE user_settings SET 
                email_notifications = ?, 
                push_notifications = ?, 
                course_updates = ?, 
                friend_requests = ? 
                WHERE user_id = ?");
            $stmt->execute([$email_notifications, $push_notifications, $course_updates, $friend_requests, $user['id']]);
            $success_message = "Notification settings updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating notification settings: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_privacy'])) {
        $profile_visibility = $_POST['profile_visibility'];
        $course_progress = $_POST['course_progress'];
        $activity_status = $_POST['activity_status'];
        
        try {
            $stmt = $pdo->prepare("UPDATE user_settings SET 
                profile_visibility = ?, 
                course_progress = ?, 
                activity_status = ? 
                WHERE user_id = ?");
            $stmt->execute([$profile_visibility, $course_progress, $activity_status, $user['id']]);
            $success_message = "Privacy settings updated successfully!";
        } catch (PDOException $e) {
            $error_message = "Error updating privacy settings: " . $e->getMessage();
        }
    }
}

// Get user settings
try {
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching settings: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Core Learners</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="settings-container">
            <!-- Settings Sidebar -->
            <div class="settings-sidebar">
                <h2>Settings</h2>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#profile" data-bs-toggle="tab">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#security" data-bs-toggle="tab">
                            <i class="fas fa-shield-alt"></i> Security
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#notifications" data-bs-toggle="tab">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#privacy" data-bs-toggle="tab">
                            <i class="fas fa-lock"></i> Privacy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#appearance" data-bs-toggle="tab">
                            <i class="fas fa-paint-brush"></i> Appearance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#language" data-bs-toggle="tab">
                            <i class="fas fa-globe"></i> Language
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#billing" data-bs-toggle="tab">
                            <i class="fas fa-credit-card"></i> Billing
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="tab-content">
                    <!-- Profile Settings -->
                    <div class="tab-pane fade show active" id="profile">
                        <h3>Profile Settings</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Profile Picture</label>
                                <div class="profile-picture-preview">
                                    <img src="<?php echo $user['avatar'] ?? 'assets/images/default-avatar.png'; ?>" alt="Profile Picture" class="rounded-circle">
                                    <input type="file" name="avatar" class="form-control mt-2">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Bio</label>
                                <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div class="tab-pane fade" id="security">
                        <h3>Security Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                        </form>
                        
                        <div class="mt-4">
                            <h4>Two-Factor Authentication</h4>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="2fa">
                                <label class="form-check-label" for="2fa">Enable Two-Factor Authentication</label>
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div class="tab-pane fade" id="notifications">
                        <h3>Notification Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <h4>Email Notifications</h4>
                                <div class="form-check">
                                    <input type="checkbox" name="email_notifications" class="form-check-input" 
                                           <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Receive email notifications</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <h4>Push Notifications</h4>
                                <div class="form-check">
                                    <input type="checkbox" name="push_notifications" class="form-check-input"
                                           <?php echo $settings['push_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Receive push notifications</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <h4>Course Updates</h4>
                                <div class="form-check">
                                    <input type="checkbox" name="course_updates" class="form-check-input"
                                           <?php echo $settings['course_updates'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Receive course updates</label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <h4>Friend Requests</h4>
                                <div class="form-check">
                                    <input type="checkbox" name="friend_requests" class="form-check-input"
                                           <?php echo $settings['friend_requests'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Receive friend request notifications</label>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_notifications" class="btn btn-primary">Update Notification Settings</button>
                        </form>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="tab-pane fade" id="privacy">
                        <h3>Privacy Settings</h3>
                        <form method="POST">
                            <div class="form-group">
                                <h4>Profile Visibility</h4>
                                <select name="profile_visibility" class="form-control">
                                    <option value="public" <?php echo $settings['profile_visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="friends" <?php echo $settings['profile_visibility'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                    <option value="private" <?php echo $settings['profile_visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <h4>Course Progress</h4>
                                <select name="course_progress" class="form-control">
                                    <option value="public" <?php echo $settings['course_progress'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="friends" <?php echo $settings['course_progress'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                    <option value="private" <?php echo $settings['course_progress'] === 'private' ? 'selected' : ''; ?>>Private</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <h4>Activity Status</h4>
                                <select name="activity_status" class="form-control">
                                    <option value="online" <?php echo $settings['activity_status'] === 'online' ? 'selected' : ''; ?>>Show Online Status</option>
                                    <option value="offline" <?php echo $settings['activity_status'] === 'offline' ? 'selected' : ''; ?>>Hide Online Status</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="update_privacy" class="btn btn-primary">Update Privacy Settings</button>
                        </form>
                    </div>

                    <!-- Appearance Settings -->
                    <div class="tab-pane fade" id="appearance">
                        <h3>Appearance Settings</h3>
                        <div class="form-group">
                            <h4>Theme</h4>
                            <div class="theme-options">
                                <div class="form-check">
                                    <input type="radio" name="theme" class="form-check-input" value="light" checked>
                                    <label class="form-check-label">Light</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="theme" class="form-check-input" value="dark">
                                    <label class="form-check-label">Dark</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" name="theme" class="form-check-input" value="system">
                                    <label class="form-check-label">System Default</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <h4>Font Size</h4>
                            <select class="form-control">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </div>

                    <!-- Language Settings -->
                    <div class="tab-pane fade" id="language">
                        <h3>Language Settings</h3>
                        <div class="form-group">
                            <label>Preferred Language</label>
                            <select class="form-control">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                                <option value="de">German</option>
                                <option value="it">Italian</option>
                                <option value="pt">Portuguese</option>
                                <option value="ru">Russian</option>
                                <option value="zh">Chinese</option>
                                <option value="ja">Japanese</option>
                                <option value="ko">Korean</option>
                            </select>
                        </div>
                    </div>

                    <!-- Billing Settings -->
                    <div class="tab-pane fade" id="billing">
                        <h3>Billing Settings</h3>
                        <div class="card">
                            <div class="card-body">
                                <h5>Current Plan</h5>
                                <p>Free Plan</p>
                                <button class="btn btn-primary">Upgrade Plan</button>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5>Payment Methods</h5>
                                <button class="btn btn-secondary">Add Payment Method</button>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5>Billing History</h5>
                                <p>No billing history available</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview profile picture before upload
        document.querySelector('input[name="avatar"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-picture-preview img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 