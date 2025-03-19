<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$user_id = $_SESSION['user_id'];

try {
    // Get posts with user information and like status
    $stmt = $conn->prepare("
        SELECT 
            p.*, 
            u.username, 
            u.profile_picture,
            COUNT(DISTINCT pl.id) as likes_count,
            COUNT(DISTINCT pc.id) as comments_count,
            EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.id AND user_id = ?) as user_liked
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        LEFT JOIN post_likes pl ON p.id = pl.post_id
        LEFT JOIN post_comments pc ON p.id = pc.post_id
        WHERE p.user_id = ? OR p.user_id IN (
            SELECT friend_id 
            FROM friendships 
            WHERE user_id = ? AND status = 'accepted'
        )
        GROUP BY p.id
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$user_id, $user_id, $user_id, $per_page, $offset]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates and prepare file paths
    foreach ($posts as &$post) {
        $post['created_at'] = date('M d, Y H:i', strtotime($post['created_at']));
        if ($post['file_path']) {
            $post['file_path'] = '/Core-Learners/uploads/' . $post['file_path'];
        }
        if (!$post['profile_picture']) {
            $post['profile_picture'] = '/Core-Learners/assets/images/default-profile.png';
        }
    }

    echo json_encode([
        'success' => true,
        'posts' => $posts,
        'has_more' => count($posts) === $per_page
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?> 