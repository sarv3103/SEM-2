<?php
session_start();
require_once 'php/config.php';
require_once 'php/razorpay_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$message = '';
$error = '';
$paymentDetails = null;

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchType = $_POST['search_type'] ?? '';
    $searchValue = $_POST['search_value'] ?? '';
    
    if ($searchType && $searchValue) {
        try {
            switch ($searchType) {
                case 'payment_id':
                    // Search by Payment ID
                    $razorpay = new RazorpayService();
                    $payment = $razorpay->getPaymentDetails($searchValue);
                    if ($payment) {
                        $paymentDetails = $payment;
                        $message = "Payment found!";
                    } else {
                        $error = "Payment ID not found.";
                    }
                    break;
                    
                case 'order_id':
                    // Search by Order ID in database
                    $stmt = $conn->prepare("
                        SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
                        FROM payment_orders po
                        JOIN bookings b ON po.booking_id = b.id
                        WHERE po.razorpay_order_id = ?
                    ");
                    $stmt->bind_param("s", $searchValue);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $orderData = $result->fetch_assoc();
                    
                    if ($orderData) {
                        $message = "Order found! Payment ID: " . ($orderData['razorpay_payment_id'] ?? 'Not yet captured');
                        $paymentDetails = $orderData;
                    } else {
                        $error = "Order ID not found in database.";
                    }
                    break;
                    
                case 'booking_id':
                    // Search by Booking ID
                    $stmt = $conn->prepare("
                        SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
                        FROM payment_orders po
                        JOIN bookings b ON po.booking_id = b.id
                        WHERE b.booking_id = ?
                    ");
                    $stmt->bind_param("s", $searchValue);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $orderData = $result->fetch_assoc();
                    
                    if ($orderData) {
                        $message = "Booking found! Payment ID: " . ($orderData['razorpay_payment_id'] ?? 'Not yet captured');
                        $paymentDetails = $orderData;
                    } else {
                        $error = "Booking ID not found.";
                    }
                    break;
                    
                case 'email':
                    // Search by customer email
                    $stmt = $conn->prepare("
                        SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
                        FROM payment_orders po
                        JOIN bookings b ON po.booking_id = b.id
                        WHERE b.contact_email = ?
                        ORDER BY po.created_at DESC
                    ");
                    $stmt->bind_param("s", $searchValue);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $orderData = $result->fetch_assoc();
                    
                    if ($orderData) {
                        $message = "Customer found! Payment ID: " . ($orderData['razorpay_payment_id'] ?? 'Not yet captured');
                        $paymentDetails = $orderData;
                    } else {
                        $error = "Email not found.";
                    }
                    break;
            }
        } catch (Exception $e) {
            $error = "Error searching: " . $e->getMessage();
        }
    }
}

// Get recent payments for reference
$stmt = $conn->prepare("
    SELECT po.*, b.booking_id, b.name, b.contact_email, b.destination, b.fare
    FROM payment_orders po
    JOIN bookings b ON po.booking_id = b.id
    ORDER BY po.created_at DESC
    LIMIT 10
");
$stmt->execute();
$recentPayments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Payment ID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="payment_admin.php">
                            <i class="fas fa-plane"></i> TravelPlanner Admin
                        </a>
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link" href="payment_admin.php">Dashboard</a>
                            <a class="nav-link" href="php/admin_logout.php">Logout</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <div class="container mt-4">
            <h1 class="mb-4">
                <i class="fas fa-search"></i> Find Payment ID
            </h1>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Search Options -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-search"></i> Search for Payment ID</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="search_type" class="form-label">Search By</label>
                                        <select class="form-select" id="search_type" name="search_type" required>
                                            <option value="">Choose option...</option>
                                            <option value="payment_id">Payment ID</option>
                                            <option value="order_id">Order ID</option>
                                            <option value="booking_id">Booking ID</option>
                                            <option value="email">Customer Email</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="search_value" class="form-label">Search Value</label>
                                        <input type="text" class="form-control" id="search_value" name="search_value" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> Where to Find Payment ID</h5>
                        </div>
                        <div class="card-body">
                            <h6>From Razorpay Dashboard:</h6>
                            <ol>
                                <li>Login to Razorpay Dashboard</li>
                                <li>Go to Payments section</li>
                                <li>Find the payment</li>
                                <li>Copy the Payment ID</li>
                            </ol>
                            
                            <h6>From Email Receipt:</h6>
                            <ul>
                                <li>Check customer's email</li>
                                <li>Look for Razorpay receipt</li>
                                <li>Payment ID is in the receipt</li>
                            </ul>
                            
                            <h6>From Your Database:</h6>
                            <ul>
                                <li>Check recent payments below</li>
                                <li>Look for Order ID</li>
                                <li>Use Order ID to find Payment ID</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details Display -->
            <?php if ($paymentDetails): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Payment ID:</strong> <?php echo htmlspecialchars($paymentDetails['razorpay_payment_id'] ?? $paymentDetails['id'] ?? 'Not captured'); ?></p>
                            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($paymentDetails['razorpay_order_id'] ?? $paymentDetails['order_id'] ?? 'N/A'); ?></p>
                            <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($paymentDetails['booking_id']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge <?php echo ($paymentDetails['status'] ?? 'pending') === 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($paymentDetails['status'] ?? 'pending'); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($paymentDetails['name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($paymentDetails['contact_email']); ?></p>
                            <p><strong>Destination:</strong> <?php echo htmlspecialchars($paymentDetails['destination']); ?></p>
                            <p><strong>Amount:</strong> ₹<?php echo number_format($paymentDetails['fare']); ?></p>
                        </div>
                    </div>
                    
                    <?php if (($paymentDetails['status'] ?? 'pending') === 'pending'): ?>
                    <div class="mt-3">
                        <a href="php/simple_verification.php" class="btn btn-success">
                            <i class="fas fa-check"></i> Go to Verification
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Payments -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-clock"></i> Recent Payments (Reference)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Order ID</th>
                                    <th>Payment ID</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($payment = $recentPayments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['booking_id']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($payment['name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['contact_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['razorpay_payment_id'] ?? 'Not captured'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $payment['status'] === 'completed' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 