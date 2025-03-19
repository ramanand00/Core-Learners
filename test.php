<?php
// Basic test file
echo "Server is working!<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['test_file'])) {
        $file = $_FILES['test_file'];
        echo "File upload test:<br>";
        echo "Name: " . $file['name'] . "<br>";
        echo "Size: " . $file['size'] . "<br>";
        echo "Type: " . $file['type'] . "<br>";
        echo "Error: " . $file['error'] . "<br>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <input type="submit" value="Upload">
</form> 