<?php
require_once 'config/database.php';

try {
    // Read the SQL file
    $sql = file_get_contents('update_tables.sql');
    
    // Split the SQL file into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $conn->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }
    
    echo "<br>Database updated successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 