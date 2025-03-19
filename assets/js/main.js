// File upload preview
function previewFile(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewElement.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Notification counter
function updateNotificationCount() {
    fetch('includes/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationBadge = document.querySelector('.notification-badge');
            if (notificationBadge) {
                notificationBadge.textContent = data.count;
                notificationBadge.style.display = data.count > 0 ? 'block' : 'none';
            }
        });
}

// Infinite scroll for posts
let loading = false;
let page = 1;

function loadMorePosts() {
    if (loading) return;
    
    loading = true;
    fetch(`includes/load_posts.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.posts.length > 0) {
                const postsContainer = document.querySelector('.posts-container');
                data.posts.forEach(post => {
                    postsContainer.appendChild(createPostElement(post));
                });
                page++;
            }
            loading = false;
        });
}

// Intersection Observer for infinite scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            loadMorePosts();
        }
    });
});

// Observe the loading trigger element
const loadingTrigger = document.querySelector('.loading-trigger');
if (loadingTrigger) {
    observer.observe(loadingTrigger);
}

// Create post element
function createPostElement(post) {
    const postElement = document.createElement('div');
    postElement.className = 'card post';
    postElement.innerHTML = `
        <div class="post-header">
            <img src="${post.profile_picture}" alt="${post.username}" class="profile-picture">
            <div class="post-info">
                <h3>${post.username}</h3>
                <span class="post-date">${post.created_at}</span>
            </div>
        </div>
        <div class="post-content">
            ${post.content}
        </div>
        ${post.file_path ? `
            <div class="post-file">
                ${getFilePreview(post.file_type, post.file_path)}
            </div>
        ` : ''}
        <div class="post-actions">
            <button class="btn-like" data-post-id="${post.id}">
                <i class="fas fa-heart"></i> ${post.likes}
            </button>
            <button class="btn-comment" data-post-id="${post.id}">
                <i class="fas fa-comment"></i> Comment
            </button>
        </div>
    `;
    return postElement;
}

// Get file preview based on file type
function getFilePreview(fileType, filePath) {
    switch (fileType) {
        case 'image':
            return `<img src="${filePath}" alt="Post image" class="post-image">`;
        case 'video':
            return `<video controls class="post-video">
                        <source src="${filePath}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>`;
        case 'pdf':
            return `<div class="pdf-preview">
                        <i class="fas fa-file-pdf"></i>
                        <a href="${filePath}" target="_blank">View PDF</a>
                    </div>`;
        default:
            return '';
    }
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', () => {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.dataset.tooltip;
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}); 