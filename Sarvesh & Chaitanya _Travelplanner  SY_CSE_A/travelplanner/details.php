<?php
$type = $_GET['type'] ?? '';
$city = $_GET['city'] ?? '';
$name = $_GET['name'] ?? '';
$img = $_GET['img'] ?? '';

function getCityDetails($city) {
  $plans = [
    'maldives' => [
      'title' => 'Maldives',
      'desc' => 'Crystal clear waters and sandy beaches.',
      'img' => 'https://th.bing.com/th/id/OIP.sqTxI06FYbaTuyn2drdj8wHaEJ?rs=1&pid=ImgDetMain',
      'sights' => ['Maafushi Island', 'Banana Reef', 'Male City Tour', 'Hulhumale Beach'],
      'packages' => [
        ['name' => 'Maldives Explorer', 'details' => '4 days, 3 nights. Maafushi, Banana Reef, city tour.', 'price' => 24999],
        ['name' => 'Beach Relax', 'details' => '3 days, 2 nights. Hulhumale, water sports, spa.', 'price' => 19999]
      ]
    ],
    'swiss alps' => [
      'title' => 'Swiss Alps',
      'desc' => 'Snowy peaks and scenic views.',
      'img' => 'https://th.bing.com/th/id/OIP.3gP11prJZHJPoktQ48n34AHaE8?w=265&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7',
      'sights' => ['Jungfraujoch', 'Matterhorn', 'Lake Lucerne', 'Interlaken'],
      'packages' => [
        ['name' => 'Alps Adventure', 'details' => '5 days, 4 nights. Jungfraujoch, Matterhorn, Lucerne.', 'price' => 49999],
        ['name' => 'Swiss Scenic', 'details' => '4 days, 3 nights. Interlaken, lakes, mountain train.', 'price' => 39999]
      ]
    ],
    'new york' => [
      'title' => 'New York',
      'desc' => 'The city that never sleeps.',
      'img' => 'https://wallpaperaccess.com/full/1211839.jpg',
      'sights' => ['Statue of Liberty', 'Central Park', 'Empire State Building', 'Times Square'],
      'packages' => [
        ['name' => 'NYC Highlights', 'details' => '4 days, 3 nights. Statue of Liberty, Empire State, Times Square.', 'price' => 45999],
        ['name' => 'Big Apple Tour', 'details' => '3 days, 2 nights. Central Park, shopping, Broadway.', 'price' => 39999]
      ]
    ],
    'amazon' => [
      'title' => 'Amazon',
      'desc' => 'Lush rainforests and exotic wildlife.',
      'img' => 'https://images.rawpixel.com/image_800/cHJpdmF0ZS9zci9pbWFnZXMvd2Vic2l0ZS8yMDIzLTExL3Jhd3BpeGVsX29mZmljZV8yM19hX3BpY3R1cmVfb2ZfYV9hbWF6b25fZm9yZXN0X2xhbmRzY2FwZV93aXRoX19kMThmMTdkNy0xNjlkLTQzZTctODhiYS0yM2RkZDJhZmY4ZGNfMS5qcGc.jpg',
      'sights' => ['Manaus City', 'Amazon Rainforest Tour', 'Meeting of Waters', 'Anavilhanas National Park'],
      'packages' => [
        ['name' => 'Amazon Explorer', 'details' => '5 days, 4 nights. Rainforest, river cruise, wildlife.', 'price' => 34999],
        ['name' => 'Jungle Adventure', 'details' => '4 days, 3 nights. National Park, Manaus, eco-lodge.', 'price' => 29999]
      ]
    ]
  ];
  return $plans[$city] ?? null;
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Details</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f7f7f7; margin: 0; }
    .details-container { max-width: 600px; margin: 2em auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px #0001; padding: 2em; }
    .details-container img { width: 100%; border-radius: 10px; margin-bottom: 1em; }
    .details-container h2 { margin-top: 0; }
    .package-card { background: #f0f8ff; border-radius: 8px; margin: 1em 0; padding: 1em; }
    .gallery-img { width: 100%; border-radius: 10px; }
    .back-btn { display: inline-block; margin-top: 1em; padding: 0.5em 1.5em; background: #007bff; color: #fff; border-radius: 6px; text-decoration: none; }
    .btn { padding: 0.7em 1.2em; background: #28a745; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .btn:hover { background: #218838; }
    .auth {
      background: linear-gradient(120deg, #6dd5ed 0%, #2193b0 100%);
      padding: 2em 0 3em 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .auth .container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 32px #0002;
      padding: 2.5em 2em 2em 2em;
      max-width: 400px;
      width: 100%;
      margin: 0 auto;
    }
    .auth-form input {
      width: 100%;
      padding: 0.8em 1em;
      margin: 0.7em 0;
      border: 1px solid #b2d8e6;
      border-radius: 8px;
      font-size: 1em;
      background: #f7fbfc;
      transition: border 0.2s;
    }
    .auth-form input:focus {
      border: 1.5px solid #2193b0;
      outline: none;
      background: #eaf6fb;
    }
    .auth-form button.btn {
      width: 100%;
      background: linear-gradient(90deg, #2193b0 0%, #6dd5ed 100%);
      color: #fff;
      font-weight: 600;
      border: none;
      border-radius: 8px;
      padding: 0.9em 0;
      margin-top: 1em;
      font-size: 1.1em;
      cursor: pointer;
      box-shadow: 0 2px 8px #2193b033;
      transition: background 0.2s;
    }
    .auth-form button.btn:hover {
      background: linear-gradient(90deg, #6dd5ed 0%, #2193b0 100%);
    }
    .auth-form p {
      margin: 1em 0 0 0;
      text-align: center;
      color: #2193b0;
    }
    #login-result .success, #register-result .success {
      color: #28a745;
      background: #eafaf1;
      border-radius: 6px;
      padding: 0.7em 1em;
      margin-top: 1em;
      text-align: center;
    }
    #login-result .error, #register-result .error {
      color: #c0392b;
      background: #fdecea;
      border-radius: 6px;
      padding: 0.7em 1em;
      margin-top: 1em;
      text-align: center;
    }
    .fare-details-box { background: #e3f2fd; border-radius: 12px; box-shadow: 0 2px 12px #2193b033; padding: 2em; margin: 2em 0; }
    .fare-details-box h3 { color: #0077cc; }
    .traveller-form input, .traveller-form select { margin: 0.5em 0; padding: 0.5em; border-radius: 6px; border: 1px solid #b2d8e6; width: 90%; }
    .traveller-form label { display: block; margin-top: 1em; }
    .traveller-form .btn { margin-top: 1em; }
  </style>
</head>
<body>
<div class="details-container">
<?php if ($type === 'destination' && $city):
  $details = getCityDetails($city);
  if ($details): ?>
    <img src="<?= htmlspecialchars($details['img']) ?>" alt="<?= htmlspecialchars($details['title']) ?>">
    <h2><?= htmlspecialchars($details['title']) ?></h2>
    <p><?= htmlspecialchars($details['desc']) ?></p>
    <h3>Sights</h3>
    <ul>
      <?php foreach ($details['sights'] as $sight): ?>
        <li><?= htmlspecialchars($sight) ?></li>
      <?php endforeach; ?>
    </ul>
    <h3>Packages</h3>
    <?php foreach ($details['packages'] as $pkg): ?>
      <div class="package-card">
        <h4><?= htmlspecialchars($pkg['name']) ?></h4>
        <p><?= htmlspecialchars($pkg['details']) ?></p>
        <div><b>Price:</b> ₹<?= number_format($pkg['price']) ?></div>
        <button class="btn select-fare-btn" data-pkg='<?= json_encode($pkg, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>Select &amp; Book</button>
      </div>
    <?php endforeach; ?>
    <div id="fare-booking-section" style="display:none;"></div>
    <script>
      // Fare selection logic
      document.querySelectorAll('.select-fare-btn').forEach(btn => {
        btn.onclick = function() {
          // Hide all package cards and show only booking section
          document.querySelectorAll('.package-card').forEach(card => card.style.display = 'none');
          document.getElementById('fare-booking-section').style.display = 'block';
          const pkg = JSON.parse(this.getAttribute('data-pkg'));
          document.getElementById('fare-booking-section').innerHTML = `
            <div class="fare-details-box">
              <h3>Booking: ${pkg.name}</h3>
              <div><b>Details:</b> ${pkg.details}</div>
              <div><b>Price:</b> ₹${pkg.price}</div>
              <form id="fare-booking-form" class="traveller-form" method="post" action="php/book.php" style="margin-top:1.5em;">
                <input type="hidden" name="type" value="destination">
                <input type="hidden" name="destination_name" value="${pkg.name}">
                <label>Travel Date: <input type="date" name="travel_date" required></label><br><br>
                <label>Number of Persons: <input type="number" name="num_persons" id="fare-num-persons" min="1" max="20" required></label><br><br>
                <label>Phone Number: <input type="tel" name="phone" required></label><br><br>
                <div id="fare-traveler-details"></div>
                <button type="button" id="add-fare-traveler" class="btn">Add Traveler Details</button><br><br>
                <button type="submit" class="btn">Confirm & Download PDF</button>
              </form>
            </div>
          `;
          // Add CSS for the fare details box
          const style = document.createElement('style');
          style.innerHTML = `.fare-details-box { background: #e3f2fd; border-radius: 12px; box-shadow: 0 2px 12px #2193b033; padding: 2em; margin: 2em 0; }
            .fare-details-box h3 { color: #0077cc; }
            .traveller-form input, .traveller-form select { margin: 0.5em 0; padding: 0.5em; border-radius: 6px; border: 1px solid #b2d8e6; width: 90%; }
            .traveller-form label { display: block; margin-top: 1em; }
            .traveller-form .btn { margin-top: 1em; }`;
          document.head.appendChild(style);
          // JS for traveler fields
          let fareTravelerCount = 0;
          const fareNumPersonsInput = document.getElementById('fare-num-persons');
          const fareTravelerDetailsDiv = document.getElementById('fare-traveler-details');
          const addFareTravelerBtn = document.getElementById('add-fare-traveler');
          function addFareTravelerField() {
            fareTravelerCount++;
            const div = document.createElement('div');
            div.innerHTML = `<b>Traveler ${fareTravelerCount}:</b> Name: <input name="traveler_name[]" required> Age: <input name="age[]" type="number" min="0" required> Gender: <select name="gender[]"><option>Male</option><option>Female</option><option>Other</option></select><br><br>`;
            fareTravelerDetailsDiv.appendChild(div);
          }
          addFareTravelerBtn.onclick = addFareTravelerField;
          fareNumPersonsInput.addEventListener('change', function() {
            fareTravelerDetailsDiv.innerHTML = '';
            fareTravelerCount = 0;
            const n = parseInt(fareNumPersonsInput.value) || 0;
            for (let i = 0; i < n; i++) addFareTravelerField();
          });
        };
      });
    </script>
<?php else: ?>
    <p>Destination details not found.</p>
<?php endif; ?>
<?php elseif ($type === 'package' && $name): ?>
<?php
// Fetch all packages from backend
$allPackages = [];
$packageDataJson = @file_get_contents('php/get_packages.php');
if ($packageDataJson !== false) {
    $packageData = json_decode($packageDataJson, true);
    if (isset($packageData['domestic']) && is_array($packageData['domestic'])) {
        $allPackages = array_merge($allPackages, $packageData['domestic']);
    }
    if (isset($packageData['international']) && is_array($packageData['international'])) {
        $allPackages = array_merge($allPackages, $packageData['international']);
    }
}
$pkg = null;
$searchName = trim(strtolower(urldecode($name)));
foreach ($allPackages as $p) {
    if (trim(strtolower($p['name'])) === $searchName) {
        $pkg = $p;
        break;
    }
}
?>
<?php if ($pkg): ?>
  <img src="<?= htmlspecialchars($pkg['image'] ?? $pkg['img'] ?? '') ?>" alt="<?= htmlspecialchars($pkg['name']) ?>">
  <h2><?= htmlspecialchars($pkg['name']) ?></h2>
  <div><b>Duration:</b> <?= htmlspecialchars($pkg['duration'] ?? ($pkg['days'] . ' Days / ' . $pkg['nights'] . ' Nights')) ?></div>
  <div><b>Cities:</b> <?= htmlspecialchars(is_array($pkg['cities']) ? implode(', ', $pkg['cities']) : $pkg['cities']) ?></div>
  <div><b>Travel Mode:</b> <?= htmlspecialchars($pkg['travel_mode'] ?? (isset($pkg['travel_modes']) ? implode(', ', $pkg['travel_modes']) : '')) ?></div>
  <div><b>Meals:</b> <?= htmlspecialchars($pkg['meals'] ?? '') ?></div>
  <div><b>Hotels:</b> <?= htmlspecialchars(is_array($pkg['hotels']) ? implode(', ', $pkg['hotels']) : $pkg['hotels']) ?></div>
  <div><b>Price per Person:</b> ₹<?= number_format($pkg['price_per_person'] ?? $pkg['price'] ?? 0) ?></div>
  <div style="margin:1em 0;"><?= htmlspecialchars($pkg['description'] ?? '') ?></div>
  <button id="book-btn" class="btn">Book This Package</button>
  <form id="booking-form" style="display:none;margin-top:2em;" method="post" action="php/book.php">
    <input type="hidden" name="package_name" value="<?= htmlspecialchars($pkg['name']) ?>">
    <label>Travel Date: <input type="date" name="travel_date" required></label><br><br>
    <label>Number of Persons: <input type="number" name="num_persons" id="pkg-num-persons" min="1" max="20" required></label><br><br>
    <label>Phone Number: <input type="tel" name="phone" required></label><br><br>
    <div id="traveler-details"></div>
    <button type="button" id="add-traveler" class="btn">Add Traveler Details</button><br><br>
    <button type="submit" class="btn">Confirm & Download PDF</button>
    <div id="booking-result"></div>
  </form>
  <script>
    document.getElementById('book-btn').onclick = function() {
      document.getElementById('booking-form').style.display = 'block';
      this.style.display = 'none';
    };
    // Traveler details JS (same as before)
    let travelerCount = 0;
    const numPersonsInput = document.getElementById('pkg-num-persons');
    const travelerDetailsDiv = document.getElementById('traveler-details');
    const addTravelerBtn = document.getElementById('add-traveler');
    function addTravelerField() {
      travelerCount++;
      const div = document.createElement('div');
      div.innerHTML = `<b>Traveler ${travelerCount}:</b> Name: <input name="traveler_name[]" required> Age: <input name="age[]" type="number" min="0" required> Gender: <select name="gender[]"><option>Male</option><option>Female</option><option>Other</option></select><br><br>`;
      travelerDetailsDiv.appendChild(div);
    }
    addTravelerBtn.onclick = addTravelerField;
    numPersonsInput.addEventListener('change', function() {
      travelerDetailsDiv.innerHTML = '';
      travelerCount = 0;
      const n = parseInt(numPersonsInput.value) || 0;
      for (let i = 0; i < n; i++) addTravelerField();
    });
    // Booking form AJAX (same as before)
    const bookingForm = document.getElementById('booking-form');
    bookingForm.addEventListener('submit', function(ev) {
      ev.preventDefault();
      const formData = new FormData(bookingForm);
      fetch('php/book.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        let html = '';
        if (data.status === 'success') {
          html = `<div class='success'>${data.msg}</div>`;
          if (data.pdf) {
            const blob = new Blob([Uint8Array.from(atob(data.pdf), c => c.charCodeAt(0))], {type: 'application/pdf'});
            const url = URL.createObjectURL(blob);
            html += `<a href='${url}' download='ticket.pdf' class='btn' style='margin-top:1em;'>Download Ticket (PDF)</a>`;
          }
          if (data.html) {
            const htmlTicket = atob(data.html);
            html += `<div style='margin-top:2em;'><b>View Ticket:</b><br><div id='ticket-html-view'></div></div>`;
            setTimeout(() => {
              document.getElementById('ticket-html-view').innerHTML = htmlTicket;
              if (data.ticket_no || data.transport_no) {
                const extra = document.createElement('div');
                extra.style.marginTop = '1em';
                extra.innerHTML =
                  (data.ticket_no ? `<b>Ticket No:</b> ${data.ticket_no}<br>` : '') +
                  (data.transport_no ? `<b>${data.transport_no}</b><br>` : '');
                document.getElementById('ticket-html-view').appendChild(extra);
              }
            }, 0);
          }
        } else {
          html = `<div class='error'>${data.msg || 'Booking failed.'}</div>`;
        }
        document.getElementById('booking-result').innerHTML = html;
      })
      .catch(() => {
        document.getElementById('booking-result').innerHTML = `<div class='error'>Booking failed. Please try again.</div>`;
      });
    });
  </script>
<?php else: ?>
  <p>Package details not found.</p>
<?php endif; ?>
<?php elseif ($type === 'gallery' && $img): ?>
    <img src="<?= htmlspecialchars($img) ?>" class="gallery-img" alt="Gallery Image">
    <h2>Photo Gallery</h2>
    <p>Enjoy this beautiful travel photo!</p>
<?php else: ?>
    <p>No details to show.</p>
<?php endif; ?>
  <a href="javascript:window.close()" class="back-btn">Close Window</a>
</div>
</body>
</html>
