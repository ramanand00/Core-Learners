<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /Core-Learners/pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Build query
$query = "
    SELECT c.*, 
           u.username as instructor_name,
           u.profile_picture as instructor_picture,
           COUNT(DISTINCT ce.id) as enrolled_count,
           COUNT(DISTINCT cr.id) as review_count,
           AVG(cr.rating) as average_rating
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    LEFT JOIN course_enrollments ce ON c.id = ce.course_id
    LEFT JOIN course_reviews cr ON c.id = cr.course_id
    WHERE 1=1
";

$params = [];

if ($search) {
    $query .= " AND (c.title LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $query .= " AND c.category = ?";
    $params[] = $category;
}

$query .= " GROUP BY c.id ORDER BY c.created_at DESC";

// Get courses
$stmt = $conn->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$stmt = $conn->prepare("SELECT DISTINCT category FROM courses ORDER BY category");
$stmt->execute();
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
            <h2>Available Courses</h2>
            <form class="search-form" method="GET">
                <div class="search-group">
                    <input type="text" name="search" placeholder="Search courses..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>

        <?php if (empty($courses)): ?>
            <div class="card">
                <p class="text-center">No courses found</p>
            </div>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-image">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <?php else: ?>
                                <div class="course-image-placeholder">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="course-content">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <div class="course-meta">
                                <span class="instructor">
                                    <img src="<?php echo $course['instructor_picture'] ? '/Core-Learners/assets/images/profile/' . $course['instructor_picture'] : '/Core-Learners/assets/images/default-profile.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($course['instructor_name']); ?>"
                                         class="instructor-picture">
                                    <?php echo htmlspecialchars($course['instructor_name']); ?>
                                </span>
                                <span class="category"><?php echo htmlspecialchars($course['category']); ?></span>
                            </div>
                            <p class="course-description">
                                <?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="course-stats">
                                <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> enrolled</span>
                                <span><i class="fas fa-star"></i> <?php echo number_format($course['average_rating'], 1); ?></span>
                                <span><i class="fas fa-comments"></i> <?php echo $course['review_count']; ?> reviews</span>
                            </div>
                            <div class="course-footer">
                                <span class="course-price">$<?php echo number_format($course['price'], 2); ?></span>
                                <a href="/Core-Learners/pages/course.php?id=<?php echo $course['id']; ?>" 
                                   class="btn btn-primary">View Course</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <style>
    .courses-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .courses-header {
        margin-bottom: 2rem;
    }

    .search-form {
        margin-top: 1rem;
    }

    .search-group {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-group input,
    .search-group select {
        flex: 1;
        min-width: 200px;
    }

    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .course-card {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s;
    }

    .course-card:hover {
        transform: translateY(-5px);
    }

    .course-image {
        height: 200px;
        overflow: hidden;
    }

    .course-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .course-image-placeholder {
        width: 100%;
        height: 100%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #adb5bd;
    }

    .course-content {
        padding: 1.5rem;
    }

    .course-content h3 {
        margin: 0 0 1rem;
        font-size: 1.2rem;
    }

    .course-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .instructor {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .instructor-picture {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }

    .category {
        background-color: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
    }

    .course-description {
        color: #6c757d;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .course-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #6c757d;
    }

    .course-stats span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .course-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .course-price {
        font-size: 1.2rem;
        font-weight: bold;
        color: #2c3e50;
    }

    @media (max-width: 768px) {
        .search-group {
            flex-direction: column;
        }

        .search-group input,
        .search-group select,
        .search-group button {
            width: 100%;
        }

        .courses-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html> 