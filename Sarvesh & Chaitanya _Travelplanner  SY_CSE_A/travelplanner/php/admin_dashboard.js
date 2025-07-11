console.log('Admin dashboard JS loaded!');

// Admin Dashboard JavaScript - TravelPlanner
$(document).ready(function() {
    console.log('Admin dashboard JS ready!');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    
    // Initialize dashboard
    loadDashboardStats();
    loadTables();
    
    // Tab change handlers
    $('#adminTab button').on('click', function() {
        const target = $(this).data('bs-target');
        console.log('Tab clicked:', target);
        loadTabContent(target);
    });
    
    // Export buttons
    $('#export-bookings').click(() => {
        console.log('Export bookings clicked');
        exportData('bookings');
    });
    $('#export-payments').click(() => {
        console.log('Export payments clicked');
        exportData('payments');
    });
    $('#export-users').click(() => {
        console.log('Export users clicked');
        exportData('users');
    });
    $('#export-messages').click(() => {
        console.log('Export messages clicked');
        exportData('contacts');
    });
    
    // Add user button
    $('#addUserBtn').click(() => {
        console.log('Add user button clicked');
        showAddUserModal();
    });
    
    console.log('Admin dashboard initialization complete!');
});

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function loadDashboardStats() {
    // Stats are loaded from PHP, just update display
    console.log('Dashboard stats loaded');
}

function loadTables() {
    // Tables are populated from PHP data
    console.log('Tables loaded');
}

function loadTabContent(tabId) {
    console.log('Loading tab:', tabId);
    // Additional tab-specific loading can be added here
}

function exportData(type) {
    window.location.href = `?export=${type}`;
}

// Modal functions
function viewBooking(booking) {
    console.log('viewBooking called with:', booking);
    console.log('Booking data type:', typeof booking);
    console.log('Booking data:', JSON.stringify(booking));
    
    const modalElement = document.getElementById('viewBookingModal');
    if (!modalElement) {
        console.error('viewBookingModal not found!');
        return;
    }
    console.log('Modal element found:', modalElement);
    
    const modal = new bootstrap.Modal(modalElement);
    const details = document.getElementById('bookingDetails');
    
    details.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Booking ID:</strong> ${booking.id || 'N/A'}</p>
                <p><strong>User ID:</strong> ${booking.user_id || 'N/A'}</p>
                <p><strong>Category:</strong> ${booking.category || 'N/A'}</p>
                <p><strong>Name:</strong> ${booking.name || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Start Date:</strong> ${booking.start_date || 'N/A'}</p>
                <p><strong>End Date:</strong> ${booking.end_date || 'N/A'}</p>
                <p><strong>Travelers:</strong> ${booking.travelers || 'N/A'}</p>
                <p><strong>Total Price:</strong> ₹${(booking.total_price || 0).toLocaleString()}</p>
            </div>
        </div>
        <div class="mt-3">
            <p><strong>Status:</strong> <span class="badge bg-${(booking.status || 'pending') === 'confirmed' ? 'success' : 'warning'}">${(booking.status || 'pending').toUpperCase()}</span></p>
        </div>
    `;
    
    console.log('Showing modal...');
    modal.show();
    console.log('Modal should be visible now');
}

function viewPayment(payment) {
    console.log('viewPayment called with:', payment);
    const modalElement = document.getElementById('viewPaymentModal');
    if (!modalElement) {
        console.error('viewPaymentModal not found!');
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
}

function viewUser(user) {
    console.log('viewUser called with:', user);
    const modalElement = document.getElementById('viewUserModal');
    if (!modalElement) {
        console.error('viewUserModal not found!');
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
}

function viewMessage(message) {
    console.log('viewMessage called with:', message);
    const modalElement = document.getElementById('viewMessageModal');
    if (!modalElement) {
        console.error('viewMessageModal not found!');
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
}

function showAddUserModal() {
    console.log('showAddUserModal called');
    const modalElement = document.getElementById('addUserModal');
    if (!modalElement) {
        console.error('addUserModal not found!');
        return;
    }
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function addUser() {
    const formData = new FormData(document.getElementById('addUserForm'));
    
    fetch('php/admin_add_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User added successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error adding user: ' + error);
    });
}

function editUser(user) {
    // Fill the edit form with user data
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editMobile').value = user.mobile || '';
    document.getElementById('editIsVerified').value = user.is_verified ? '1' : '0';
    // Show the modal
    const modalElement = document.getElementById('editUserModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function saveEditUser() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    fetch('php/admin_edit_user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User updated successfully!');
            // Close modal
            const modalElement = document.getElementById('editUserModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            modal.hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error updating user: ' + error);
    });
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('user_id', userId);
        fetch('php/admin_delete_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error deleting user: ' + error);
        });
    }
}

function resendTicket(bookingId) {
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
            if (data.success) {
                alert('Ticket resent successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error resending ticket: ' + error);
        });
    }
}

function verifyPayment(paymentId) {
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
            if (data.success) {
                alert('Payment verified successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error verifying payment: ' + error);
        });
    }
}

function replyMessage(message) {
    const email = message.email;
    const subject = 'Re: Your message to TravelPlanner';
    window.open(`mailto:${email}?subject=${encodeURIComponent(subject)}`);
}

function showPaymentVerification() {
    alert('Payment verification interface coming soon!');
}

function showResendTickets() {
    alert('Resend tickets interface coming soon!');
}

// Auto-refresh dashboard every 30 seconds
setInterval(() => {
    if (document.getElementById('tab-dashboard').classList.contains('active')) {
        console.log('Auto-refreshing dashboard stats...');
    }
}, 30000);

// Render payments table (wallet top-ups only)
function renderPaymentsTable() {
    let html = '';
    filteredPayments.forEach(payment => {
        html += `<tr>
            <td>${payment.id || payment.payment_id || '-'}</td>
            <td>${payment.username || payment.user_email || '-'}</td>
            <td>Wallet Top-up</td>
            <td>₹${parseFloat(payment.amount || 0).toLocaleString()}</td>
            <td><span class="badge bg-${payment.status === 'completed' ? 'success' : (payment.status === 'pending' ? 'warning' : 'danger')}">${payment.status || '-'}</span></td>
            <td>${payment.payment_method || '-'}</td>
            <td>${payment.created_at ? new Date(payment.created_at).toLocaleString() : '-'}</td>
            <td>${payment.razorpay_payment_id || '-'}</td>
            <td>
                <button class="btn btn-outline-info btn-sm view-payment-details" data-id="${payment.id}">View</button>
            </td>
        </tr>`;
    });
    $('#payments-table tbody').html(html);
}

// After loading payments data and applying filters, call renderPaymentsTable()
function applyPaymentFilters() {
    // ... existing filter logic ...
    renderPaymentsTable();
}

// --- Wallet & Payments Data Sync ---
function fetchWalletAndPaymentsData() {
    $.get('php/get_admin_payments_data.php', function(resp) {
        if (resp && resp.success && resp.data && resp.data.wallets) {
            allWalletBalances = resp.data.wallets || [];
            loadWalletBalances();
        } else {
            allWalletBalances = [];
            loadWalletBalances();
        }
    }, 'json').fail(function() {
        allWalletBalances = [];
        loadWalletBalances();
    });
}

// Hook into tab activation for Payments & Wallet
$(document).ready(function() {
    // ... existing code ...
    $('button[data-bs-target="#payments"]').on('shown.bs.tab', function() {
        fetchWalletAndPaymentsData();
    });
    // Optionally, fetch on page load if Payments tab is default
    if ($('button[data-bs-target="#payments"]').hasClass('active')) {
        fetchWalletAndPaymentsData();
    }
});
