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
    $stmt = $conn->prepare("INSERT INTO user_settings (user_id, theme_mode) VALUES (?, 'system')");
    $stmt->execute([$_SESSION['user_id']]);
    $settings = ['theme_mode' => 'system'];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme_mode = $_POST['theme_mode'] ?? 'system';
    
    $stmt = $conn->prepare("UPDATE user_settings SET theme_mode = ? WHERE user_id = ?");
    if ($stmt->execute([$theme_mode, $_SESSION['user_id']])) {
        $success = 'Settings updated successfully!';
        $settings['theme_mode'] = $theme_mode;
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
        <div class="settings-header">
            <h1>Settings</h1>
            <p>Customize your Core Learners experience</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="settings-section">
            <h2>Appearance</h2>
            <form method="POST" class="settings-form">
                <div class="form-group">
                    <label>Theme Mode</label>
                    <div class="theme-options">
                        <div class="theme-option">
                            <input type="radio" id="theme-light" name="theme_mode" value="light" 
                                   <?php echo $settings['theme_mode'] === 'light' ? 'checked' : ''; ?>>
                            <label for="theme-light">
                                <i class="fas fa-sun"></i>
                                <span>Light Mode</span>
                            </label>
                        </div>
                        <div class="theme-option">
                            <input type="radio" id="theme-dark" name="theme_mode" value="dark" 
                                   <?php echo $settings['theme_mode'] === 'dark' ? 'checked' : ''; ?>>
                            <label for="theme-dark">
                                <i class="fas fa-moon"></i>
                                <span>Dark Mode</span>
                            </label>
                        </div>
                        <div class="theme-option">
                            <input type="radio" id="theme-system" name="theme_mode" value="system" 
                                   <?php echo $settings['theme_mode'] === 'system' ? 'checked' : ''; ?>>
                            <label for="theme-system">
                                <i class="fas fa-desktop"></i>
                                <span>System Default</span>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="settings-section">
            <h2>Account Settings</h2>
            <div class="account-settings">
                <a href="change-password.php" class="btn btn-secondary">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="delete-account.php" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Account
                </a>
            </div>
        </div>

        <div class="settings-section">
            <h2>Notifications</h2>
            <div class="notification-settings">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        <span>Friend Requests</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        <span>Post Comments</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" checked>
                        <span>Course Updates</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html> 