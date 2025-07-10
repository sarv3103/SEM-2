<?php
echo "✅ Server is working!";
echo "<br>Current directory: " . __DIR__;
echo "<br>Files in php directory:";
$files = scandir('php');
foreach($files as $file) {
    if($file != '.' && $file != '..') {
        echo "<br>- " . $file;
    }
}
?> 