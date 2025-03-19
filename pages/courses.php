<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, u.username as instructor_name, u.profile_picture,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_count,
           (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id) as lessons_count,
           (SELECT COUNT(*) FROM course_comments WHERE course_id = c.id) as comments_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    JOIN course_enrollments ce ON c.id = ce.course_id
    WHERE ce.user_id = ?
    ORDER BY ce.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$enrolled_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's created courses (if instructor)
$stmt = $conn->prepare("
    SELECT c.*, u.username as instructor_name, u.profile_picture,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_count,
           (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id) as lessons_count,
           (SELECT COUNT(*) FROM course_comments WHERE course_id = c.id) as comments_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    WHERE c.instructor_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$my_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended courses
$stmt = $conn->prepare("
    SELECT c.*, u.username as instructor_name, u.profile_picture,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id) as enrolled_count,
           (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id) as lessons_count,
           (SELECT COUNT(*) FROM course_comments WHERE course_id = c.id) as comments_count
    FROM courses c
    JOIN users u ON c.instructor_id = u.id
    WHERE c.id NOT IN (
        SELECT course_id FROM course_enrollments WHERE user_id = ?
    )
    AND c.instructor_id != ?
    ORDER BY enrolled_count DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recommended_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get popular categories
$stmt = $conn->prepare("
    SELECT c.category, COUNT(*) as course_count
    FROM courses c
    GROUP BY c.category
    ORDER BY course_count DESC
    LIMIT 5
");
$stmt->execute();
$popular_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <!-- Create Course Section (for instructors) -->
        <?php if (isset($_SESSION['is_instructor']) && $_SESSION['is_instructor']): ?>
            <div class="card">
                <h2>Create Course</h2>
                <form action="../includes/create_course.php" method="POST" enctype="multipart/form-data" class="course-form">
                    <div class="form-group">
                        <label for="title">Course Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select a category</option>
                            <option value="programming">Programming</option>
                            <option value="design">Design</option>
                            <option value="business">Business</option>
                            <option value="marketing">Marketing</option>
                            <option value="personal_development">Personal Development</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price ($)</label>
                        <input type="number" id="price" name="price" class="form-control" min="0" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Course Thumbnail</label>
                        <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Course</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- My Courses Section -->
        <div class="card">
            <h2>My Courses</h2>
            <?php if (empty($my_courses)): ?>
                <div class="no-courses">
                    <i class="fas fa-book"></i>
                    <p>You haven't created any courses yet</p>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($my_courses as $course): ?>
                        <div class="course-card">
                            <div class="course-thumbnail">
                                <img src="<?php echo $course['thumbnail_path'] ? '../assets/images/courses/' . $course['thumbnail_path'] : '../assets/images/default-course.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            </div>
                            <div class="course-info">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> enrolled</span>
                                    <span><i class="fas fa-book"></i> <?php echo $course['lessons_count']; ?> lessons</span>
                                    <span><i class="fas fa-comment"></i> <?php echo $course['comments_count']; ?> comments</span>
                                </div>
                                <div class="course-actions">
                                    <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                                    <button class="btn btn-secondary edit-course" data-course-id="<?php echo $course['id']; ?>">Edit</button>
                                    <button class="btn btn-danger delete-course" data-course-id="<?php echo $course['id']; ?>">Delete</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Enrolled Courses Section -->
        <div class="card">
            <h2>Enrolled Courses</h2>
            <?php if (empty($enrolled_courses)): ?>
                <div class="no-courses">
                    <i class="fas fa-graduation-cap"></i>
                    <p>You haven't enrolled in any courses yet</p>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach ($enrolled_courses as $course): ?>
                        <div class="course-card">
                            <div class="course-thumbnail">
                                <img src="<?php echo $course['thumbnail_path'] ? '../assets/images/courses/' . $course['thumbnail_path'] : '../assets/images/default-course.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            </div>
                            <div class="course-info">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p>By <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                                <p><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> enrolled</span>
                                    <span><i class="fas fa-book"></i> <?php echo $course['lessons_count']; ?> lessons</span>
                                    <span><i class="fas fa-comment"></i> <?php echo $course['comments_count']; ?> comments</span>
                                </div>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">Continue Learning</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Popular Categories -->
        <?php if (!empty($popular_categories)): ?>
            <div class="card">
                <h2>Popular Categories</h2>
                <div class="categories-grid">
                    <?php foreach ($popular_categories as $category): ?>
                        <a href="courses.php?category=<?php echo urlencode($category['category']); ?>" class="category-card">
                            <i class="fas fa-folder"></i>
                            <h3><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $category['category']))); ?></h3>
                            <p><?php echo $category['course_count']; ?> courses</p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recommended Courses -->
        <?php if (!empty($recommended_courses)): ?>
            <div class="card">
                <h2>Recommended Courses</h2>
                <div class="courses-grid">
                    <?php foreach ($recommended_courses as $course): ?>
                        <div class="course-card">
                            <div class="course-thumbnail">
                                <img src="<?php echo $course['thumbnail_path'] ? '../assets/images/courses/' . $course['thumbnail_path'] : '../assets/images/default-course.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($course['title']); ?>">
                            </div>
                            <div class="course-info">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p>By <?php echo htmlspecialchars($course['instructor_name']); ?></p>
                                <p><?php echo htmlspecialchars($course['description']); ?></p>
                                <div class="course-meta">
                                    <span><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?> enrolled</span>
                                    <span><i class="fas fa-book"></i> <?php echo $course['lessons_count']; ?> lessons</span>
                                    <span><i class="fas fa-comment"></i> <?php echo $course['comments_count']; ?> comments</span>
                                </div>
                                <div class="course-price">
                                    <span class="price">$<?php echo number_format($course['price'], 2); ?></span>
                                </div>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">View Course</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle course deletion
            document.querySelectorAll('.delete-course').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this course?')) {
                        const courseId = this.dataset.courseId;
                        fetch('../includes/delete_course.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                course_id: courseId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('.course-card').remove();
                            }
                        });
                    }
                });
            });

            // Handle course editing
            document.querySelectorAll('.edit-course').forEach(button => {
                button.addEventListener('click', function() {
                    const courseId = this.dataset.courseId;
                    window.location.href = `edit-course.php?id=${courseId}`;
                });
            });
        });
    </script>
</body>
</html> 