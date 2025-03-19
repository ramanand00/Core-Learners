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

// Get course categories
try {
    $stmt = $pdo->query("SELECT * FROM course_categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching categories: " . $e->getMessage();
}

// Get courses with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9;
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

if (isset($_GET['level']) && !empty($_GET['level'])) {
    $where_conditions[] = "level = ?";
    $params[] = $_GET['level'];
}

if (isset($_GET['price']) && !empty($_GET['price'])) {
    switch ($_GET['price']) {
        case 'free':
            $where_conditions[] = "price = 0";
            break;
        case 'paid':
            $where_conditions[] = "price > 0";
            break;
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

try {
    // Get total count
    $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM courses $where_clause");
    $count_stmt->execute($params);
    $total_courses = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get courses for current page
    $stmt = $pdo->prepare("
        SELECT c.*, cat.name as category_name, u.name as instructor_name,
               (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count,
               (SELECT AVG(rating) FROM course_reviews WHERE course_id = c.id) as average_rating
        FROM courses c 
        LEFT JOIN course_categories cat ON c.category_id = cat.id 
        LEFT JOIN users u ON c.instructor_id = u.id 
        $where_clause 
        ORDER BY c.created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $params[] = $per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_pages = ceil($total_courses / $per_page);
} catch (PDOException $e) {
    $error_message = "Error fetching courses: " . $e->getMessage();
}

// Get user's enrolled courses
try {
    $stmt = $pdo->prepare("
        SELECT c.*, ce.enrolled_at, ce.progress 
        FROM courses c 
        JOIN course_enrollments ce ON c.id = ce.course_id 
        WHERE ce.user_id = ? 
        ORDER BY ce.enrolled_at DESC
    ");
    $stmt->execute([$user['id']]);
    $enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching enrolled courses: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Core Learners</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search courses..." 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
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
                                <select name="level" class="form-control">
                                    <option value="">All Levels</option>
                                    <option value="beginner" <?php echo isset($_GET['level']) && $_GET['level'] == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="intermediate" <?php echo isset($_GET['level']) && $_GET['level'] == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="advanced" <?php echo isset($_GET['level']) && $_GET['level'] == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="price" class="form-control">
                                    <option value="">All Prices</option>
                                    <option value="free" <?php echo isset($_GET['price']) && $_GET['price'] == 'free' ? 'selected' : ''; ?>>Free</option>
                                    <option value="paid" <?php echo isset($_GET['price']) && $_GET['price'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Course Grid -->
                <div class="course-grid">
                    <?php if (empty($courses)): ?>
                        <div class="col-12">
                            <p class="text-center">No courses found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-card">
                                <div class="course-image-container">
                                    <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                         class="course-image">
                                    <?php if ($course['price'] == 0): ?>
                                        <span class="course-badge badge-success">Free</span>
                                    <?php endif; ?>
                                </div>
                                <div class="course-info">
                                    <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <p class="text-muted">
                                        <?php echo htmlspecialchars($course['instructor_name']); ?> • 
                                        <?php echo htmlspecialchars($course['category_name']); ?>
                                    </p>
                                    <div class="course-stats">
                                        <span><i class="fas fa-users"></i> <?php echo number_format($course['enrollment_count']); ?></span>
                                        <span><i class="fas fa-star"></i> <?php echo number_format($course['average_rating'], 1); ?></span>
                                    </div>
                                    <div class="course-meta">
                                        <span><i class="fas fa-clock"></i> <?php echo $course['duration']; ?></span>
                                        <span><i class="fas fa-signal"></i> <?php echo ucfirst($course['level']); ?></span>
                                    </div>
                                    <div class="course-price">
                                        <?php if ($course['price'] > 0): ?>
                                            <span class="price">$<?php echo number_format($course['price'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="price">Free</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary w-100">
                                        View Course
                                    </a>
                                </div>
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
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?><?php echo isset($_GET['price']) ? '&price=' . $_GET['price'] : ''; ?>">
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
                <!-- My Courses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>My Courses</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($enrolled_courses)): ?>
                            <p>You haven't enrolled in any courses yet.</p>
                        <?php else: ?>
                            <?php foreach ($enrolled_courses as $course): ?>
                                <div class="enrolled-course-item">
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="d-flex align-items-center">
                                        <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                             class="enrolled-course-thumbnail">
                                        <div class="enrolled-course-info">
                                            <h6><?php echo htmlspecialchars($course['title']); ?></h6>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: <?php echo $course['progress']; ?>%"
                                                     aria-valuenow="<?php echo $course['progress']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                    <?php echo $course['progress']; ?>%
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Popular Courses -->
                <div class="card">
                    <div class="card-header">
                        <h5>Popular Courses</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $stmt = $pdo->query("
                                SELECT c.*, cat.name as category_name, u.name as instructor_name,
                                       (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrollment_count,
                                       (SELECT AVG(rating) FROM course_reviews WHERE course_id = c.id) as average_rating
                                FROM courses c 
                                LEFT JOIN course_categories cat ON c.category_id = cat.id 
                                LEFT JOIN users u ON c.instructor_id = u.id 
                                ORDER BY enrollment_count DESC 
                                LIMIT 5
                            ");
                            $popular_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $error_message = "Error fetching popular courses: " . $e->getMessage();
                        }
                        ?>

                        <?php if (!empty($popular_courses)): ?>
                            <?php foreach ($popular_courses as $course): ?>
                                <div class="popular-course-item">
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="d-flex align-items-center">
                                        <img src="<?php echo $course['thumbnail'] ?? 'assets/images/default-course.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($course['title']); ?>" 
                                             class="popular-course-thumbnail">
                                        <div class="popular-course-info">
                                            <h6><?php echo htmlspecialchars($course['title']); ?></h6>
                                            <small class="text-muted">
                                                <?php echo number_format($course['enrollment_count']); ?> students
                                            </small>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No popular courses available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 