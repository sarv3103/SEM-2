<?php
// admin_dashboard.php - (Reset for new logic)
session_start();
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/session.php';
requireAdmin();

// Calculate total revenue from wallet top-ups (Razorpay)
$total_revenue = 0;
$res = $conn->query("SELECT SUM(amount) as total FROM payment_orders WHERE payment_method = 'razorpay' AND status = 'completed'");
if ($res && $row = $res->fetch_assoc()) {
    $total_revenue = floatval($row['total']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TravelPlanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-card {
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            transition: box-shadow 0.2s;
            min-height: 210px;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 24px rgba(0,0,0,0.13);
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            font-family: inherit;
            line-height: 1.1;
        }
        .stat-label {
            font-size: 1.1rem;
            color: #888;
            font-family: inherit;
            font-weight: 400;
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.2rem;
        }
        .icon-value-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 70px;
        }
        .card-body {
            text-align: center;
        }
        .text-purple { color: #6f42c1 !important; }
        .border-purple { border-color: #6f42c1 !important; }
        .btn-outline-purple {
            color: #6f42c1;
            border-color: #6f42c1;
            background-color: transparent;
        }
        .btn-outline-purple:hover, .btn-outline-purple:focus {
            background-color: #6f42c1;
            color: #fff;
            border-color: #6f42c1;
        }
        .table-responsive { max-height: none; }
        /* Only allow horizontal scroll on very small screens */
        @media (max-width: 575.98px) {
          #bookings-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
          }
        }
        #bookings-table th, #bookings-table td {
          white-space: normal;
          word-break: break-word;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Welcome, Admin!</h1>
        <a href="php/admin_logout.php" class="btn btn-danger">Logout</a>
    </div>
    <div class="row mb-4" id="dashboard-summary-cards">
        <div class="col-md-3" id="user-summary-card"></div>
        <div class="col-md-3" id="message-summary-card"></div>
        <div class="col-md-3" id="revenue-summary-card"></div>
        <div class="col-md-3" id="booking-summary-card"></div>
    </div>
    
</div>

<!-- Bookings Section -->
<div class="container py-5">
  <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">Contact Messages</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue" type="button" role="tab">Revenue</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">Payments & Wallet</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">Bookings</button>
    </li>
  </ul>
  <div class="tab-content" id="adminTabsContent">
    <div class="tab-pane fade show active" id="users" role="tabpanel">
      <div class="mb-3 d-flex justify-content-between align-items-center">
        <input type="text" id="userSearch" class="form-control w-25" placeholder="Search users...">
                    <div>
          <button class="btn btn-success btn-sm me-2" id="addUserBtn"><i class="fas fa-user-plus"></i> Add User</button>
          <button class="btn btn-outline-primary btn-sm" id="downloadUsersBtn"><i class="fas fa-download"></i> Download CSV</button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="users-table">
          <thead class="table-light">
            <tr>
              <th>User ID</th>
              <th>Username</th>
              <th>Name</th>
              <th>Email</th>
              <th>Mobile</th>
              <th>Registered</th>
              <th>Email Verified</th>
              <th>Mobile Verified</th>
              <th>Admin</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- User rows will be inserted here -->
          </tbody>
        </table>
                    </div>
                </div>
    <div class="tab-pane fade" id="messages" role="tabpanel">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h4><i class="fas fa-envelope"></i> Contact Messages</h4>
        <button class="btn btn-outline-primary btn-sm" id="export-messages"><i class="fas fa-file-export"></i> Export</button>
      </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover" id="messages-table">
          <thead class="table-light">
            <tr>
              <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Subject</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody><!-- Data loaded by JS --></tbody>
        </table>
                </div>
            </div>
    <div class="tab-pane fade" id="revenue" role="tabpanel">
      <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-outline-primary btn-sm" id="export-revenue"><i class="fas fa-file-export"></i> Export Revenue</button>
      </div>
      <div class="row mb-4" id="revenue-summary-cards"></div>
      <div class="mb-3 row g-2 align-items-end" id="revenue-filters">
        <div class="col-md-3">
          <label class="form-label">Search</label>
          <input type="text" class="form-control" id="revenueSearch" placeholder="Booking ID, Payment ID, Destination...">
        </div>
        <div class="col-md-2">
          <label class="form-label">Min Amount</label>
          <input type="number" class="form-control" id="revenueMinAmount" placeholder="Min">
        </div>
        <div class="col-md-2">
          <label class="form-label">Max Amount</label>
          <input type="number" class="form-control" id="revenueMaxAmount" placeholder="Max">
        </div>
        <div class="col-md-2">
          <label class="form-label">From Date</label>
          <input type="date" class="form-control" id="revenueFromDate">
        </div>
        <div class="col-md-2">
          <label class="form-label">To Date</label>
          <input type="date" class="form-control" id="revenueToDate">
        </div>
        <div class="col-md-2">
          <label class="form-label">Destination</label>
          <select class="form-select" id="revenueDestination">
            <option value="">All</option>
          </select>
                    </div>
                </div>
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="revenue-table">
          <thead class="table-light">
            <tr>
              <th>Booking ID</th>
              <th>Booking Date</th>
              <th>Amount</th>
              <th>Destination</th>
              <th>Razorpay Payment ID</th>
              <th>View Details</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
                </div>
            </div>
    <div class="tab-pane fade" id="payments" role="tabpanel">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fas fa-credit-card"></i> Payments & Wallet Management</h3>
        <div>
          <button class="btn btn-outline-primary btn-sm me-2" id="export-payments"><i class="fas fa-file-export"></i> Export Payments</button>
          <button class="btn btn-success btn-sm" id="add-wallet-credit-btn"><i class="fas fa-plus"></i> Add Wallet Credit</button>
        </div>
      </div>
      <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
      <div style="margin: 10px 0 20px 0; text-align: right;">
        <a href="wallet_admin.php" class="btn btn-primary" style="font-size: 1.1em;">
          <i class="fa fa-wallet"></i> Go to Wallet Management
        </a>
      </div>
      <?php endif; ?>

      <!-- Payment Summary Cards -->
      <div class="row mb-4" id="payment-summary-cards">
        <div class="col-md-3">
          <div class="card dashboard-card border-primary">
            <div class="card-body text-center">
              <i class="fas fa-wallet fa-2x text-primary mb-2"></i>
              <h4 class="mb-1" id="total-wallet-balance">₹0</h4>
              <p class="text-muted mb-0">Total Wallet Balance</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card dashboard-card border-success">
            <div class="card-body text-center">
              <i class="fas fa-rupee-sign fa-2x text-success mb-2"></i>
              <h4 class="mb-1" id="total-revenue">₹<?= number_format($total_revenue, 2) ?></h4>
              <p class="text-muted mb-0">Total Revenue (Wallet Top-Ups)</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card dashboard-card border-warning">
            <div class="card-body text-center">
              <i class="fas fa-clock fa-2x text-warning mb-2"></i>
              <h4 class="mb-1" id="pending-payments">0</h4>
              <p class="text-muted mb-0">Pending Payments</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card dashboard-card border-danger">
            <div class="card-body text-center">
              <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
              <h4 class="mb-1" id="failed-payments">0</h4>
              <p class="text-muted mb-0">Failed Payments</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Filters -->
      <div class="row mb-3">
        <div class="col-md-3">
          <input type="text" id="paymentSearch" class="form-control" placeholder="Search by user, payment ID...">
        </div>
        <div class="col-md-2">
          <select class="form-select" id="paymentStatusFilter">
            <option value="">All Status</option>
            <option value="completed">Completed</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" id="paymentTypeFilter">
            <option value="">All Types</option>
            <option value="booking">Booking Payment</option>
            <option value="wallet">Wallet Top-up</option>
            <option value="refund">Refund</option>
          </select>
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" id="paymentFromDate" placeholder="From Date">
        </div>
        <div class="col-md-2">
          <input type="date" class="form-control" id="paymentToDate" placeholder="To Date">
        </div>
        <div class="col-md-1">
          <button class="btn btn-outline-secondary" id="clearPaymentFilters">Clear</button>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="table-responsive">
        <table class="table table-striped table-hover" id="payments-table">
          <thead class="table-light">
            <tr>
              <th>Payment ID</th>
              <th>User</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Payment Method</th>
              <th>Date</th>
              <th>Reference</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <!-- Data will be loaded by JavaScript -->
          </tbody>
        </table>
      </div>

      <!-- Wallet Management Section -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5><i class="fas fa-wallet"></i> User Wallet Balances</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive" style="max-height: 300px;">
                <table class="table table-sm" id="wallet-balances-table">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Balance</th>
                      <th>Last Updated</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Wallet data will be loaded here -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h5><i class="fas fa-chart-line"></i> Payment Statistics</h5>
            </div>
            <div class="card-body">
              <div id="payment-stats">
                <!-- Statistics will be loaded here -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="bookings" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0"><i class="fas fa-ticket-alt"></i> Bookings</h3>
            <button class="btn btn-outline-primary btn-sm" id="export-bookings"><i class="fas fa-file-export"></i> Export Bookings</button>
        </div>
        <input type="text" id="bookingSearch" class="form-control w-25 mb-2" placeholder="Search bookings...">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="bookings-table">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Date</th>
                        <th>Travellers</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Ticket</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
  </div>
</div>

<!-- Traveller Details Modal -->
<div class="modal fade" id="travellerDetailsModal" tabindex="-1" aria-labelledby="travellerDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="travellerDetailsModalLabel">Traveller & Booking Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="travellerDetailsBody">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addUserForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="addUsername" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="addUsername" required>
          </div>
          <div class="mb-3">
            <label for="addName" class="form-label">Name</label>
            <input type="text" class="form-control" name="name" id="addName">
          </div>
          <div class="mb-3">
            <label for="addEmail" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="addEmail" required>
          </div>
          <div class="mb-3">
            <label for="addMobile" class="form-label">Mobile</label>
            <input type="text" class="form-control" name="mobile" id="addMobile">
          </div>
          <div class="mb-3">
            <label for="addPassword" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="addPassword" required>
          </div>
          <div class="mb-3">
            <label for="addIsAdmin" class="form-label">Admin</label>
            <select class="form-select" name="is_admin" id="addIsAdmin">
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <input type="hidden" name="user_id" id="editUserId">
          <div class="mb-3">
            <label for="editUsername" class="form-label">Username</label>
            <input type="text" class="form-control" name="username" id="editUsername" required>
          </div>
          <div class="mb-3">
            <label for="editName" class="form-label">Name</label>
            <input type="text" class="form-control" name="name" id="editName">
          </div>
          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="editEmail" required>
          </div>
          <div class="mb-3">
            <label for="editMobile" class="form-label">Mobile</label>
            <input type="text" class="form-control" name="mobile" id="editMobile">
          </div>
          <div class="mb-3">
            <label class="form-label">Email Verified</label>
            <select class="form-select" name="is_verified" id="editEmailVerified">
              <option value="1">Yes</option>
              <option value="0">No</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Password Reset Modal -->
<div class="modal fade" id="resetPwModal" tabindex="-1" aria-labelledby="resetPwModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resetPwModalLabel">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="resetPwForm">
        <div class="modal-body">
          <input type="hidden" name="user_id" id="resetPwUserId">
          <div class="mb-3">
            <label for="resetPassword" class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password" id="resetPassword" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Contact Messages Modal -->
<div class="modal fade" id="replyMessageModal" tabindex="-1" aria-labelledby="replyMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="replyMessageModalLabel">Reply to Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="replyMessageForm">
        <div class="modal-body">
          <input type="hidden" name="message_id" id="replyMessageId">
          <div class="mb-3">
            <label for="replyToEmail" class="form-label">To</label>
            <input type="email" class="form-control" id="replyToEmail" name="to_email" readonly>
          </div>
          <div class="mb-3">
            <label for="replySubject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="replySubject" name="subject" required>
          </div>
          <div class="mb-3">
            <label for="replyBody" class="form-label">Message</label>
            <textarea class="form-control" id="replyBody" name="body" rows="5" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Send Reply</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Revenue Details Modal -->
<div class="modal fade" id="revenueDetailsModal" tabindex="-1" aria-labelledby="revenueDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="revenueDetailsModalLabel">Booking & Payment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="revenueDetailsBody">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Add modal at the end of the file -->
<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-labelledby="verifyPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifyPaymentModalLabel">Verify Payment & Confirm Booking</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="verifyPaymentModalBody">
        <!-- Booking/payment details will be loaded here by JS -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" id="cancelBookingBtn">Cancel Booking</button>
        <button type="button" class="btn btn-success" id="markAsPaidBtn">Mark as Paid</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Wallet Credit Modal -->
<div class="modal fade" id="addWalletCreditModal" tabindex="-1" aria-labelledby="addWalletCreditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addWalletCreditModalLabel">Add Wallet Credit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addWalletCreditForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="walletUserId" class="form-label">Select User</label>
            <select class="form-select" id="walletUserId" name="user_id" required>
              <option value="">Choose user...</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="walletAmount" class="form-label">Amount (₹)</label>
            <input type="number" class="form-control" id="walletAmount" name="amount" min="1" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="walletReason" class="form-label">Reason</label>
            <select class="form-select" id="walletReason" name="reason" required>
              <option value="">Select reason...</option>
              <option value="admin_credit">Admin Credit</option>
              <option value="refund">Refund</option>
              <option value="bonus">Bonus</option>
              <option value="compensation">Compensation</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="walletRemarks" class="form-label">Remarks</label>
            <textarea class="form-control" id="walletRemarks" name="remarks" rows="3" placeholder="Enter any additional remarks..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add Credit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentDetailsModalLabel">Payment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="paymentDetailsBody">
        <!-- Payment details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" id="refundPaymentBtn">Refund Payment</button>
        <button type="button" class="btn btn-success" id="approvePaymentBtn">Approve Payment</button>
      </div>
    </div>
  </div>
</div>

<!-- Manual Payment Verification Modal -->
<div class="modal fade" id="manualPaymentModal" tabindex="-1" aria-labelledby="manualPaymentModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="manualPaymentModalLabel">Manual Payment Verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="manualPaymentForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="manualPaymentId" class="form-label">Payment ID</label>
            <input type="text" class="form-control" id="manualPaymentId" name="payment_id" required>
          </div>
          <div class="mb-3">
            <label for="manualOrderId" class="form-label">Order ID</label>
            <input type="text" class="form-control" id="manualOrderId" name="order_id" required>
          </div>
          <div class="mb-3">
            <label for="manualAmount" class="form-label">Amount (₹)</label>
            <input type="number" class="form-control" id="manualAmount" name="amount" min="1" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="manualRemarks" class="form-label">Admin Remarks</label>
            <textarea class="form-control" id="manualRemarks" name="remarks" rows="3" placeholder="Enter verification remarks..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Verify Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
let allUsers = [];
let allBookings = [];
let filteredBookings = [];
// Fetch and display user count
$(document).ready(function() {
    $.get('php/get_admin_stats.php', function(data) {
        renderDashboardSummaryCards(data);      // Top cards
        renderRevenueSummaryCards(data);        // Revenue tab summary cards
    });
    // Add delegated event listeners for summary card buttons, matching the revenue logic
    $('#dashboard-summary-cards').on('click', '#view-users-btn', function() {
        $('#users-tab').tab('show');
        setTimeout(function() {
            const usersPane = document.getElementById('users');
            if (usersPane) usersPane.scrollIntoView({ behavior: 'smooth' });
        }, 200);
    });
    $('#dashboard-summary-cards').on('click', '#view-messages-btn', function() {
        $('#messages-tab').tab('show');
        setTimeout(function() {
            const messagesPane = document.getElementById('messages');
            if (messagesPane) messagesPane.scrollIntoView({ behavior: 'smooth' });
        }, 200);
    });
    $('#dashboard-summary-cards').on('click', '#view-all-revenue-btn', function() {
        $('#revenue-tab').tab('show');
        setTimeout(function() {
            document.getElementById('revenue').scrollIntoView({behavior: 'smooth'});
        }, 200);
    });
    // Search filter
    $('#userSearch').on('input', function() {
        renderUsersTable($(this).val());
    });
    // Add User button
    $('#addUserBtn').on('click', function() {
        $('#addUserForm')[0].reset();
        $('#addUserModal').modal('show');
    });
    // Download CSV button
    $('#downloadUsersBtn').on('click', function() {
        downloadUsersCSV();
    });
    // Add event handler for View All Bookings button
    $('#dashboard-summary-cards').on('click', '#view-all-bookings-btn', function() {
        $('#bookings-tab').tab('show');
        setTimeout(function() {
            document.getElementById('bookings').scrollIntoView({behavior: 'smooth'});
        }, 200);
    });
});
function loadUsers() {
  $.get('php/get_admin_users.php', function(resp) {
    allUsers = resp.data || [];
    renderUsersTable($('#userSearch').val());
  });
}
function renderUsersTable(filter) {
  let html = '';
  let users = allUsers;
  if (filter) {
    const f = filter.toLowerCase();
    users = users.filter(u =>
      (u.username && u.username.toLowerCase().includes(f)) ||
      (u.name && u.name.toLowerCase().includes(f)) ||
      (u.email && u.email.toLowerCase().includes(f)) ||
      (u.mobile && u.mobile.toLowerCase().includes(f))
    );
  }
  if (users.length > 0) {
    users.forEach(function(user) {
      html += `<tr>
        <td>${user.id}</td>
        <td>${user.username}</td>
        <td>${user.name ? user.name : '-'}</td>
        <td>${user.email}</td>
        <td>${user.mobile ? user.mobile : '-'}</td>
        <td>${user.registered}</td>
        <td><span class="badge bg-${user.verified === 'Yes' ? 'success' : 'secondary'}">${user.verified}</span></td>
        <td><span class="badge bg-${user.mobile_verified === 'Yes' ? 'success' : 'secondary'}">${user.mobile_verified || 'No'}</span></td>
        <td><span class="badge bg-${user.is_admin === 'Yes' ? 'primary' : 'light text-dark'}">${user.is_admin}</span></td>
        <td>
          <button class="btn btn-sm btn-primary btn-edit-user me-1" data-id="${user.id}" title="Edit User"><i class="fas fa-edit"></i> Edit</button>
          <button class="btn btn-sm btn-warning btn-reset-password me-1" data-id="${user.id}" title="Reset Password"><i class="fas fa-key"></i> Reset PW</button>
          <button class="btn btn-sm btn-danger btn-delete-user" data-id="${user.id}" title="Delete User"><i class="fas fa-trash"></i> Delete</button>
        </td>
      </tr>`;
    });
  } else {
    html = '<tr><td colspan="10" class="text-center">No users found.</td></tr>';
  }
  $('#users-table tbody').html(html);
}
// Edit user
$(document).on('click', '.btn-edit-user', function() {
  const userId = $(this).data('id');
  const user = allUsers.find(u => u.id == userId);
  if (!user) return;
  $('#editUserId').val(user.id);
  $('#editUsername').val(user.username);
  $('#editName').val(user.name);
  $('#editEmail').val(user.email);
  $('#editMobile').val(user.mobile);
  $('#editEmailVerified').val(user.verified === 'Yes' ? '1' : '0');
  $('#editUserModal').modal('show');
});
$('#editUserForm').submit(function(e) {
  e.preventDefault();
  $.post('php/admin_edit_user.php', $(this).serialize(), function(resp) {
    if (resp.success) {
      $('#editUserModal').modal('hide');
      loadUsers();
    } else {
      alert('Failed to update user: ' + (resp.message || 'Unknown error'));
    }
  });
});
// Password reset
$(document).on('click', '.btn-reset-password', function() {
  const userId = $(this).data('id');
  $('#resetPwUserId').val(userId);
  $('#resetPassword').val('');
  $('#resetPwModal').modal('show');
});
$('#resetPwForm').submit(function(e) {
  e.preventDefault();
  $.post('php/admin_reset_password.php', $(this).serialize(), function(resp) {
    if (resp.success) {
      $('#resetPwModal').modal('hide');
      alert('Password reset successfully!');
    } else {
      alert('Failed to reset password: ' + (resp.message || 'Unknown error'));
    }
  });
});
// Delete user
$(document).on('click', '.btn-delete-user', function() {
  let userId = $(this).data('id');
  if (!userId) {
    // Fallback: get user ID from first <td> in the row
    userId = $(this).closest('tr').find('td').first().text().trim();
    console.log('[Fallback] Extracted userId from row:', userId);
  } else {
    console.log('Delete userId:', userId);
  }
  if (!userId) {
    alert('Invalid user ID!');
    return;
  }
  if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
  $.post('php/admin_delete_user.php', {user_id: userId}, function(resp) {
    if (resp.success) {
      loadUsers();
    } else {
      alert('Failed to delete user: ' + (resp.error || 'Unknown error'));
    }
  }).fail(function() {
    alert('Failed to communicate with the server. Please try again.');
  });
});
// Add User form submit
$('#addUserForm').submit(function(e) {
  e.preventDefault();
  $.post('php/admin_add_user.php', $(this).serialize(), function(resp) {
    if (resp.success) {
      $('#addUserModal').modal('hide');
      loadUsers();
    } else {
      alert('Failed to add user: ' + (resp.error || 'Unknown error'));
    }
  });
});
// Download users as CSV
function downloadUsersCSV() {
  if (!allUsers.length) {
    alert('No users to download!');
    return;
  }
  const headers = ['User ID','Username','Name','Email','Mobile','Registered','Email Verified','Mobile Verified','Admin'];
  const rows = allUsers.map(u => [
    u.id,
    u.username,
    u.name || '',
    u.email,
    u.mobile || '',
    u.registered,
    u.verified,
    u.mobile_verified || '',
    u.is_admin
  ]);
  let csv = '';
  csv += headers.join(',') + '\n';
  rows.forEach(r => {
    csv += r.map(x => '"' + (x ? String(x).replace(/"/g, '""') : '') + '"').join(',') + '\n';
  });
  const blob = new Blob([csv], {type: 'text/csv'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'users.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// Fetch and display contact messages in the Messages tab
let allMessages = [];
function loadAdminMessages() {
  $.get('php/get_admin_messages.php', function(resp) {
    allMessages = resp.data || [];
    renderMessagesTable();
  });
}
function renderMessagesTable() {
  let html = '';
  if (allMessages.length > 0) {
    allMessages.forEach(row => {
      html += `<tr>
        <td>${row.id}</td>
        <td>${row.name}</td>
        <td>${row.email}</td>
        <td>${row.phone || '-'}</td>
        <td>${row.subject || '-'}</td>
        <td>${row.message}</td>
        <td>${row.date}</td>
        <td><span class="badge bg-${row.status === 'New' ? 'info' : (row.status === 'Replied' ? 'success' : 'secondary')}">${row.status}</span></td>
        <td class="table-actions">
          <button class="btn btn-sm btn-primary reply-message-btn me-1" data-id="${row.id}" title="Reply"><i class="fas fa-reply"></i> Reply</button>
          <button class="btn btn-sm btn-success mark-read-btn me-1" data-id="${row.id}" title="Mark as Read"><i class="fas fa-check"></i> Mark Read</button>
          <button class="btn btn-sm btn-danger delete-message-btn" data-id="${row.id}" title="Delete"><i class="fas fa-trash"></i> Delete</button>
        </td>
      </tr>`;
    });
  } else {
    html = '<tr><td colspan="9" class="text-center">No messages found.</td></tr>';
  }
  $('#messages-table tbody').html(html);
}
// Load messages when Messages tab is shown
$('button[data-bs-target="#messages"]').on('shown.bs.tab', loadAdminMessages);
if ($('#messages').hasClass('active')) loadAdminMessages();

// Load users when Users tab is shown
$('button[data-bs-target="#users"]').on('shown.bs.tab', loadUsers);
if ($('#users').hasClass('active')) loadUsers();

// Export messages as CSV
$('#export-messages').on('click', function() {
  if (!allMessages.length) {
    alert('No messages to export!');
    return;
  }
  const headers = ['ID','Name','Email','Phone','Subject','Message','Date','Status'];
  const rows = allMessages.map(m => [
    m.id, m.name, m.email, m.phone || '', m.subject || '', m.message, m.date, m.status
  ]);
  let csv = '';
  csv += headers.join(',') + '\n';
  rows.forEach(r => {
    csv += r.map(x => '"' + (x ? String(x).replace(/"/g, '""') : '') + '"').join(',') + '\n';
  });
  const blob = new Blob([csv], {type: 'text/csv'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'contact_messages.csv';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
});
// Reply, Mark as Read, Delete actions (AJAX handlers to be implemented)
$(document).on('click', '.reply-message-btn', function() {
  const msgId = $(this).data('id');
  const msg = allMessages.find(m => m.id == msgId);
  if (!msg) return;
  $('#replyMessageId').val(msg.id);
  $('#replyToEmail').val(msg.email);
  $('#replySubject').val('Re: ' + (msg.subject || ''));
  $('#replyBody').val('');
  $('#replyMessageModal').modal('show');
});
$('#replyMessageForm').submit(function(e) {
  e.preventDefault();
  $.post('php/admin_reply_message.php', $(this).serialize(), function(resp) {
    if (resp.success) {
      alert('Reply sent!');
      $('#replyMessageModal').modal('hide');
      loadAdminMessages();
    } else {
      alert('Failed to send reply: ' + (resp.error || 'Unknown error'));
    }
  });
});
$(document).on('click', '.mark-read-btn', function() {
  const msgId = $(this).data('id');
  $.post('php/admin_mark_message_read.php', {message_id: msgId}, function(resp) {
    if (resp.success) {
      alert('Marked as read!');
      loadAdminMessages();
    } else {
      alert('Failed to mark as read: ' + (resp.error || 'Unknown error'));
    }
  });
});
$(document).on('click', '.delete-message-btn', function() {
  const msgId = $(this).data('id');
  if (!confirm('Are you sure you want to delete this message?')) return;
  $.post('php/admin_delete_message.php', {message_id: msgId}, function(resp) {
    if (resp.success) {
      alert('Message deleted!');
      loadAdminMessages();
    } else {
      alert('Failed to delete message: ' + (resp.error || 'Unknown error'));
    }
  });
});

// Revenue Tab Logic
let revenueData = [];
let filteredRevenueData = [];
function loadRevenueData() {
  $.get('php/get_admin_revenue.php', function(resp) {
    if (!resp || !resp.data) return;
    revenueData = resp.data;
    // Populate destination dropdown
    const destinations = Array.from(new Set(revenueData.map(r => r.destination).filter(Boolean)));
    let destOptions = '<option value="">All</option>';
    destinations.forEach(d => { destOptions += `<option value="${d}">${d}</option>`; });
    $('#revenueDestination').html(destOptions);
    // Summary cards
    const s = resp.summary;
    let cards = '';
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-success'><div class='card-body d-flex align-items-center'><span class='stat-icon text-success'><i class='fas fa-rupee-sign'></i></span><div><div class='stat-value'>₹${s.total_revenue.toLocaleString()}</div><div class='stat-label'>Total Revenue</div></div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-primary'><div class='card-body d-flex align-items-center'><span class='stat-icon text-primary'><i class='fas fa-ticket-alt'></i></span><div><div class='stat-value'>${s.total_bookings}</div><div class='stat-label'>Total Bookings</div></div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-info'><div class='card-body d-flex align-items-center'><span class='stat-icon text-info'><i class='fas fa-chart-line'></i></span><div><div class='stat-value'>₹${s.month_revenue.toLocaleString()}</div><div class='stat-label'>This Month's Revenue</div></div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-warning'><div class='card-body d-flex align-items-center'><span class='stat-icon text-warning'><i class='fas fa-calendar-alt'></i></span><div><div class='stat-value'>${s.month_bookings}</div><div class='stat-label'>This Month's Bookings</div></div></div></div></div>`;
    $('#revenue-summary-cards').html(cards);
    applyRevenueFilters();
  });
}
function applyRevenueFilters() {
  const search = ($('#revenueSearch').val() || '').toLowerCase();
  const minAmount = parseFloat($('#revenueMinAmount').val()) || null;
  const maxAmount = parseFloat($('#revenueMaxAmount').val()) || null;
  const fromDate = $('#revenueFromDate').val();
  const toDate = $('#revenueToDate').val();
  const destination = $('#revenueDestination').val();
  filteredRevenueData = revenueData.filter(row => {
    // Search
    let match = true;
    if (search) {
      match = (
        (row.booking_id && row.booking_id.toString().toLowerCase().includes(search)) ||
        (row.razorpay_payment_id && row.razorpay_payment_id.toLowerCase().includes(search)) ||
        (row.destination && row.destination.toLowerCase().includes(search))
      );
    }
    if (!match) return false;
    // Amount
    const amt = parseFloat(row.amount);
    if (minAmount !== null && amt < minAmount) return false;
    if (maxAmount !== null && amt > maxAmount) return false;
    // Date
    if (fromDate && (!row.booking_date || row.booking_date < fromDate)) return false;
    if (toDate && (!row.booking_date || row.booking_date > toDate)) return false;
    // Destination
    if (destination && row.destination !== destination) return false;
    return true;
  });
  renderRevenueTable();
}
function renderRevenueTable() {
  let html = '';
  filteredRevenueData.forEach(row => {
    html += `<tr>
      <td>${row.booking_id}</td>
      <td>${row.booking_date ? new Date(row.booking_date).toLocaleString() : '-'}</td>
      <td>₹${parseFloat(row.amount).toLocaleString()}</td>
      <td>${row.destination || '-'}</td>
      <td>${row.razorpay_payment_id || '-'}</td>
      <td><button class='btn btn-sm btn-outline-info view-revenue-details' data-id='${row.booking_id}' data-payment='${row.razorpay_payment_id}'>View</button></td>
    </tr>`;
  });
  $('#revenue-table tbody').html(html);
}
// Filter events
$('#revenueSearch, #revenueMinAmount, #revenueMaxAmount, #revenueFromDate, #revenueToDate, #revenueDestination').on('input change', applyRevenueFilters);
// Show details modal
$(document).on('click', '.view-revenue-details', function() {
  const bookingId = $(this).data('id');
  const paymentId = $(this).data('payment');
  const row = revenueData.find(r => r.booking_id == bookingId);
  let html = '';
  if (row) {
    html += `<p><strong>Booking ID:</strong> ${row.booking_id}</p>`;
    html += `<p><strong>Booking Date:</strong> ${row.booking_date ? new Date(row.booking_date).toLocaleString() : '-'}</p>`;
    html += `<p><strong>Amount:</strong> ₹${parseFloat(row.amount).toLocaleString()}</p>`;
    html += `<p><strong>Destination:</strong> ${row.destination || '-'}</p>`;
    html += `<p><strong>Razorpay Payment ID:</strong> ${row.razorpay_payment_id || '-'}</p>`;
    html += `<p><strong>Payment Date:</strong> ${row.payment_date ? new Date(row.payment_date).toLocaleString() : '-'}</p>`;
  } else {
    html = '<p>No details found.</p>';
  }
  $('#revenueDetailsBody').html(html);
  $('#revenueDetailsModal').modal('show');
});
// Load revenue data when Revenue tab is shown
$('button[data-bs-target="#revenue"]').on('shown.bs.tab', loadRevenueData);
// Optionally, load on page load if tab is active
if ($('#revenue').hasClass('active')) loadRevenueData();

// Render all four summary cards with unified style and layout
function renderDashboardSummaryCards(stats) {
  const cards = [
    {
      id: 'user-summary-card',
      border: 'primary',
      icon: 'fa-users',
      iconColor: 'text-primary',
      value: stats.total_users,
      label: 'Registered Users',
      btnId: 'view-users-btn',
      btnClass: 'btn-outline-primary',
      btnText: 'View All Users'
    },
    {
      id: 'message-summary-card',
      border: 'warning',
      icon: 'fa-envelope',
      iconColor: 'text-warning',
      value: stats.total_messages,
      label: 'Contact Messages',
      btnId: 'view-messages-btn',
      btnClass: 'btn-outline-warning',
      btnText: 'View All Messages'
    },
    {
      id: 'revenue-summary-card',
      border: 'success',
      icon: 'fa-rupee-sign',
      iconColor: 'text-success',
      value: `₹${stats.total_revenue ? stats.total_revenue.toLocaleString() : '0'}`,
      label: 'Total Revenue',
      btnId: 'view-all-revenue-btn',
      btnClass: 'btn-outline-success',
      btnText: 'View All Revenue'
    },
    {
      id: 'booking-summary-card',
      border: 'purple',
      icon: 'fa-ticket',
      iconColor: 'text-purple',
      value: stats.total_bookings,
      label: 'Total Bookings',
      btnId: 'view-all-bookings-btn',
      btnClass: 'btn-outline-purple',
      btnText: 'View All Bookings'
    }
  ];
  cards.forEach(card => {
    $('#' + card.id).html(`
      <div class='card dashboard-card border-${card.border} h-100'>
        <div class='card-body d-flex flex-column align-items-center justify-content-center h-100 text-center'>
          <div class='icon-value-group mb-2'>
            <span class='stat-icon ${card.iconColor}'><i class='fas ${card.icon} fa-3x'></i></span>
            <span class="stat-value">${card.value}</span>
          </div>
          <div class='stat-label mb-3'>${card.label}</div>
          <button class='btn ${card.btnClass} btn-sm mt-auto' id='${card.btnId}'>${card.btnText}</button>
        </div>
      </div>`);
  });
}

// Render Revenue tab summary cards using unified stats
function renderRevenueSummaryCards(stats) {
    let cards = '';
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-success'><div class='card-body d-flex flex-column align-items-center justify-content-center h-100'><span class='stat-icon text-success'><i class='fas fa-rupee-sign'></i></span><div class='stat-value mb-1'>₹${stats.total_revenue.toLocaleString()}</div><div class='stat-label mb-3'>Total Revenue</div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-primary'><div class='card-body d-flex flex-column align-items-center justify-content-center h-100'><span class='stat-icon text-primary'><i class='fas fa-ticket-alt'></i></span><div class='stat-value mb-1'>${stats.total_bookings}</div><div class='stat-label mb-3'>Total Bookings</div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-info'><div class='card-body d-flex flex-column align-items-center justify-content-center h-100'><span class='stat-icon text-info'><i class='fas fa-chart-line'></i></span><div class='stat-value mb-1'>₹${stats.month_revenue.toLocaleString()}</div><div class='stat-label mb-3'>This Month's Revenue</div></div></div></div>`;
    cards += `<div class='col-md-3 mb-3'><div class='card dashboard-card border-warning'><div class='card-body d-flex flex-column align-items-center justify-content-center h-100'><span class='stat-icon text-warning'><i class='fas fa-calendar-alt'></i></span><div class='stat-value mb-1'>${stats.month_bookings}</div><div class='stat-label mb-3'>This Month's Bookings</div></div></div></div>`;
    $('#revenue-summary-cards').html(cards);
}

// In the JS section, add the export logic after filteredRevenueData and renderRevenueTable are defined:
$('#export-revenue').on('click', function() {
    if (!filteredRevenueData.length) {
        alert('No revenue data to export!');
        return;
    }
    const headers = ['Booking ID', 'Booking Date', 'Amount', 'Destination', 'Razorpay Payment ID', 'Payment Date'];
    const rows = filteredRevenueData.map(row => [
        row.booking_id,
        row.booking_date ? new Date(row.booking_date).toLocaleString() : '',
        row.amount,
        row.destination || '',
        row.razorpay_payment_id || '',
        row.payment_date ? new Date(row.payment_date).toLocaleString() : ''
    ]);
    let csv = '';
    csv += headers.join(',') + '\n';
    rows.forEach(r => {
        csv += r.map(x => '"' + (x ? String(x).replace(/"/g, '""') : '') + '"').join(',') + '\n';
    });
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'revenue.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});

function loadBookings() {
    $.get('php/get_admin_bookings.php', function(resp) {
        allBookings = resp.data || [];
        filteredBookings = allBookings;
        renderBookingsTable();
        // Sync the summary card with the actual count
        $('#booking-summary-card .stat-value').text(allBookings.length);
    });
}
function renderBookingsTable() {
    let html = '';
    filteredBookings.forEach(row => {
        html += `<tr>
            <td><strong>${row.booking_id}</strong></td>
            <td>${row.user}</td>
            <td><span class="badge bg-info">${row.category}</span></td>
            <td>${row.name}</td>
            <td>${row.from || '-'}</td>
            <td>${row.to || '-'}</td>
            <td>${row.dates}</td>
            <td><span class="badge bg-secondary">${row.travelers}</span></td>
            <td><span class="badge bg-${row.status === 'completed' ? 'success' : (row.status === 'pending' ? 'warning' : 'danger')}">${row.status}</span></td>
            <td><span class="badge bg-${row.payment_status === 'completed' ? 'success' : (row.payment_status === 'pending' ? 'warning' : 'danger')}">${row.payment_status}</span></td>
            <td><strong>₹${row.amount}</strong></td>
            <td>
                <a href="php/view_ticket_details.php?booking_id=${row.booking_id}" target="_blank" class="btn btn-success btn-sm">View Ticket</a>
                <!-- Add verify/confirm button for pending/unpaid bookings -->
                ${row.payment_status === 'pending' || row.status === 'pending' ? `<button class="btn btn-warning btn-sm ms-2 verify-payment-btn" data-id="${row.booking_id}">Verify/Confirm</button>` : ''}
            </td>
            <td><button class="btn btn-outline-info btn-sm view-traveller-details" data-id="${row.id}">View</button></td>
        </tr>`;
    });
    $('#bookings-table tbody').html(html);
}
// Show traveller details modal
$(document).on('click', '.view-traveller-details', function() {
    const bookingId = $(this).data('id');
    const row = allBookings.find(b => b.id == bookingId);
    if (!row) return;
    let html = '';
    html += `<div class='row'><div class='col-md-6'><strong>Booking ID:</strong> ${row.booking_id}</div><div class='col-md-6'><strong>User:</strong> ${row.user}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>Type:</strong> ${row.category}</div><div class='col-md-6'><strong>Name:</strong> ${row.name}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>From:</strong> ${row.from || '-'}</div><div class='col-md-6'><strong>To:</strong> ${row.to || '-'}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>Dates:</strong> ${row.dates}</div><div class='col-md-6'><strong>Travellers:</strong> ${row.travelers}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>Status:</strong> ${row.status}</div><div class='col-md-6'><strong>Payment Status:</strong> ${row.payment_status}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>Payment Mode:</strong> ${row.payment_mode || '-'}</div><div class='col-md-6'><strong>Amount:</strong> ₹${row.amount}</div></div>`;
    html += `<div class='row'><div class='col-md-6'><strong>Razorpay Payment ID:</strong> ${row.razorpay_payment_id || '-'}</div><div class='col-md-6'><strong>Created At:</strong> ${row.created_at}</div></div>`;
    // Add more details as needed (email verified, transport, etc.)
    html += `<div class='row'><div class='col-md-12 mt-2'><a href="php/view_ticket_details.php?booking_id=${row.booking_id}" class="btn btn-outline-success btn-sm" target="_blank">View/Download Ticket</a></div></div>`;
    $('#travellerDetailsBody').html(html);
    $('#travellerDetailsModal').modal('show');
});
// Export bookings as CSV
$('#export-bookings').on('click', function() {
    if (!allBookings.length) {
        alert('No bookings to export!');
        return;
    }
    const headers = ['Booking ID','User','Type','Name','Dates','Travellers','Status','Payment','Amount','Razorpay Payment ID','Created At'];
    const rows = allBookings.map(row => [
        row.booking_id,
        row.user,
        row.category,
        row.name,
        row.dates,
        row.travelers,
        row.status,
        row.payment_status,
        row.amount,
        row.razorpay_payment_id,
        row.created_at
    ]);
    let csv = '';
    csv += headers.join(',') + '\n';
    rows.forEach(r => {
        csv += r.map(x => '"' + (x ? String(x).replace(/"/g, '""') : '') + '"').join(',') + '\n';
    });
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'bookings.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});
// Remove loadBookings() from $(document).ready()
// Instead, load bookings only when the Bookings tab is shown
$('button[data-bs-target="#bookings"]').on('shown.bs.tab', function() {
    $('#bookings-table tbody').html('<tr><td colspan="14" class="text-center">Loading...</td></tr>');
    loadBookings();
});
// Optionally, load on page load if Bookings tab is active
if ($('#bookings').hasClass('active')) {
    $('#bookings-table tbody').html('<tr><td colspan="14" class="text-center">Loading...</td></tr>');
    loadBookings();
}
// In the Bookings tab, add a search input above the table
$('#bookingSearch').on('input', function() {
    const filter = $(this).val().toLowerCase();
    filteredBookings = allBookings.filter(row =>
        (row.booking_id && row.booking_id.toLowerCase().includes(filter)) ||
        (row.user && row.user.toLowerCase().includes(filter)) ||
        (row.name && row.name.toLowerCase().includes(filter)) ||
        (row.status && row.status.toLowerCase().includes(filter))
    );
    renderBookingsTable();
    // Also update the summary card to match filtered count if you want
    // $('#booking-summary-card .stat-value').text(filteredBookings.length);
});

// ===== PAYMENTS TAB FUNCTIONALITY =====
let allPayments = [];
let filteredPayments = [];
let allWalletBalances = [];

// Load payments data
function loadPaymentsData() {
    $.get('php/get_admin_payments_data.php', function(resp) {
        if (!resp || !resp.data) return;
        allPayments = resp.data.payments || [];
        allWalletBalances = resp.data.wallets || [];
        
        // Update summary cards
        updatePaymentSummaryCards(resp.summary);
        
        // Load wallet balances
        loadWalletBalances();
        
        // Apply filters
        applyPaymentFilters();
    });
}

// Update payment summary cards
function updatePaymentSummaryCards(summary) {
    $('#total-wallet-balance').text('₹' + (summary.total_wallet || 0).toLocaleString());
    $('#completed-payments').text(summary.completed_payments || 0);
    $('#pending-payments').text(summary.pending_payments || 0);
    $('#failed-payments').text(summary.failed_payments || 0);
}

// Load wallet balances
function loadWalletBalances() {
    let html = '';
    allWalletBalances.forEach(wallet => {
        html += `<tr>
            <td>${wallet.username}</td>
            <td><strong>₹${parseFloat(wallet.balance || 0).toLocaleString()}</strong></td>
            <td>${wallet.last_updated || '-'}</td>
            <td>
                <button class="btn btn-sm btn-primary manage-wallet-btn" data-user-id="${wallet.user_id}" data-username="${wallet.username}">
                    <i class="fas fa-edit"></i> Manage
                </button>
            </td>
        </tr>`;
    });
    $('#wallet-balances-table tbody').html(html);
}

// Apply payment filters
function applyPaymentFilters() {
    const search = ($('#paymentSearch').val() || '').toLowerCase();
    const status = $('#paymentStatusFilter').val();
    const type = $('#paymentTypeFilter').val();
    const fromDate = $('#paymentFromDate').val();
    const toDate = $('#paymentToDate').val();
    
    filteredPayments = allPayments.filter(payment => {
        // Search filter
        if (search) {
            const searchMatch = (
                (payment.payment_id && payment.payment_id.toLowerCase().includes(search)) ||
                (payment.username && payment.username.toLowerCase().includes(search)) ||
                (payment.user_email && payment.user_email.toLowerCase().includes(search))
            );
            if (!searchMatch) return false;
        }
        
        // Status filter
        if (status && payment.status !== status) return false;
        
        // Type filter
        if (type && payment.payment_type !== type) return false;
        
        // Date filter
        if (fromDate && (!payment.created_at || payment.created_at < fromDate)) return false;
        if (toDate && (!payment.created_at || payment.created_at > toDate)) return false;
        
        return true;
    });
    
    renderPaymentsTable();
}

// Render payments table
function renderPaymentsTable() {
    let html = '';
    filteredPayments.forEach(payment => {
        const statusBadge = payment.status === 'completed' ? 'success' : 
                           payment.status === 'pending' ? 'warning' : 'danger';
        
        html += `<tr>
            <td><strong>${payment.payment_id || '-'}</strong></td>
            <td>
                ${payment.username}<br>
                <small class="text-muted">${payment.user_email || '-'}</small>
            </td>
            <td><span class="badge bg-info">${payment.payment_type || '-'}</span></td>
            <td><strong>₹${parseFloat(payment.amount || 0).toLocaleString()}</strong></td>
            <td><span class="badge bg-${statusBadge}">${payment.status || '-'}</span></td>
            <td>${payment.payment_method || '-'}</td>
            <td>${payment.created_at ? new Date(payment.created_at).toLocaleString() : '-'}</td>
            <td>${payment.reference || '-'}</td>
            <td>
                <button class="btn btn-sm btn-info view-payment-details me-1" data-id="${payment.id}">
                    <i class="fas fa-eye"></i> View
                </button>
                ${payment.status === 'pending' ? 
                    `<button class="btn btn-sm btn-success verify-payment-manual me-1" data-id="${payment.id}">
                        <i class="fas fa-check"></i> Verify
                    </button>` : ''
                }
                ${payment.status === 'completed' ? 
                    `<button class="btn btn-sm btn-warning refund-payment-btn" data-id="${payment.id}">
                        <i class="fas fa-undo"></i> Refund
                    </button>` : ''
                }
            </td>
        </tr>`;
    });
    
    if (filteredPayments.length === 0) {
        html = '<tr><td colspan="9" class="text-center">No payments found.</td></tr>';
    }
    
    $('#payments-table tbody').html(html);
}

// Payment filter events
$('#paymentSearch, #paymentStatusFilter, #paymentTypeFilter, #paymentFromDate, #paymentToDate').on('input change', applyPaymentFilters);

// Clear payment filters
$('#clearPaymentFilters').on('click', function() {
    $('#paymentSearch').val('');
    $('#paymentStatusFilter').val('');
    $('#paymentTypeFilter').val('');
    $('#paymentFromDate').val('');
    $('#paymentToDate').val('');
    applyPaymentFilters();
});

// Add wallet credit button
$('#add-wallet-credit-btn').on('click', function() {
    // Populate user dropdown
    let userOptions = '<option value="">Choose user...</option>';
    allWalletBalances.forEach(wallet => {
        userOptions += `<option value="${wallet.user_id}">${wallet.username} (₹${parseFloat(wallet.balance || 0).toLocaleString()})</option>`;
    });
    $('#walletUserId').html(userOptions);
    $('#addWalletCreditModal').modal('show');
});

// Add wallet credit form submit
$('#addWalletCreditForm').on('submit', function(e) {
    e.preventDefault();
    $.post('php/admin_add_wallet_credit.php', $(this).serialize(), function(resp) {
        if (resp.success) {
            $('#addWalletCreditModal').modal('hide');
            alert('Wallet credit added successfully!');
            loadPaymentsData(); // Reload data
        } else {
            alert('Failed to add wallet credit: ' + (resp.message || 'Unknown error'));
        }
    });
});

// View payment details
$(document).on('click', '.view-payment-details', function() {
    const paymentId = $(this).data('id');
    $.get('php/get_payment_details.php', { payment_id: paymentId }, function(data) {
        $('#paymentDetailsBody').html(data);
        $('#paymentDetailsModal').modal('show');
        $('#paymentDetailsModal').data('payment-id', paymentId);
    });
});

// Manual payment verification
$(document).on('click', '.verify-payment-manual', function() {
    const paymentId = $(this).data('id');
    const payment = allPayments.find(p => p.id == paymentId);
    if (payment) {
        $('#manualPaymentId').val(payment.payment_id || '');
        $('#manualOrderId').val(payment.order_id || '');
        $('#manualAmount').val(payment.amount || '');
        $('#manualPaymentModal').modal('show');
        $('#manualPaymentModal').data('payment-id', paymentId);
    }
});

// Manual payment verification form submit
$('#manualPaymentForm').on('submit', function(e) {
    e.preventDefault();
    const paymentId = $('#manualPaymentModal').data('payment-id');
    const formData = $(this).serialize() + '&payment_record_id=' + paymentId;
    
    $.post('php/admin_verify_payment_manual.php', formData, function(resp) {
        if (resp.success) {
            $('#manualPaymentModal').modal('hide');
            alert('Payment verified successfully!');
            loadPaymentsData(); // Reload data
        } else {
            alert('Failed to verify payment: ' + (resp.message || 'Unknown error'));
        }
    });
});

// Refund payment
$(document).on('click', '.refund-payment-btn', function() {
    const paymentId = $(this).data('id');
    if (confirm('Are you sure you want to refund this payment?')) {
        $.post('php/admin_refund_payment.php', { payment_id: paymentId }, function(resp) {
            if (resp.success) {
                alert('Payment refunded successfully!');
                loadPaymentsData(); // Reload data
            } else {
                alert('Failed to refund payment: ' + (resp.message || 'Unknown error'));
            }
        });
    }
});

// Manage wallet button
$(document).on('click', '.manage-wallet-btn', function() {
    const userId = $(this).data('user-id');
    const username = $(this).data('username');
    $('#walletUserId').val(userId);
    $('#addWalletCreditModalLabel').text('Manage Wallet - ' + username);
    $('#addWalletCreditModal').modal('show');
});

// Export payments
$('#export-payments').on('click', function() {
    if (!filteredPayments.length) {
        alert('No payments to export!');
        return;
    }
    
    const headers = ['Payment ID', 'User', 'Email', 'Type', 'Amount', 'Status', 'Payment Method', 'Date', 'Reference'];
    const rows = filteredPayments.map(payment => [
        payment.payment_id || '',
        payment.username || '',
        payment.user_email || '',
        payment.payment_type || '',
        payment.amount || '',
        payment.status || '',
        payment.payment_method || '',
        payment.created_at ? new Date(payment.created_at).toLocaleString() : '',
        payment.reference || ''
    ]);
    
    let csv = headers.join(',') + '\n';
    rows.forEach(row => {
        csv += row.map(x => '"' + (x ? String(x).replace(/"/g, '""') : '') + '"').join(',') + '\n';
    });
    
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'payments.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
});

// Load payments when Payments tab is shown
$('button[data-bs-target="#payments"]').on('shown.bs.tab', function() {
    $('#payments-table tbody').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
    loadPaymentsData();
});

// Optionally, load on page load if Payments tab is active
if ($('#payments').hasClass('active')) {
    $('#payments-table tbody').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
    loadPaymentsData();
}
</script>
</body>
</html>
