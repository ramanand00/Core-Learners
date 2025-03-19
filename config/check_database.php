<?php
// Database configuration
$host = 'localhost';
$dbname = 'core_learners';
$username = 'root';
$password = '';

try {
    // Try to connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!<br>";
    
    // Check if database exists
    $stmt = $conn->query("SELECT DATABASE()");
    $current_db = $stmt->fetchColumn();
    echo "Connected to database: $current_db<br>";
    
    // Check if required tables exist
    $required_tables = [
        'users',
        'posts',
        'post_likes',
        'post_comments',
        'friendships',
        'interests',
        'user_interests',
        'courses',
        'course_lessons',
        'course_enrollments',
        'course_comments',
        'notes',
        'note_likes',
        'note_comments',
        'videos',
        'video_likes',
        'video_comments',
        'video_views',
        'notifications',
        'user_settings',
        'comments'
    ];
    
    echo "<br>Checking required tables:<br>";
    foreach ($required_tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ Table '$table' exists<br>";
        } else {
            echo "✗ Table '$table' is missing<br>";
        }
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 