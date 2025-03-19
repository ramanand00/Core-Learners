<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['note_id'])) {
    echo json_encode(['success' => false, 'message' => 'Note ID is required']);
    exit();
}

$note_id = $data['note_id'];
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

    // Delete related records first
    $stmt = $conn->prepare("DELETE FROM note_views WHERE note_id = ?");
    $stmt->execute([$note_id]);

    $stmt = $conn->prepare("DELETE FROM note_likes WHERE note_id = ?");
    $stmt->execute([$note_id]);

    $stmt = $conn->prepare("DELETE FROM note_comments WHERE note_id = ?");
    $stmt->execute([$note_id]);

    // Finally delete the note
    $stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$note_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Note deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Note not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} 