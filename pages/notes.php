<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /Core-Learners/pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle note creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_note'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags = trim($_POST['tags']);

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $conn->prepare("INSERT INTO notes (user_id, title, content, tags) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $content, $tags]);
            $_SESSION['success'] = "Note created successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error creating note: " . $e->getMessage();
        }
    }
}

// Get user's notes with view count
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
        WHERE n.user_id = ?
        GROUP BY n.id
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching notes: " . $e->getMessage();
    $notes = [];
}
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
        <!-- Create Note Form -->
        <div class="card create-note">
            <h2>Create New Note</h2>
            <form action="" method="POST" class="note-form">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="tags">Tags (comma-separated)</label>
                    <input type="text" id="tags" name="tags" class="form-control" placeholder="e.g., programming, web, php">
                </div>
                <button type="submit" name="create_note" class="btn btn-primary">Create Note</button>
            </form>
        </div>

        <!-- Notes List -->
        <div class="notes-list">
            <?php if (empty($notes)): ?>
                <div class="card">
                    <p class="text-center">No notes yet. Create your first note!</p>
                </div>
            <?php else: ?>
                <?php foreach ($notes as $note): ?>
                    <div class="card note-card">
                        <div class="note-header">
                            <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                            <div class="note-meta">
                                <span class="note-date"><?php echo date('M d, Y', strtotime($note['created_at'])); ?></span>
                                <span class="note-stats">
                                    <i class="fas fa-eye"></i> <?php echo $note['view_count']; ?>
                                    <i class="fas fa-heart"></i> <?php echo $note['like_count']; ?>
                                    <i class="fas fa-comment"></i> <?php echo $note['comment_count']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="note-content">
                            <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                        </div>
                        <?php if (!empty($note['tags'])): ?>
                            <div class="note-tags">
                                <?php foreach (explode(',', $note['tags']) as $tag): ?>
                                    <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="note-actions">
                            <button class="btn btn-sm btn-primary view-note" data-note-id="<?php echo $note['id']; ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button class="btn btn-sm btn-secondary edit-note" data-note-id="<?php echo $note['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger delete-note" data-note-id="<?php echo $note['id']; ?>">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Note View Modal -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="noteModalContent"></div>
        </div>
    </div>

    <!-- Note Edit Modal -->
    <div id="editNoteModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Note</h2>
            <form id="editNoteForm" method="POST">
                <input type="hidden" id="editNoteId" name="note_id">
                <div class="form-group">
                    <label for="editTitle">Title</label>
                    <input type="text" id="editTitle" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editContent">Content</label>
                    <textarea id="editContent" name="content" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="editTags">Tags (comma-separated)</label>
                    <input type="text" id="editTags" name="tags" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal functionality
        const modals = document.querySelectorAll('.modal');
        const closeButtons = document.querySelectorAll('.close');

        function openModal(modal) {
            modal.style.display = 'block';
        }

        function closeModal(modal) {
            modal.style.display = 'none';
        }

        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                closeModal(modal);
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal(event.target);
            }
        });

        // View note
        document.querySelectorAll('.view-note').forEach(button => {
            button.addEventListener('click', async function() {
                const noteId = this.dataset.noteId;
                try {
                    const response = await fetch(`/Core-Learners/includes/get_note.php?id=${noteId}`);
                    const data = await response.json();
                    if (data.success) {
                        const note = data.note;
                        document.getElementById('noteModalContent').innerHTML = `
                            <h2>${note.title}</h2>
                            <div class="note-meta">
                                <span class="note-date">${note.created_at}</span>
                            </div>
                            <div class="note-content">${note.content}</div>
                            ${note.tags ? `
                                <div class="note-tags">
                                    ${note.tags.split(',').map(tag => `<span class="tag">${tag.trim()}</span>`).join('')}
                                </div>
                            ` : ''}
                        `;
                        openModal(document.getElementById('noteModal'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Edit note
        document.querySelectorAll('.edit-note').forEach(button => {
            button.addEventListener('click', async function() {
                const noteId = this.dataset.noteId;
                try {
                    const response = await fetch(`/Core-Learners/includes/get_note.php?id=${noteId}`);
                    const data = await response.json();
                    if (data.success) {
                        const note = data.note;
                        document.getElementById('editNoteId').value = note.id;
                        document.getElementById('editTitle').value = note.title;
                        document.getElementById('editContent').value = note.content;
                        document.getElementById('editTags').value = note.tags || '';
                        openModal(document.getElementById('editNoteModal'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            });
        });

        // Delete note
        document.querySelectorAll('.delete-note').forEach(button => {
            button.addEventListener('click', async function() {
                if (confirm('Are you sure you want to delete this note?')) {
                    const noteId = this.dataset.noteId;
                    try {
                        const response = await fetch('/Core-Learners/includes/delete_note.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ note_id: noteId })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.closest('.note-card').remove();
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                }
            });
        });

        // Handle edit form submission
        document.getElementById('editNoteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            try {
                const response = await fetch('/Core-Learners/includes/update_note.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>
</html> 