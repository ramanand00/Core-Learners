<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getCurrentUser();
$success_message = '';
$error_message = '';

// Get video categories
try {
    $stmt = $pdo->query("SELECT * FROM video_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching categories: " . $e->getMessage();
}

// Get videos with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where_conditions = [];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
    $search_term = "%{$_GET['search']}%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM videos $where_clause");
    $count_stmt->execute($params);
    $total_videos = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get videos for current page
    $stmt = $pdo->prepare("
        SELECT v.*, c.name as category_name, u.name as instructor_name 
        FROM videos v 
        LEFT JOIN video_categories c ON v.category_id = c.id 
        LEFT JOIN users u ON v.instructor_id = u.id 
        $where_clause 
        ORDER BY v.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $params[] = $per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_videos / $per_page);
} catch (PDOException $e) {
    $error_message = "Error fetching videos: " . $e->getMessage();
}

// Get user's watch history
try {
    $stmt = $pdo->prepare("
        SELECT v.*, vh.watched_at 
        FROM video_history vh 
        JOIN videos v ON vh.video_id = v.id 
        WHERE vh.user_id = ? 
        ORDER BY vh.watched_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user['id']]);
    $watch_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching watch history: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos - Core Learners</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet" />
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Search and Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search videos..." 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                                <?php echo isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Video Grid -->
                <div class="video-grid">
                    <?php if (empty($videos)): ?>
                        <div class="col-12">
                            <p class="text-center">No videos found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videos as $video): ?>
                            <div class="video-card">
                                <a href="watch.php?id=<?php echo $video['id']; ?>" class="video-link">
                                    <div class="video-thumbnail-container">
                                        <img src="<?php echo $video['thumbnail'] ?? 'assets/images/default-thumbnail.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                             class="video-thumbnail">
                                        <span class="video-duration"><?php echo $video['duration']; ?></span>
                                    </div>
                                    <div class="video-info">
                                        <h5><?php echo htmlspecialchars($video['title']); ?></h5>
                                        <p class="text-muted">
                                            <?php echo htmlspecialchars($video['instructor_name']); ?> • 
                                            <?php echo htmlspecialchars($video['category_name']); ?>
                                        </p>
                                        <div class="video-stats">
                                            <span><i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?></span>
                                            <span><i class="fas fa-thumbs-up"></i> <?php echo number_format($video['likes']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Watch History -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Watch History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($watch_history)): ?>
                            <p>No watch history</p>
                        <?php else: ?>
                            <?php foreach ($watch_history as $video): ?>
                                <div class="watch-history-item">
                                    <a href="watch.php?id=<?php echo $video['id']; ?>" class="d-flex align-items-center">
                                        <img src="<?php echo $video['thumbnail'] ?? 'assets/images/default-thumbnail.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                             class="watch-history-thumbnail">
                                        <div class="watch-history-info">
                                            <h6><?php echo htmlspecialchars($video['title']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($video['watched_at'])); ?>
                                            </small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Popular Videos -->
                <div class="card">
                    <div class="card-header">
                        <h5>Popular Videos</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT v.*, c.name as category_name, u.name as instructor_name 
                                FROM videos v 
                                LEFT JOIN video_categories c ON v.category_id = c.id 
                                LEFT JOIN users u ON v.instructor_id = u.id 
                                ORDER BY v.views DESC 
                                LIMIT 5
                            ");
                            $popular_videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $error_message = "Error fetching popular videos: " . $e->getMessage();
                        }
                        ?>

                        <?php if (!empty($popular_videos)): ?>
                            <?php foreach ($popular_videos as $video): ?>
                                <div class="popular-video-item">
                                    <a href="watch.php?id=<?php echo $video['id']; ?>" class="d-flex align-items-center">
                                        <img src="<?php echo $video['thumbnail'] ?? 'assets/images/default-thumbnail.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                             class="popular-video-thumbnail">
                                        <div class="popular-video-info">
                                            <h6><?php echo htmlspecialchars($video['title']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo number_format($video['views']); ?> views
                                            </small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No popular videos available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://vjs.zencdn.net/7.20.3/video.min.js"></script>
    <script>
        // Initialize video.js for any video players on the page
        document.addEventListener('DOMContentLoaded', function() {
            videojs('video-player', {
                controls: true,
                autoplay: false,
                preload: 'auto',
                responsive: true,
                fluid: true
            });
        });
    </script>
</body>
</html> 