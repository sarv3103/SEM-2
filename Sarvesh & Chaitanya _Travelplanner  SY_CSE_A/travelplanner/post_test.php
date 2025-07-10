<?php
// Test if PHP receives POST data
$post_data = print_r($_POST, true);
$request_data = print_r($_REQUEST, true);
$raw_data = file_get_contents('php://input');

$debug_content = "=== POST DATA ===\n";
$debug_content .= $post_data;
$debug_content .= "\n\n=== REQUEST DATA ===\n";
$debug_content .= $request_data;
$debug_content .= "\n\n=== RAW INPUT ===\n";
$debug_content .= $raw_data;
$debug_content .= "\n\n=== TIMESTAMP ===\n";
$debug_content .= date('Y-m-d H:i:s');

file_put_contents('debug_post.txt', $debug_content);
?>
<!DOCTYPE html>
<html>
<head>
    <title>POST Test - XAMPP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        input, button { padding: 8px; margin: 5px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>XAMPP POST Data Test</h1>
    
    <form method="POST" action="post_test.php">
        <div class="form-group">
            <label>Test Field:</label>
            <input type="text" name="testfield" value="hello world">
        </div>
        <div class="form-group">
            <label>Another Field:</label>
            <input type="text" name="another_field" value="test value">
        </div>
        <button type="submit">Send POST Data</button>
    </form>
    
    <div class="result">
        <h3>Debug Information:</h3>
        <p><strong>POST Data:</strong></p>
        <pre><?php echo htmlspecialchars($post_data); ?></pre>
        
        <p><strong>Request Data:</strong></p>
        <pre><?php echo htmlspecialchars($request_data); ?></pre>
        
        <p><strong>Raw Input:</strong></p>
        <pre><?php echo htmlspecialchars($raw_data); ?></pre>
        
        <p><strong>Debug file written:</strong> debug_post.txt</p>
        <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="result">
        <h3>Next Steps:</h3>
        <ol>
            <li>Submit the form above</li>
            <li>Check if debug_post.txt file is created in the same folder</li>
            <li>Open debug_post.txt and see if it contains the POST data</li>
            <li>If the file is empty or not created, there's a server configuration issue</li>
        </ol>
    </div>
</body>
</html> 