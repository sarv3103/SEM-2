<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="#">
                            <i class="fas fa-plane"></i> TravelPlanner Admin
                        </a>
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link" href="php/admin_logout.php">Logout</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <div class="container mt-4">
            <h1 class="mb-4">
                <i class="fas fa-credit-card"></i> Payment Management Dashboard
            </h1>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-search fa-3x text-info mb-3"></i>
                            <h5 class="card-title">Simple Verification</h5>
                            <p class="card-text">Basic payment verification system</p>
                            <a href="php/simple_verification.php" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i> Access
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Enhanced Verification</h5>
                            <p class="card-text">Advanced payment verification with ticket generation</p>
                            <a href="php/enhanced_payment_verification.php" class="btn btn-success">
                                <i class="fas fa-arrow-right"></i> Access
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-tools fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Admin Verify Payment</h5>
                            <p class="card-text">Alternative verification interface</p>
                            <a href="php/admin_verify_payment.php" class="btn btn-warning">
                                <i class="fas fa-arrow-right"></i> Access
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i> How to Use</h5>
                        </div>
                        <div class="card-body">
                            <h6>Payment Verification Process:</h6>
                            <ol>
                                <li>When a user pays ₹1 on Razorpay but your website shows "Payment Failed"</li>
                                <li>Go to any of the verification systems above</li>
                                <li>Find the pending payment in the list</li>
                                <li>Click "Verify" button to auto-fill the Order ID</li>
                                <li>Enter the Payment ID from Razorpay</li>
                                <li>Click "Verify Payment" to update the database</li>
                                <li>The booking will be marked as paid and ticket will be generated</li>
                            </ol>
                            
                            <h6>Where to find Payment ID and Order ID:</h6>
                            <ul>
                                <li><strong>Payment ID:</strong> Found in Razorpay dashboard or email receipt</li>
                                <li><strong>Order ID:</strong> Found in your database or Razorpay dashboard</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-check-circle"></i> Manual Payment Verification</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="php/simple_verification.php" id="admin-dashboard-verify-form">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <label for="payment_id" class="form-label">Payment ID</label>
                                        <input type="text" class="form-control" id="payment_id" name="payment_id" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="order_id" class="form-label">Order ID</label>
                                        <input type="text" class="form-control" id="order_id" name="order_id" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success">Verify Payment</button>
                                        <span id="verify-spinner" style="display:none; margin-left:10px;"><span class="spinner-border spinner-border-sm text-success"></span> Verifying...</span>
                                    </div>
                                </div>
                            </form>
                            <div id="dashboard-verification-result" class="mt-3"></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-list"></i> Pending/Failed Payments</h5>
                        </div>
                        <div class="card-body">
                            <iframe src="php/simple_verification.php?embed=1" style="width:100%;height:400px;border:none;"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('admin-dashboard-verify-form').onsubmit = async function(e) {
        e.preventDefault();
        document.getElementById('verify-spinner').style.display = 'inline-block';
        document.getElementById('dashboard-verification-result').innerHTML = '';
        const payment_id = document.getElementById('payment_id').value;
        const order_id = document.getElementById('order_id').value;
        const formData = new URLSearchParams();
        formData.append('payment_id', payment_id);
        formData.append('order_id', order_id);
        try {
            const response = await fetch('php/simple_verification.php?ajax=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
            const data = await response.json();
            let html = '';
            if (data.success) {
                html += `<div class='alert alert-success'>${data.message}</div>`;
                if (data.booking) {
                    html += `<div class='card mt-3'><div class='card-header'><strong>Booking Details</strong></div><div class='card-body'>`;
                    html += `<p><strong>Booking ID:</strong> ${data.booking.booking_id}</p>`;
                    html += `<p><strong>Name:</strong> ${data.booking.name || ''}</p>`;
                    html += `<p><strong>Email:</strong> ${data.booking.contact_email || ''}</p>`;
                    html += `<p><strong>Destination:</strong> ${data.booking.destination || ''}</p>`;
                    html += `<p><strong>Travelers:</strong> ${data.booking.num_travelers || ''}</p>`;
                    html += `<p><strong>Status:</strong> ${data.booking.payment_status || ''}</p>`;
                    html += `</div></div>`;
                }
            } else {
                html += `<div class='alert alert-danger'>${data.message}</div>`;
            }
            document.getElementById('dashboard-verification-result').innerHTML = html;
        } catch (err) {
            document.getElementById('dashboard-verification-result').innerHTML = `<div class='alert alert-danger'>Error verifying payment. Please try again.</div>`;
        }
        document.getElementById('verify-spinner').style.display = 'none';
    };
    </script>
</body>
</html> 