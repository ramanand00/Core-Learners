<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data['post_id'] ?? null;
$content = $data['content'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$post_id || !$content) {
    echo json_encode(['success' => false, 'message' => 'Post ID and content are required']);
    exit();
}

try {
    // Insert the comment
    $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);
    $comment_id = $conn->lastInsertId();

    // Get the comment with user information
    $stmt = $conn->prepare("
        SELECT pc.*, u.username, u.profile_picture 
        FROM post_comments pc 
        JOIN users u ON pc.user_id = u.id 
        WHERE pc.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Format the date
    $comment['created_at'] = date('M d, Y H:i', strtotime($comment['created_at']));

    echo json_encode([
        'success' => true,
        'comment' => $comment
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 