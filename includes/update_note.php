<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if (!isset($_POST['note_id']) || !isset($_POST['title']) || !isset($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$note_id = $_POST['note_id'];
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$tags = isset($_POST['tags']) ? trim($_POST['tags']) : null;
$user_id = $_SESSION['user_id'];

try {
    // First check if the note belongs to the user
    $stmt = $conn->prepare("SELECT user_id FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$note || $note['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    // Update the note
    $stmt = $conn->prepare("
        UPDATE notes 
        SET title = ?, content = ?, tags = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$title, $content, $tags, $note_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Note updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 