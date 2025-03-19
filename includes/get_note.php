<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Note ID is required']);
    exit();
}

$note_id = $_GET['id'];

try {
    $stmt = $conn->prepare("
        SELECT n.*, 
               COUNT(DISTINCT nv.id) as view_count,
               COUNT(DISTINCT nl.id) as like_count,
               COUNT(DISTINCT nc.id) as comment_count
        FROM notes n
        LEFT JOIN note_views nv ON n.id = nv.note_id
        LEFT JOIN note_likes nl ON n.id = nl.note_id
        LEFT JOIN note_comments nc ON n.id = nc.note_id
        WHERE n.id = ?
        GROUP BY n.id
    ");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($note) {
        // Record the view
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("
                INSERT INTO note_views (note_id, user_id, viewed_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE viewed_at = NOW()
            ");
            $stmt->execute([$note_id, $_SESSION['user_id']]);
        }

        echo json_encode(['success' => true, 'note' => $note]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 