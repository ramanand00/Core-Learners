<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user settings
$stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// If no settings exist, create default settings
if (!$settings) {
    $stmt = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
    $stmt->execute([$_SESSION['user_id']]);
    $settings = [
        'email_notifications' => true,
        'profile_visibility' => 'public',
        'theme_mode' => 'light'
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $profile_visibility = $_POST['profile_visibility'] ?? 'public';
    $theme_mode = $_POST['theme_mode'] ?? 'light';

    $stmt = $conn->prepare("
        UPDATE user_settings 
        SET email_notifications = ?, 
            profile_visibility = ?, 
            theme_mode = ?
        WHERE user_id = ?
    ");
    
    if ($stmt->execute([$email_notifications, $profile_visibility, $theme_mode, $_SESSION['user_id']])) {
        $success = 'Settings updated successfully!';
        // Refresh settings
        $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = 'Failed to update settings. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="settings-container">
        <div class="settings-sidebar">
            <div class="card">
                <div class="card-header">
                    <h3>Settings Menu</h3>
                </div>
                <div class="card-body">
                    <ul class="settings-menu">
                        <li class="active"><a href="#profile">Profile Settings</a></li>
                        <li><a href="#notifications">Notification Settings</a></li>
                        <li><a href="#privacy">Privacy Settings</a></li>
                        <li><a href="#appearance">Appearance</a></li>
                        <li><a href="#security">Security</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="settings-main">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="settings-form">
                <!-- Notification Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3>Notification Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="email_notifications" 
                                       <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                Enable email notifications
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Privacy Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3>Privacy Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="profile_visibility">Profile Visibility</label>
                            <select id="profile_visibility" name="profile_visibility" class="form-control">
                                <option value="public" <?php echo $settings['profile_visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                                <option value="friends" <?php echo $settings['profile_visibility'] === 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                                <option value="private" <?php echo $settings['profile_visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3>Appearance Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="theme_mode">Theme Mode</label>
                            <select id="theme_mode" name="theme_mode" class="form-control">
                                <option value="light" <?php echo $settings['theme_mode'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                <option value="dark" <?php echo $settings['theme_mode'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 