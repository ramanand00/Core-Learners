<?php
require_once 'config/database.php';

try {
    // Drop all existing tables
    $tables = [
        'user_settings',
        'notifications',
        'video_comments',
        'video_likes',
        'video_views',
        'videos',
        'note_likes',
        'notes',
        'course_enrollments',
        'course_comments',
        'course_lessons',
        'courses',
        'user_interests',
        'interests',
        'friendships',
        'post_comments',
        'post_likes',
        'posts',
        'users'
    ];

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
        echo "Dropped table: $table<br>";
    }

    // Read and execute the SQL file
    $sql = file_get_contents('tables.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }
    
    echo "<br>All tables recreated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 