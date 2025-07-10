// Ticket Modal Logic for My Bookings

document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-ticket-btn')) {
            e.preventDefault();
            const bookingData = JSON.parse(e.target.getAttribute('data-booking'));
            showTicketModalFromBooking(bookingData);
        }
    });
});

function showTicketModalFromBooking(bookingData) {
    // Remove any existing modal
    const oldModal = document.getElementById('ticket-preview-modal');
    if (oldModal) oldModal.remove();

    // Generate ticket HTML (reuse logic from booking flow, simplified here)
    const ticketHtml = generateSimpleTicketHtml(bookingData, bookingData.booking_id || bookingData.id || '', bookingData.total_amount || bookingData.fare || 0);

    // Create modal
    const modal = document.createElement('div');
    modal.id = 'ticket-preview-modal';
    modal.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center;`;
    modal.innerHTML = `
        <div style="background: white; border-radius: 15px; max-width: 900px; width: 95vw; max-height: 95vh; overflow-y: auto; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <button id="close-ticket-modal" style="position: absolute; top: 15px; right: 20px; background: #dc3545; color: white; border: none; border-radius: 50%; width: 32px; height: 32px; font-size: 20px; cursor: pointer;">&times;</button>
            <div id="ticket-preview-content">${ticketHtml}</div>
            <div style="display: flex; justify-content: center; gap: 15px; margin: 30px 0 20px 0;">
                <button class="btn" style="background: #28a745; color: white;" onclick='downloadTicketHtmlFromModal()'>Download Ticket</button>
                <button class="btn" style="background: #0077cc; color: white;" onclick='printTicketHtmlFromModal()'>Print Ticket</button>
                <button class="btn" style="background: #ffc107; color: #212529;" onclick='sendTicketEmailFromModal()'>Send to Email</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('close-ticket-modal').onclick = () => modal.remove();

    // Store for actions
    window._currentTicketBookingData = bookingData;
}

// Minimal generateSimpleTicketHtml for modal (copy from booking flow or import if modular)
function generateSimpleTicketHtml(bookingData, bookingId, totalAmount) {
    // ... (copy the latest generateSimpleTicketHtml from your booking flow here) ...
    // For brevity, you can import or copy the function body as in package_booking.html
    // This is a placeholder:
    return `<div style='padding:40px;text-align:center;'>
        <h2>Ticket Preview</h2>
        <div><b>Booking ID:</b> ${bookingId}</div>
        <div><b>Name:</b> ${(bookingData.travelers && bookingData.travelers[0] && bookingData.travelers[0].name) || bookingData.contact_name || ''}</div>
        <div><b>Amount:</b> ₹${totalAmount}</div>
        <div style='margin-top:20px;color:#888;'>[Full ticket details here]</div>
    </div>`;
}

function downloadTicketHtmlFromModal() {
    const bookingData = window._currentTicketBookingData;
    const html = generateSimpleTicketHtml(bookingData, bookingData.booking_id || bookingData.id || '', bookingData.total_amount || bookingData.fare || 0);
    const blob = new Blob([html], { type: 'text/html' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `TravelPlanner_Ticket_${bookingData.booking_id || bookingData.id || ''}.html`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function printTicketHtmlFromModal() {
    const bookingData = window._currentTicketBookingData;
    const html = generateSimpleTicketHtml(bookingData, bookingData.booking_id || bookingData.id || '', bookingData.total_amount || bookingData.fare || 0);
    const printWindow = window.open('', '_blank');
    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function sendTicketEmailFromModal() {
    const bookingData = window._currentTicketBookingData;
    let emailTo = bookingData.contact_email || '';
    let promptMsg = 'Enter the email address to send the ticket to:';
    if (emailTo) {
        promptMsg = `Send ticket to this email? (Edit if you want to send to a different address)\n${emailTo}`;
    }
    const userInput = prompt(promptMsg, emailTo);
    if (!userInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(userInput)) {
        alert('Please enter a valid email address.');
        return;
    }
    fetch('send_ticket_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ bookingData, bookingId: bookingData.booking_id || bookingData.id || '', totalAmount: bookingData.total_amount || bookingData.fare || 0, emailTo: userInput })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Ticket sent successfully!');
        } else {
            alert('Failed to send ticket: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(() => {
        alert('Failed to send ticket. Please try again.');
    });
} 