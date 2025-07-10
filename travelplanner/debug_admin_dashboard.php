<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Admin Dashboard Debug Test</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Test Buttons</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Modal Tests</h5>
                            <button class="btn btn-info btn-sm mb-2" onclick="testViewBooking()">Test View Booking</button><br>
                            <button class="btn btn-info btn-sm mb-2" onclick="testViewPayment()">Test View Payment</button><br>
                            <button class="btn btn-info btn-sm mb-2" onclick="testViewUser()">Test View User</button><br>
                            <button class="btn btn-info btn-sm mb-2" onclick="testViewMessage()">Test View Message</button><br>
                            <button class="btn btn-success btn-sm mb-2" onclick="testAddUser()">Test Add User</button><br>
                        </div>
                        <div class="col-md-6">
                            <h5>Action Tests</h5>
                            <button class="btn btn-warning btn-sm mb-2" onclick="testDeleteUser()">Test Delete User</button><br>
                            <button class="btn btn-primary btn-sm mb-2" onclick="testResendTicket()">Test Resend Ticket</button><br>
                            <button class="btn btn-success btn-sm mb-2" onclick="testVerifyPayment()">Test Verify Payment</button><br>
                            <button class="btn btn-secondary btn-sm mb-2" onclick="testExport()">Test Export</button><br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Console Output</h4>
                </div>
                <div class="card-body">
                    <div id="console-output" style="background:#f8f9fa; padding:10px; height:300px; overflow-y:scroll; font-family:monospace; font-size:11px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>System Check</h4>
                </div>
                <div class="card-body">
                    <div id="system-check"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Modals -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="paymentDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewMessageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addUser()">Add User</button>
            </div>
        </div>
    </div>
</div>

<script>
// Console logging function
function log(message) {
    console.log(message);
    const output = document.getElementById('console-output');
    const timestamp = new Date().toLocaleTimeString();
    output.innerHTML += `[${timestamp}] ${message}<br>`;
    output.scrollTop = output.scrollHeight;
}

// Test data
const testBooking = {
    id: 1,
    user_id: 2,
    type: 'flight',
    date: '2025-06-18',
    num_travelers: 1,
    source: 'nanded',
    destination: 'mumbai',
    fare: 3000,
    total_price: 3000,
    status: 'confirmed'
};

const testPayment = {
    id: 1,
    order_id: 'order_123',
    username: 'testuser',
    email: 'test@example.com',
    amount: 3000,
    status: 'completed',
    created_at: '2025-06-24 01:00:00'
};

const testUser = {
    id: 1,
    username: 'testuser',
    email: 'test@example.com',
    mobile: '1234567890',
    created_at: '2025-06-24 01:00:00',
    is_verified: 1
};

const testMessage = {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    message: 'This is a test message',
    created_at: '2025-06-24 01:00:00',
    status: 'unread'
};

// Test functions
function testViewBooking() {
    log('=== Testing viewBooking ===');
    log('Data: ' + JSON.stringify(testBooking));
    viewBooking(testBooking);
}

function testViewPayment() {
    log('=== Testing viewPayment ===');
    log('Data: ' + JSON.stringify(testPayment));
    viewPayment(testPayment);
}

function testViewUser() {
    log('=== Testing viewUser ===');
    log('Data: ' + JSON.stringify(testUser));
    viewUser(testUser);
}

function testViewMessage() {
    log('=== Testing viewMessage ===');
    log('Data: ' + JSON.stringify(testMessage));
    viewMessage(testMessage);
}

function testAddUser() {
    log('=== Testing showAddUserModal ===');
    showAddUserModal();
}

function testDeleteUser() {
    log('=== Testing deleteUser ===');
    deleteUser(1);
}

function testResendTicket() {
    log('=== Testing resendTicket ===');
    resendTicket(1);
}

function testVerifyPayment() {
    log('=== Testing verifyPayment ===');
    verifyPayment(1);
}

function testExport() {
    log('=== Testing exportData ===');
    exportData('bookings');
}

// Modal functions (copied from admin_dashboard.js)
function viewBooking(booking) {
    log('viewBooking called with: ' + JSON.stringify(booking));
    log('Booking data type: ' + typeof booking);
    
    const modalElement = document.getElementById('viewBookingModal');
    if (!modalElement) {
        log('ERROR: viewBookingModal not found!');
        return;
    }
    log('Modal element found: ' + modalElement.id);
    
    const modal = new bootstrap.Modal(modalElement);
    const details = document.getElementById('bookingDetails');
    
    details.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking ID:</strong> ${booking.id || 'N/A'}</p>
                <p><strong>User ID:</strong> ${booking.user_id || 'N/A'}</p>
                <p><strong>Type:</strong> ${booking.type || 'N/A'}</p>
                <p><strong>Date:</strong> ${booking.date || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Source:</strong> ${booking.source || 'N/A'}</p>
                <p><strong>Destination:</strong> ${booking.destination || 'N/A'}</p>
                <p><strong>Travelers:</strong> ${booking.num_travelers || 'N/A'}</p>
                <p><strong>Total Price:</strong> ₹${(booking.total_price || 0).toLocaleString()}</p>
            </div>
        </div>
        <div class="mt-3">
            <p><strong>Status:</strong> <span class="badge bg-${(booking.status || 'pending') === 'confirmed' ? 'success' : 'warning'}">${(booking.status || 'pending').toUpperCase()}</span></p>
        </div>
    `;
    
    log('Showing modal...');
    modal.show();
    log('Modal should be visible now');
}

function viewPayment(payment) {
    log('viewPayment called with: ' + JSON.stringify(payment));
    const modalElement = document.getElementById('viewPaymentModal');
    if (!modalElement) {
        log('ERROR: viewPaymentModal not found!');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    const details = document.getElementById('paymentDetails');
    
    details.innerHTML = `
        <p><strong>Order ID:</strong> ${payment.order_id || 'N/A'}</p>
        <p><strong>User:</strong> ${payment.username || payment.email || 'N/A'}</p>
        <p><strong>Amount:</strong> ₹${(payment.amount || 0).toLocaleString()}</p>
        <p><strong>Status:</strong> <span class="badge bg-${(payment.status || 'pending') === 'completed' ? 'success' : 'warning'}">${(payment.status || 'pending').toUpperCase()}</span></p>
        <p><strong>Date:</strong> ${payment.created_at ? new Date(payment.created_at).toLocaleString() : 'N/A'}</p>
    `;
    
    modal.show();
    log('Payment modal should be showing now');
}

function viewUser(user) {
    log('viewUser called with: ' + JSON.stringify(user));
    const modalElement = document.getElementById('viewUserModal');
    if (!modalElement) {
        log('ERROR: viewUserModal not found!');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    const details = document.getElementById('userDetails');
    
    details.innerHTML = `
        <p><strong>ID:</strong> ${user.id}</p>
        <p><strong>Username:</strong> ${user.username}</p>
        <p><strong>Email:</strong> ${user.email}</p>
        <p><strong>Mobile:</strong> ${user.mobile || 'N/A'}</p>
        <p><strong>Registered:</strong> ${user.created_at ? new Date(user.created_at).toLocaleDateString() : 'N/A'}</p>
        <p><strong>Verified:</strong> <span class="badge bg-${user.is_verified ? 'success' : 'warning'}">${user.is_verified ? 'Yes' : 'No'}</span></p>
    `;
    
    modal.show();
    log('User modal should be showing now');
}

function viewMessage(message) {
    log('viewMessage called with: ' + JSON.stringify(message));
    const modalElement = document.getElementById('viewMessageModal');
    if (!modalElement) {
        log('ERROR: viewMessageModal not found!');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    const details = document.getElementById('messageDetails');
    
    details.innerHTML = `
        <p><strong>From:</strong> ${message.name} (${message.email})</p>
        <p><strong>Date:</strong> ${message.created_at ? new Date(message.created_at).toLocaleString() : 'N/A'}</p>
        <p><strong>Status:</strong> <span class="badge bg-${(message.status || 'unread') === 'read' ? 'success' : 'warning'}">${(message.status || 'unread').toUpperCase()}</span></p>
        <hr>
        <p><strong>Message:</strong></p>
        <div class="border p-3 bg-light">${message.message}</div>
    `;
    
    modal.show();
    log('Message modal should be showing now');
}

function showAddUserModal() {
    log('showAddUserModal called');
    const modalElement = document.getElementById('addUserModal');
    if (!modalElement) {
        log('ERROR: addUserModal not found!');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    log('Add user modal should be showing now');
}

function addUser() {
    log('addUser called');
    const formData = new FormData(document.getElementById('addUserForm'));
    
    fetch('php/admin_add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        log('Add user response: ' + JSON.stringify(data));
        if (data.success) {
            alert('User added successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        log('Add user error: ' + error);
        alert('Error adding user: ' + error);
    });
}

function deleteUser(userId) {
    log('deleteUser called with ID: ' + userId);
    if (confirm('Are you sure you want to delete this user?')) {
        fetch('php/admin_delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            log('Delete user response: ' + JSON.stringify(data));
            if (data.success) {
                alert('User deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            log('Delete user error: ' + error);
            alert('Error deleting user: ' + error);
        });
    }
}

function resendTicket(bookingId) {
    log('resendTicket called with ID: ' + bookingId);
    if (confirm('Resend ticket for this booking?')) {
        fetch('php/admin_resend_ticket.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ booking_id: bookingId })
        })
        .then(response => response.json())
        .then(data => {
            log('Resend ticket response: ' + JSON.stringify(data));
            if (data.success) {
                alert('Ticket resent successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            log('Resend ticket error: ' + error);
            alert('Error resending ticket: ' + error);
        });
    }
}

function verifyPayment(paymentId) {
    log('verifyPayment called with ID: ' + paymentId);
    if (confirm('Verify this payment?')) {
        fetch('php/admin_payment_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payment_id: paymentId, action: 'verify' })
        })
        .then(response => response.json())
        .then(data => {
            log('Verify payment response: ' + JSON.stringify(data));
            if (data.success) {
                alert('Payment verified successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            log('Verify payment error: ' + error);
            alert('Error verifying payment: ' + error);
        });
    }
}

function exportData(type) {
    log('exportData called with type: ' + type);
    window.location.href = `?export=${type}`;
}

// System check
function performSystemCheck() {
    const systemCheck = document.getElementById('system-check');
    let html = '<div class="row">';
    
    // Check jQuery
    html += '<div class="col-md-3"><div class="card">';
    html += '<div class="card-body text-center">';
    if (typeof $ !== 'undefined') {
        html += '<i class="fas fa-check-circle text-success fa-2x"></i>';
        html += '<p class="mt-2">jQuery Loaded</p>';
        html += '<small>v' + $.fn.jquery + '</small>';
    } else {
        html += '<i class="fas fa-times-circle text-danger fa-2x"></i>';
        html += '<p class="mt-2">jQuery Missing</p>';
    }
    html += '</div></div></div>';
    
    // Check Bootstrap
    html += '<div class="col-md-3"><div class="card">';
    html += '<div class="card-body text-center">';
    if (typeof bootstrap !== 'undefined') {
        html += '<i class="fas fa-check-circle text-success fa-2x"></i>';
        html += '<p class="mt-2">Bootstrap Loaded</p>';
    } else {
        html += '<i class="fas fa-times-circle text-danger fa-2x"></i>';
        html += '<p class="mt-2">Bootstrap Missing</p>';
    }
    html += '</div></div></div>';
    
    // Check Modals
    html += '<div class="col-md-3"><div class="card">';
    html += '<div class="card-body text-center">';
    const modals = ['viewBookingModal', 'viewPaymentModal', 'viewUserModal', 'viewMessageModal', 'addUserModal'];
    const missingModals = modals.filter(id => !document.getElementById(id));
    if (missingModals.length === 0) {
        html += '<i class="fas fa-check-circle text-success fa-2x"></i>';
        html += '<p class="mt-2">All Modals Found</p>';
    } else {
        html += '<i class="fas fa-times-circle text-danger fa-2x"></i>';
        html += '<p class="mt-2">Missing Modals</p>';
        html += '<small>' + missingModals.join(', ') + '</small>';
    }
    html += '</div></div></div>';
    
    // Check Functions
    html += '<div class="col-md-3"><div class="card">';
    html += '<div class="card-body text-center">';
    const functions = ['viewBooking', 'viewPayment', 'viewUser', 'viewMessage', 'showAddUserModal'];
    const missingFunctions = functions.filter(fn => typeof window[fn] !== 'function');
    if (missingFunctions.length === 0) {
        html += '<i class="fas fa-check-circle text-success fa-2x"></i>';
        html += '<p class="mt-2">All Functions Found</p>';
    } else {
        html += '<i class="fas fa-times-circle text-danger fa-2x"></i>';
        html += '<p class="mt-2">Missing Functions</p>';
        html += '<small>' + missingFunctions.join(', ') + '</small>';
    }
    html += '</div></div></div>';
    
    html += '</div>';
    systemCheck.innerHTML = html;
}

// Initialize
$(document).ready(function() {
    log('=== Admin Dashboard Debug Started ===');
    log('Page loaded and ready!');
    log('jQuery version: ' + $.fn.jquery);
    log('Bootstrap available: ' + (typeof bootstrap !== 'undefined'));
    log('Current URL: ' + window.location.href);
    
    performSystemCheck();
    
    // Test if modals can be created
    log('Testing modal creation...');
    try {
        const testModal = new bootstrap.Modal(document.getElementById('viewBookingModal'));
        log('Modal creation successful');
    } catch (error) {
        log('Modal creation failed: ' + error.message);
    }
});
</script>
</body>
</html> 