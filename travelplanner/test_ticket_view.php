<!DOCTYPE html>
<html>
<head>
    <title>Test Ticket Generation</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-link { display: inline-block; margin: 10px; padding: 10px 20px; background: #0077cc; color: white; text-decoration: none; border-radius: 5px; }
        .test-link:hover { background: #005fa3; }
        .success { background: #28a745 !important; }
        .success:hover { background: #218838 !important; }
    </style>
</head>
<body>
    <h1>Test Ticket Generation</h1>
    <p>Click on any booking ID to test the ticket generation:</p>
    
    <a href="php/view_ticket_details.php?booking_id=66" target="_blank" class="test-link">Test Booking ID 66</a>
    <a href="php/view_ticket_details.php?booking_id=65" target="_blank" class="test-link">Test Booking ID 65</a>
    <a href="php/view_ticket_details.php?booking_id=64" target="_blank" class="test-link">Test Booking ID 64</a>
    <a href="php/view_ticket_details.php?booking_id=62" target="_blank" class="test-link">Test Booking ID 62</a>
    
    <h2>Alternative Test (Simple Script)</h2>
    <a href="test_simple_ticket.php?booking_id=66" target="_blank" class="test-link success">Test Simple Script - Booking 66</a>
    <a href="test_simple_ticket.php?booking_id=65" target="_blank" class="test-link success">Test Simple Script - Booking 65</a>
    
    <h2>Debug Information</h2>
    <p><a href="ticket_debug.html" target="_blank">View Last Generated HTML</a></p>
    <p><a href="error_log.txt" target="_blank">View Error Log</a></p>
    
    <h2>Instructions</h2>
    <ul>
        <li>Click any booking ID to test ticket generation</li>
        <li>The ticket should open in a new tab as a PDF</li>
        <li>If there's an error, check the error log</li>
        <li>The HTML template is saved to ticket_debug.html for debugging</li>
    </ul>
</body>
</html> 