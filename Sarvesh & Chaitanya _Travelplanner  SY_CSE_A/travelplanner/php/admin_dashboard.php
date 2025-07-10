<?php
session_start();
require_once 'config.php';
require_once 'session.php';
if (!isAdmin()) {
    header('Location: admin_login.php');
    exit();
}
// Fetch wallet balance for the logged-in admin
$wallet_balance = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($wallet_balance);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TravelPlanner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .dashboard-card { min-width: 180px; margin-bottom: 20px; }
        .nav-tabs .nav-link.active { background: #0077cc; color: #fff !important; }
        .nav-tabs .nav-link { color: #0077cc; font-weight: 600; }
        .table-actions button { margin-right: 5px; }
        .modal-header { background: #0077cc; color: #fff; }
        .stat-icon { font-size: 2rem; margin-right: 10px; }
        .stat-card { display: flex; align-items: center; }
        .stat-value { font-size: 1.5rem; font-weight: bold; }
        .stat-label { color: #666; }
        .wallet-balance {
            position: absolute;
            top: 20px;
            right: 30px;
            background: #fff;
            border: 2px solid #0077cc;
            color: #0077cc;
            border-radius: 20px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px #0077cc22;
            z-index: 1000;
        }
        .wallet-balance i { margin-right: 7px; }
    </style>
</head>
<body>
<div class="wallet-balance"><i class="fas fa-wallet"></i> Wallet: ₹<?php echo number_format($wallet_balance, 2); ?></div>
<div class="container-fluid py-4">
    <h1 class="mb-4"><i class="fas fa-user-shield"></i> Admin Dashboard</h1>
    <!-- Stats Row -->
    <div class="row" id="dashboard-stats">
        <!-- Stats will be loaded here by JS -->
    </div>
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-dashboard" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">Dashboard</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-bookings" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">Bookings</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-payments" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">Payments</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-users" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-messages" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab">Contact Messages</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-manual" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">Manual Actions</button>
        </li>
    </ul>
    <div class="tab-content" id="adminTabContent">
        <!-- Dashboard Tab -->
        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
            <div class="row" id="dashboard-analytics">
                <!-- Analytics/Charts can go here -->
            </div>
        </div>
        <!-- Bookings Tab -->
        <div class="tab-pane fade" id="bookings" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4><i class="fas fa-ticket-alt"></i> Bookings</h4>
                <button class="btn btn-outline-primary btn-sm" id="export-bookings"><i class="fas fa-file-export"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="bookings-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>User</th><th>Category</th><th>Name</th><th>Dates</th><th>Travelers</th><th>Status</th><th>Payment</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody><!-- Data loaded by JS --></tbody>
                </table>
            </div>
        </div>
        <!-- Payments Tab -->
        <div class="tab-pane fade" id="payments" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4><i class="fas fa-credit-card"></i> Payments</h4>
                <button class="btn btn-outline-primary btn-sm" id="export-payments"><i class="fas fa-file-export"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="payments-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Booking</th><th>User</th><th>Amount</th><th>Status</th><th>Date</th><th>Method</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody><!-- Data loaded by JS --></tbody>
                </table>
            </div>
        </div>
        <!-- Users Tab -->
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4><i class="fas fa-users"></i> Users</h4>
                <div>
                  <button class="btn btn-success btn-sm" id="addUserBtn"><i class="fas fa-user-plus"></i> Add User</button>
                  <button class="btn btn-outline-primary btn-sm" id="export-users"><i class="fas fa-file-export"></i> Export</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="users-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Mobile</th><th>Registered</th><th>Verified</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody><!-- Data loaded by JS --></tbody>
                </table>
            </div>
        </div>
        <!-- Contact Messages Tab -->
        <div class="tab-pane fade" id="messages" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4><i class="fas fa-envelope"></i> Contact Messages</h4>
                <button class="btn btn-outline-primary btn-sm" id="export-messages"><i class="fas fa-file-export"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="messages-table">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Name</th><th>Email</th><th>Message</th><th>Date</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody><!-- Data loaded by JS --></tbody>
                </table>
            </div>
        </div>
        <!-- Manual Actions Tab -->
        <div class="tab-pane fade" id="manual" role="tabpanel">
            <div class="mb-3">
                <h4><i class="fas fa-tools"></i> Manual Actions</h4>
                <p>Payment verification, resend ticket, etc. (Coming soon)</p>
            </div>
        </div>
    </div>
</div>
<!-- Modals for actions (edit user, reply, view ticket, etc.) will be added here by JS or backend -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ticketModalLabel">Booking Ticket</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="ticketModalBody">
        <div class="text-center"><div class="spinner-border"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a id="downloadTicketBtn" class="btn btn-primary" href="#" download style="display:none;">Download PDF</a>
      </div>
    </div>
  </div>
</div>
<!-- User Edit Modal -->
<div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userEditModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="userEditForm">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- User Reset Password Modal -->
<div class="modal fade" id="userResetPasswordModal" tabindex="-1" aria-labelledby="userResetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userResetPasswordModalLabel">Reset Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="userResetPasswordForm">
        <div class="modal-body">
          <input type="hidden" name="user_id" id="resetUserId">
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
<!-- User Add Modal -->
<div class="modal fade" id="userAddModal" tabindex="-1" aria-labelledby="userAddModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="userAddModalLabel">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="userAddForm">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Add User</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// Fetch and display dashboard stats
$(document).ready(function() {
    $.get('php/get_admin_stats.php', function(data) {
        const stats = [
            { label: 'Bookings', value: data.total_bookings, icon: 'fa-ticket-alt', color: 'primary' },
            { label: 'Revenue', value: '₹' + (data.total_revenue || 0).toLocaleString(), icon: 'fa-rupee-sign', color: 'success' },
            { label: 'Users', value: data.total_users, icon: 'fa-users', color: 'info' },
            { label: 'Contact Messages', value: data.total_messages, icon: 'fa-envelope', color: 'warning' },
            { label: 'Plans Generated', value: data.total_plans, icon: 'fa-map-marked-alt', color: 'secondary' },
            { label: 'Ticket Downloads', value: data.total_downloads, icon: 'fa-file-download', color: 'dark' },
            { label: 'Visitors', value: data.total_visitors, icon: 'fa-eye', color: 'danger' }
        ];
        let html = '';
        stats.forEach(stat => {
            html += `<div class="col-md-3 col-sm-6 mb-3">
                <div class="card dashboard-card border-${stat.color}">
                    <div class="card-body stat-card">
                        <span class="stat-icon text-${stat.color}"><i class="fas ${stat.icon}"></i></span>
                        <div>
                            <div class="stat-value">${stat.value}</div>
                            <div class="stat-label">${stat.label}</div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        $('#dashboard-stats').html(html);
    });
});
// Fetch and display bookings in the Bookings tab
function loadAdminBookings() {
    $.get('php/get_admin_bookings.php', function(resp) {
        let html = '';
        resp.data.forEach(row => {
            html += `<tr>
                <td>${row.booking_id}</td>
                <td>${row.user}</td>
                <td>${row.category}</td>
                <td>${row.name}</td>
                <td>${row.dates}</td>
                <td>${row.travelers}</td>
                <td><span class="badge bg-${row.status === 'confirmed' ? 'success' : (row.status === 'cancelled' ? 'danger' : 'secondary')}">${row.status}</span></td>
                <td><span class="badge bg-${row.payment_status === 'completed' || row.payment_status === 'paid' ? 'success' : (row.payment_status === 'failed' ? 'danger' : 'warning')}">${row.payment_status || '-'}</span></td>
                <td class="table-actions">
                    <button class="btn btn-sm btn-info view-ticket-btn" data-id="${row.id}"><i class="fas fa-eye"></i> View</button>
                    <button class="btn btn-sm btn-primary download-ticket-btn" data-id="${row.id}"><i class="fas fa-download"></i> Download</button>
                    <button class="btn btn-sm btn-secondary resend-ticket-btn" data-id="${row.id}"><i class="fas fa-paper-plane"></i> Resend</button>
                </td>
            </tr>`;
        });
        $('#bookings-table tbody').html(html);
    });
}
// Load bookings when Bookings tab is shown
$('button[data-bs-target="#bookings"]').on('shown.bs.tab', loadAdminBookings);
// Optionally, load on page load if tab is active
if ($('#bookings').hasClass('active')) loadAdminBookings();
// Fetch and display payments in the Payments tab
function loadAdminPayments() {
    $.get('php/get_admin_payments.php', function(resp) {
        let html = '';
        resp.data.forEach(row => {
            html += `<tr>
                <td>${row.payment_id || '-'}</td>
                <td>${row.booking_id || '-'}</td>
                <td>${row.user}</td>
                <td>₹${row.amount ? Number(row.amount).toLocaleString() : '-'}</td>
                <td><span class="badge bg-${row.status === 'completed' ? 'success' : (row.status === 'failed' ? 'danger' : (row.status === 'pending' ? 'warning' : 'secondary'))}">${row.status || '-'}</span></td>
                <td>${row.date || '-'}</td>
                <td>${row.method || '-'}</td>
                <td class="table-actions">
                    <button class="btn btn-sm btn-info view-payment-btn" data-id="${row.id}"><i class="fas fa-eye"></i> View</button>
                    <button class="btn btn-sm btn-success verify-payment-btn" data-id="${row.id}"><i class="fas fa-check"></i> Verify</button>
                </td>
            </tr>`;
        });
        $('#payments-table tbody').html(html);
    });
}
// Load payments when Payments tab is shown
$('button[data-bs-target="#payments"]').on('shown.bs.tab', loadAdminPayments);
// Optionally, load on page load if tab is active
if ($('#payments').hasClass('active')) loadAdminPayments();
// Fetch and display users in the Users tab
function loadAdminUsers() {
    $.get('php/get_admin_users.php', function(resp) {
        let html = '';
        resp.data.forEach(row => {
            html += `<tr>
                <td>${row.id}</td>
                <td>${row.username}</td>
                <td>${row.name}</td>
                <td>${row.email}</td>
                <td>${row.mobile || '-'}</td>
                <td>${row.registered}</td>
                <td><span class="badge bg-${row.verified === 'Yes' ? 'success' : 'secondary'}">${row.verified}</span></td>
                <td class="table-actions">
                    <button class="btn btn-sm btn-warning btn-edit-user" data-id="${row.id}"><i class="fas fa-edit"></i> Edit</button>
                    <button class="btn btn-sm btn-danger btn-reset-password" data-id="${row.id}"><i class="fas fa-key"></i> Reset PW</button>
                    <button class="btn btn-sm btn-danger btn-delete-user" data-id="${row.id}"><i class="fas fa-trash"></i> Delete</button>
                </td>
            </tr>`;
        });
        $('#users-table tbody').html(html);
    });
}
// Load users when Users tab is shown
$('button[data-bs-target="#users"]').on('shown.bs.tab', loadAdminUsers);
// Optionally, load on page load if tab is active
if ($('#users').hasClass('active')) loadAdminUsers();
// Fetch and display contact messages in the Messages tab
function loadAdminMessages() {
    $.get('php/get_admin_messages.php', function(resp) {
        let html = '';
        resp.data.forEach(row => {
            html += `<tr>
                <td>${row.id}</td>
                <td>${row.name}</td>
                <td>${row.email}</td>
                <td>${row.message}</td>
                <td>${row.date}</td>
                <td><span class="badge bg-${row.status === 'New' ? 'info' : (row.status === 'Replied' ? 'success' : 'secondary')}">${row.status}</span></td>
                <td class="table-actions">
                    <button class="btn btn-sm btn-primary reply-message-btn" data-id="${row.id}"><i class="fas fa-reply"></i> Reply</button>
                </td>
            </tr>`;
        });
        $('#messages-table tbody').html(html);
    });
}
// Load messages when Messages tab is shown
$('button[data-bs-target="#messages"]').on('shown.bs.tab', loadAdminMessages);
// Optionally, load on page load if tab is active
if ($('#messages').hasClass('active')) loadAdminMessages();
// Ticket actions in Bookings tab
$(document).on('click', '.btn-view-ticket', function() {
    const bookingId = $(this).data('id');
    $('#ticketModalBody').html('<div class="text-center"><div class="spinner-border"></div></div>');
    $('#downloadTicketBtn').hide();
    $('#ticketModal').modal('show');
    $.get('php/admin_get_ticket.php', { booking_id: bookingId }, function(resp) {
        if (resp.success && resp.pdf_base64) {
            const pdfData = 'data:application/pdf;base64,' + resp.pdf_base64;
            $('#ticketModalBody').html(`<iframe src="${pdfData}" style="width:100%;height:500px;"></iframe>`);
            $('#downloadTicketBtn').attr('href', pdfData).show();
        } else {
            $('#ticketModalBody').html('<div class="alert alert-danger">Could not load ticket.</div>');
        }
    });
});
$(document).on('click', '.btn-resend-ticket', function() {
    const bookingId = $(this).data('id');
    if (!confirm('Resend ticket to user?')) return;
    $.post('php/admin_resend_ticket.php', { booking_id: bookingId }, function(resp) {
        if (resp.success) {
            alert('Ticket sent to user email!');
        } else {
            alert('Failed to send ticket.');
        }
    });
});
// User Edit
$(document).on('click', '.btn-edit-user', function() {
    const row = $(this).closest('tr');
    $('#editUserId').val(row.find('td:eq(0)').text());
    $('#editUsername').val(row.find('td:eq(1)').text());
    $('#editName').val(row.find('td:eq(2)').text());
    $('#editEmail').val(row.find('td:eq(3)').text());
    $('#editMobile').val(row.find('td:eq(4)').text());
    $('#userEditModal').modal('show');
});
$('#userEditForm').submit(function(e) {
    e.preventDefault();
    $.post('php/admin_edit_user.php', $(this).serialize(), function(resp) {
        if (resp.success) {
            $('#userEditModal').modal('hide');
            loadAdminUsers();
        } else {
            alert('Failed to update user: ' + (resp.error || 'Unknown error'));
        }
    });
});
// User Reset Password
$(document).on('click', '.btn-reset-password', function() {
    const row = $(this).closest('tr');
    $('#resetUserId').val(row.find('td:eq(0)').text());
    $('#resetPassword').val('');
    $('#userResetPasswordModal').modal('show');
});
$('#userResetPasswordForm').submit(function(e) {
    e.preventDefault();
    $.post('php/admin_reset_password.php', $(this).serialize(), function(resp) {
        if (resp.success) {
            $('#userResetPasswordModal').modal('hide');
            alert('Password reset successfully!');
        } else {
            alert('Failed to reset password: ' + (resp.error || 'Unknown error'));
        }
    });
});
// User Add
$('#addUserBtn').click(function() {
    $('#userAddForm')[0].reset();
    $('#userAddModal').modal('show');
});
$('#userAddForm').submit(function(e) {
    e.preventDefault();
    $.post('php/admin_add_user.php', $(this).serialize(), function(resp) {
        if (resp.success) {
            $('#userAddModal').modal('hide');
            loadAdminUsers();
        } else {
            alert('Failed to add user: ' + (resp.error || 'Unknown error'));
        }
    });
});
// User Delete
$(document).on('click', '.btn-delete-user', function() {
    if (!confirm('Delete this user?')) return;
    const row = $(this).closest('tr');
    const user_id = row.find('td:eq(0)').text();
    $.post('php/admin_delete_user.php', {user_id}, function(resp) {
        if (resp.success) {
            loadAdminUsers();
        } else {
            alert('Failed to delete user: ' + (resp.error || 'Unknown error'));
        }
    });
});
</script>
</body>
</html> 