<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all courses with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total courses count
$stmt = $conn->query("SELECT COUNT(*) FROM courses");
$total_courses = $stmt->fetchColumn();
$total_pages = ceil($total_courses / $limit);

// Get courses with category
$stmt = $conn->prepare("
    SELECT c.*, 
           u.username as instructor_name,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_students,
           (SELECT AVG(rating) FROM course_reviews WHERE course_id = c.id) as average_rating
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    ORDER BY c.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$limit, $offset]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get course categories
$stmt = $conn->query("SELECT DISTINCT category FROM courses WHERE category IS NOT NULL");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Core Learners</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <div class="courses-container">
        <div class="courses-header">
            <h1>Available Courses</h1>
            <div class="courses-search">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search courses..." 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <div class="courses-sidebar">
            <div class="card">
                <div class="card-header">
                    <h3>Categories</h3>
                </div>
                <div class="card-body">
                    <ul class="category-list">
                        <li>
                            <a href="?category=all" class="<?php echo !isset($_GET['category']) || $_GET['category'] === 'all' ? 'active' : ''; ?>">
                                All Courses
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li>
                                <a href="?category=<?php echo urlencode($category); ?>" 
                                   class="<?php echo isset($_GET['category']) && $_GET['category'] === $category ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="courses-main">
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <img src="<?php echo $course['thumbnail'] ?? '../assets/images/default-course.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                             class="course-thumbnail">
                        <div class="course-info">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="course-meta">
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                <span><i class="fas fa-users"></i> <?php echo $course['enrolled_students']; ?> students</span>
                                <?php if ($course['average_rating']): ?>
                                    <span><i class="fas fa-star"></i> <?php echo number_format($course['average_rating'], 1); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($course['category']): ?>
                                <span class="course-category"><?php echo htmlspecialchars($course['category']); ?></span>
                            <?php endif; ?>
                            <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" 
                           class="<?php echo $page === $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 