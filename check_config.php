<?php
// Check if mod_rewrite is enabled
if (in_array('mod_rewrite', apache_get_modules())) {
    echo "mod_rewrite is enabled<br>";
} else {
    echo "mod_rewrite is NOT enabled<br>";
}

// Check PHP version
echo "PHP Version: " . phpversion() . "<br>";

// Check if .htaccess is being read
if (isset($_SERVER['HTACCESS'])) {
    echo ".htaccess is being read<br>";
} else {
    echo ".htaccess is NOT being read<br>";
}

// Display server information
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

// Check file permissions
$files = [
    '.htaccess',
    'index.php',
    'assets/css/style.css',
    'assets/js/script.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "$file exists<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "<br>";
    } else {
        echo "$file does NOT exist<br>";
    }
}
?> 