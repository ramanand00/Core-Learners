<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Core-Learners/pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$content = $_POST['content'] ?? '';
$file = $_FILES['file'] ?? null;

try {
    $conn->beginTransaction();

    // Handle file upload if present
    $file_path = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'application/pdf'];
        $max_size = 64 * 1024 * 1024; // 64MB

        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type');
        }

        if ($file['size'] > $max_size) {
            throw new Exception('File too large');
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $file_path = $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            throw new Exception('Failed to upload file');
        }
    }

    // Insert post into database
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, file_path, file_type) VALUES (?, ?, ?, ?)");
    $file_type = $file ? pathinfo($file['name'], PATHINFO_EXTENSION) : null;
    $stmt->execute([$user_id, $content, $file_path, $file_type]);

    $conn->commit();

    // Redirect back to home page
    header('Location: /Core-Learners/index.php');
    exit();

} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
    header('Location: /Core-Learners/index.php');
    exit();
}
?> 