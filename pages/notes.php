<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's notes
$stmt = $conn->prepare("
    SELECT n.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM note_likes WHERE note_id = n.id) as likes_count,
           (SELECT COUNT(*) FROM note_comments WHERE note_id = n.id) as comments_count
    FROM notes n
    JOIN users u ON n.user_id = u.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended notes
$stmt = $conn->prepare("
    SELECT n.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM note_likes WHERE note_id = n.id) as likes_count,
           (SELECT COUNT(*) FROM note_comments WHERE note_id = n.id) as comments_count
    FROM notes n
    JOIN users u ON n.user_id = u.id
    WHERE n.user_id != ?
    AND n.id NOT IN (
        SELECT note_id FROM note_views WHERE user_id = ?
    )
    ORDER BY likes_count DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recommended_notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recently viewed notes
$stmt = $conn->prepare("
    SELECT n.*, u.username, u.profile_picture,
           (SELECT COUNT(*) FROM note_likes WHERE note_id = n.id) as likes_count,
           (SELECT COUNT(*) FROM note_comments WHERE note_id = n.id) as comments_count
    FROM notes n
    JOIN users u ON n.user_id = u.id
    JOIN note_views nv ON n.id = nv.note_id
    WHERE nv.user_id = ?
    ORDER BY nv.viewed_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recently_viewed = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.0-dev.3/quill.snow.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="notes-container">
        <!-- Create Note Section -->
        <div class="card">
            <h2>Create Note</h2>
            <form action="../includes/create_note.php" method="POST" class="note-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <div id="editor"></div>
                    <input type="hidden" name="content" id="content">
                </div>
                <div class="form-group">
                    <label for="tags">Tags (comma-separated)</label>
                    <input type="text" id="tags" name="tags" class="form-control" placeholder="e.g., programming, math, science">
                </div>
                <button type="submit" class="btn btn-primary">Create Note</button>
            </form>
        </div>

        <!-- My Notes Section -->
        <div class="card">
            <h2>My Notes</h2>
            <?php if (empty($my_notes)): ?>
                <div class="no-notes">
                    <i class="fas fa-sticky-note"></i>
                    <p>You haven't created any notes yet</p>
                </div>
            <?php else: ?>
                <div class="notes-grid">
                    <?php foreach ($my_notes as $note): ?>
                        <div class="note-card">
                            <div class="note-header">
                                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                                <div class="note-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $note['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $note['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $note['comments_count']; ?></span>
                                </div>
                            </div>
                            <div class="note-preview">
                                <?php echo substr(strip_tags($note['content']), 0, 200) . '...'; ?>
                            </div>
                            <div class="note-tags">
                                <?php foreach (explode(',', $note['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="note-actions">
                                <a href="note-view.php?id=<?php echo $note['id']; ?>" class="btn btn-primary">View</a>
                                <button class="btn btn-secondary edit-note" data-note-id="<?php echo $note['id']; ?>">Edit</button>
                                <button class="btn btn-danger delete-note" data-note-id="<?php echo $note['id']; ?>">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recently Viewed Notes -->
        <?php if (!empty($recently_viewed)): ?>
            <div class="card">
                <h2>Recently Viewed</h2>
                <div class="notes-grid">
                    <?php foreach ($recently_viewed as $note): ?>
                        <div class="note-card">
                            <div class="note-header">
                                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                                <div class="note-meta">
                                    <span>By <?php echo htmlspecialchars($note['username']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $note['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $note['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $note['comments_count']; ?></span>
                                </div>
                            </div>
                            <div class="note-preview">
                                <?php echo substr(strip_tags($note['content']), 0, 200) . '...'; ?>
                            </div>
                            <div class="note-tags">
                                <?php foreach (explode(',', $note['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="note-view.php?id=<?php echo $note['id']; ?>" class="btn btn-primary">View Again</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recommended Notes -->
        <?php if (!empty($recommended_notes)): ?>
            <div class="card">
                <h2>Recommended Notes</h2>
                <div class="notes-grid">
                    <?php foreach ($recommended_notes as $note): ?>
                        <div class="note-card">
                            <div class="note-header">
                                <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                                <div class="note-meta">
                                    <span>By <?php echo htmlspecialchars($note['username']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $note['views_count']; ?></span>
                                    <span><i class="fas fa-heart"></i> <?php echo $note['likes_count']; ?></span>
                                    <span><i class="fas fa-comment"></i> <?php echo $note['comments_count']; ?></span>
                                </div>
                            </div>
                            <div class="note-preview">
                                <?php echo substr(strip_tags($note['content']), 0, 200) . '...'; ?>
                            </div>
                            <div class="note-tags">
                                <?php foreach (explode(',', $note['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <a href="note-view.php?id=<?php echo $note['id']; ?>" class="btn btn-primary">View</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/2.0.0-dev.3/quill.min.js"></script>
    <script>
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'header': 1 }, { 'header': 2 }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });

        // Update hidden input before form submission
        document.querySelector('.note-form').addEventListener('submit', function() {
            document.getElementById('content').value = quill.root.innerHTML;
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle note deletion
            document.querySelectorAll('.delete-note').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this note?')) {
                        const noteId = this.dataset.noteId;
                        fetch('../includes/delete_note.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                note_id: noteId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.note-card').remove();
                            }
                        });
                    }
                });
            });

            // Handle note editing
            document.querySelectorAll('.edit-note').forEach(button => {
                button.addEventListener('click', function() {
                    const noteId = this.dataset.noteId;
                    window.location.href = `edit-note.php?id=${noteId}`;
                });
            });
        });
    </script>
</body>
</html> 