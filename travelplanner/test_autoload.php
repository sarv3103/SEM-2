<?php
// Test autoload path
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing autoload paths...\n";

// Test the path that was failing
$path1 = __DIR__ . '/vendor/autoload.php';
echo "Path 1: $path1\n";
echo "Exists: " . (file_exists($path1) ? 'YES' : 'NO') . "\n\n";

// Test the correct path
$path2 = __DIR__ . '/vendor/autoload.php';
echo "Path 2: $path2\n";
echo "Exists: " . (file_exists($path2) ? 'YES' : 'NO') . "\n\n";

// Test from php directory
$path3 = __DIR__ . '/php/../vendor/autoload.php';
echo "Path 3: $path3\n";
echo "Exists: " . (file_exists($path3) ? 'YES' : 'NO') . "\n\n";

// Try to include the correct one
try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoload included successfully!\n";
    
    // Test if mPDF class exists
    if (class_exists('\Mpdf\Mpdf')) {
        echo "✅ mPDF class found!\n";
    } else {
        echo "❌ mPDF class not found!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 