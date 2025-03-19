<?php
require_once 'config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('tables.sql');
    
    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }
    
    echo "<br>All tables created successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 