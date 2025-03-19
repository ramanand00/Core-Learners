// Post Interactions
document.addEventListener('DOMContentLoaded', function() {
    // Handle post likes
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            handleLike(postId, this);
        });
    });

    // Handle post comments
    document.querySelectorAll('.btn-comment').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            showCommentSection(postId);
        });
    });

    // Handle file upload preview
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                previewFile(file);
            }
        });
    }

    // Infinite scroll
    const loadingTrigger = document.querySelector('.loading-trigger');
    if (loadingTrigger) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMorePosts();
                }
            });
        });
        observer.observe(loadingTrigger);
    }
});

// Handle post likes
async function handleLike(postId, button) {
    try {
        const response = await fetch('/Core-Learners/includes/handle_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        });

        const data = await response.json();
        if (data.success) {
            button.classList.toggle('liked');
            const likeCount = button.querySelector('.like-count');
            if (likeCount) {
                likeCount.textContent = data.likes;
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Show comment section
function showCommentSection(postId) {
    const post = document.querySelector(`[data-post-id="${postId}"]`).closest('.post');
    let commentSection = post.querySelector('.comment-section');
    
    if (!commentSection) {
        commentSection = createCommentSection(postId);
        post.appendChild(commentSection);
    }
    
    commentSection.style.display = commentSection.style.display === 'none' ? 'block' : 'none';
}

// Create comment section
function createCommentSection(postId) {
    const section = document.createElement('div');
    section.className = 'comment-section';
    section.innerHTML = `
        <div class="comment-form">
            <textarea placeholder="Write a comment..."></textarea>
            <button class="btn btn-primary submit-comment">Post</button>
        </div>
        <div class="comments-list"></div>
    `;

    // Handle comment submission
    const submitButton = section.querySelector('.submit-comment');
    submitButton.addEventListener('click', () => submitComment(postId, section));

    return section;
}

// Submit comment
async function submitComment(postId, section) {
    const textarea = section.querySelector('textarea');
    const content = textarea.value.trim();

    if (!content) return;

    try {
        const response = await fetch('/Core-Learners/includes/handle_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                content: content
            })
        });

        const data = await response.json();
        if (data.success) {
            addCommentToSection(section, data.comment);
            textarea.value = '';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Add comment to section
function addCommentToSection(section, comment) {
    const commentsList = section.querySelector('.comments-list');
    const commentElement = document.createElement('div');
    commentElement.className = 'comment';
    commentElement.innerHTML = `
        <img src="${comment.profile_picture}" alt="${comment.username}" class="comment-avatar">
        <div class="comment-content">
            <div class="comment-header">
                <span class="comment-username">${comment.username}</span>
                <span class="comment-date">${comment.created_at}</span>
            </div>
            <p>${comment.content}</p>
        </div>
    `;
    commentsList.insertBefore(commentElement, commentsList.firstChild);
}

// Preview file upload
function previewFile(file) {
    const reader = new FileReader();
    const preview = document.createElement('div');
    preview.className = 'file-preview';

    reader.onload = function(e) {
        if (file.type.startsWith('image/')) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        } else if (file.type.startsWith('video/')) {
            preview.innerHTML = `<video src="${e.target.result}" controls></video>`;
        } else if (file.type === 'application/pdf') {
            preview.innerHTML = `
                <div class="pdf-preview">
                    <i class="fas fa-file-pdf"></i>
                    <span>${file.name}</span>
                </div>
            `;
        }

        const existingPreview = document.querySelector('.file-preview');
        if (existingPreview) {
            existingPreview.remove();
        }
        document.querySelector('.create-post').insertBefore(preview, document.querySelector('.btn-primary'));
    };

    reader.readAsDataURL(file);
}

// Load more posts
let isLoading = false;
let currentPage = 1;

async function loadMorePosts() {
    if (isLoading) return;
    isLoading = true;

    try {
        const response = await fetch(`/Core-Learners/includes/load_posts.php?page=${currentPage + 1}`);
        const data = await response.json();

        if (data.success && data.posts.length > 0) {
            appendPosts(data.posts);
            currentPage++;
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        isLoading = false;
    }
}

// Append new posts
function appendPosts(posts) {
    const container = document.querySelector('.posts-container');
    posts.forEach(post => {
        const postElement = createPostElement(post);
        container.appendChild(postElement);
    });
}

// Create post element
function createPostElement(post) {
    const div = document.createElement('div');
    div.className = 'card post';
    div.innerHTML = `
        <div class="post-header">
            <img src="${post.profile_picture}" alt="${post.username}" class="profile-picture">
            <div class="post-info">
                <h3>${post.username}</h3>
                <span class="post-date">${post.created_at}</span>
            </div>
        </div>
        <div class="post-content">${post.content}</div>
        ${post.file_path ? createFilePreview(post.file_path) : ''}
        <div class="post-actions">
            <button class="btn-like" data-post-id="${post.id}">
                <i class="fas fa-heart"></i> Like
            </button>
            <button class="btn-comment" data-post-id="${post.id}">
                <i class="fas fa-comment"></i> Comment
            </button>
        </div>
    `;

    // Add event listeners
    const likeButton = div.querySelector('.btn-like');
    const commentButton = div.querySelector('.btn-comment');

    likeButton.addEventListener('click', () => handleLike(post.id, likeButton));
    commentButton.addEventListener('click', () => showCommentSection(post.id));

    return div;
}

// Create file preview
function createFilePreview(filePath) {
    const fileType = filePath.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) {
        return `<div class="post-file"><img src="${filePath}" alt="Post image" class="post-image"></div>`;
    } else if (['mp4', 'webm'].includes(fileType)) {
        return `
            <div class="post-file">
                <video controls class="post-video">
                    <source src="${filePath}" type="video/${fileType}">
                    Your browser does not support the video tag.
                </video>
            </div>
        `;
    } else if (fileType === 'pdf') {
        return `
            <div class="post-file">
                <div class="pdf-preview">
                    <i class="fas fa-file-pdf"></i>
                    <a href="${filePath}" target="_blank">View PDF</a>
                </div>
            </div>
        `;
    }
    return '';
} 