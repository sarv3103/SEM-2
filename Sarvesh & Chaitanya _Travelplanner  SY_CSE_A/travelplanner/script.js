// Toggle mobile navigation menu
const navToggler = document.querySelector('.nav-toggler');
const navLinks = document.querySelector('.nav-links');
const nav = document.querySelector('.navbar');

navToggler.addEventListener('click', () => {
    navLinks.classList.toggle('open');
});

// Close mobile menu when a link is clicked
document.querySelectorAll('.nav-links li a').forEach(link => {
    link.addEventListener('click', () => {
        if(navLinks.classList.contains('open')){
            navLinks.classList.remove('open');
        }
    });
});

// Change navbar style on scroll
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// ===== LOGIN/LOGOUT/SESSION MANAGEMENT =====

// Check session status and update UI on page load
document.addEventListener('DOMContentLoaded', function() {
    checkSessionStatus();
    setupLoginForm();
    setupRegisterForm();
    setupLogoutHandlers();
    setupProfileMenu();
    setupPlannerForms();
});

// Check session status
function checkSessionStatus() {
    fetch('php/session_status.php')
        .then(res => res.json())
        .then(data => {
            console.log('Session status:', data);
            if (data.logged_in) {
                // Map is_admin to userType for compatibility
                const userType = data.is_admin == 1 ? 'admin' : 'user';
                updateUIForLoggedInUser(data.username, userType, data.wallet_balance);
            } else {
                updateUIForLoggedOutUser();
            }
        })
        .catch(err => {
            console.error('Session check failed:', err);
            updateUIForLoggedOutUser();
        });
}

// Setup login form handlers
function setupLoginForm() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm || loginForm._handlerAttached) return;
    
    loginForm._handlerAttached = true;
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleLogin();
    });
    
    // Enter key submits form
    loginForm.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleLogin();
        }
    });
}

// Handle login submission
function handleLogin() {
    const loginForm = document.getElementById('login-form');
    const loginResult = document.getElementById('login-result');
    
    if (!loginForm || !loginResult) return;
    
    loginResult.innerHTML = '<div class="loader">Logging in...</div>';
    
    const formData = new FormData(loginForm);
    
    fetch('php/login.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
            .then(data => {
            if (data.status === 'success') {
                localStorage.setItem('loggedInUser', data.username);
                localStorage.setItem('isAdminUser', data.is_admin == 1 ? '1' : '0');
                localStorage.setItem('user_logged_in', '1'); // Set login status for booking enforcement
                
                loginResult.innerHTML = `<div class='success'><span class='icon'>✅</span> Login successful, Welcome <b>${data.username}</b>!`;
                
                // Refresh session status to get wallet balance
                checkSessionStatus();
                
                // Check for ?next=... param
                const params = new URLSearchParams(window.location.search);
                const next = params.get('next');
                
                setTimeout(() => {
                    window.location = next || data.redirect || 'index.html';
                }, 900);
            } else {
                loginResult.innerHTML = `<div class='error'><span class='icon'>❌</span> ${data.msg || 'Login failed.'}</div>`;
            }
        })
    .catch(() => {
        loginResult.innerHTML = `<div class='error'><span class='icon'>❌</span> Login failed. Please try again.</div>`;
    });
}

// Setup register form handlers
function setupRegisterForm() {
    const registerForm = document.getElementById('register-form');
    if (!registerForm || registerForm._handlerAttached) return;
    
    registerForm._handlerAttached = true;
    
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleRegister();
    });
}

// Handle register submission
function handleRegister() {
    const registerForm = document.getElementById('register-form');
    const registerResult = document.getElementById('register-result');
    
    if (!registerForm || !registerResult) return;
    
    const formData = new FormData(registerForm);
    
    fetch('php/register.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            registerResult.innerHTML = `<div class='success'><span class='icon'>✅</span> ${data.msg || 'Registration successful. Please login.'}</div>`;
            setTimeout(() => window.location.href = "login.html", 1500);
        } else {
            registerResult.innerHTML = `<div class='error'><span class='icon'>❌</span> ${data.msg || 'Registration failed.'}</div>`;
        }
    })
    .catch(() => {
        registerResult.innerHTML = `<div class='error'><span class='icon'>❌</span> Registration failed. Please try again.</div>`;
    });
}

// Setup logout handlers
function setupLogoutHandlers() {
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });
    }
}

// Handle logout
function handleLogout() {
    fetch('php/logout.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                localStorage.removeItem('loggedInUser');
                localStorage.removeItem('isAdminUser');
                localStorage.removeItem('user_logged_in'); // Clear login status
                updateUIForLoggedOutUser();
                window.location.href = 'index.html';
            }
        })
        .catch(() => {
            localStorage.removeItem('loggedInUser');
            localStorage.removeItem('isAdminUser');
            localStorage.removeItem('user_logged_in'); // Clear login status
            updateUIForLoggedOutUser();
            window.location.href = 'index.html';
        });
}

// Setup profile menu
function setupProfileMenu() {
    const profileLink = document.getElementById('profile-link');
    const profileDropdown = document.querySelector('.profile-dropdown');
    
    if (profileLink && profileDropdown) {
        profileLink.addEventListener('click', function(e) {
            e.preventDefault();
            profileDropdown.style.display = profileDropdown.style.display === 'none' ? 'block' : 'none';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileLink.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.style.display = 'none';
            }
        });
    }
    
    // Setup profile menu links
    setupProfileMenuLinks();
}

// Setup profile menu links
function setupProfileMenuLinks() {
    // Edit Profile Link
    const editProfileLink = document.getElementById('edit-profile-link');
    if (editProfileLink) {
        editProfileLink.addEventListener('click', function(e) {
            e.preventDefault();
            // TODO: Implement edit profile functionality
            alert('Edit Profile functionality coming soon!');
        });
    }
    
    // My Bookings Link
    const myBookingsLink = document.getElementById('my-bookings-link');
    if (myBookingsLink) {
        myBookingsLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'php/mybookings.php';
        });
    }
    
    // Wallet Management Link
    const walletManagementLink = document.getElementById('wallet-management-link');
    if (walletManagementLink) {
        walletManagementLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'wallet.html';
        });
    }
    
    // Logout Link
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            handleLogout();
        });
    }
}

// Update UI for logged in user
function updateUIForLoggedInUser(username, userType, walletBalance = 0) {
    console.log('Updating UI for logged in user:', username, userType, 'Wallet:', walletBalance);
    
    const loginLink = document.getElementById('login-link');
    const profileMenu = document.querySelector('.profile-menu');
    const welcomeUser = document.getElementById('welcome-user');
    const authSection = document.getElementById('auth');
    
    if (loginLink) loginLink.style.display = 'none';
    if (profileMenu) profileMenu.style.display = 'block';
    if (welcomeUser) {
        welcomeUser.innerHTML = `
            <div class="welcome-text">Welcome, ${username}!</div>
            <div class="wallet-balance">💰 Wallet: ₹${parseFloat(walletBalance || 0).toFixed(2)}</div>
        `;
        welcomeUser.style.display = 'block';
    }
    if (authSection) authSection.style.display = 'none';
    
    // Update localStorage
    localStorage.setItem('loggedInUser', username);
    localStorage.setItem('isAdminUser', userType === 'admin' ? '1' : '0');
    localStorage.setItem('walletBalance', walletBalance || 0);
}

// Update UI for logged out user
function updateUIForLoggedOutUser() {
    console.log('Updating UI for logged out user');
    
    const loginLink = document.getElementById('login-link');
    const profileMenu = document.querySelector('.profile-menu');
    const welcomeUser = document.getElementById('welcome-user');
    const authSection = document.getElementById('auth');
    
    if (loginLink) loginLink.style.display = 'block';
    if (profileMenu) profileMenu.style.display = 'none';
    if (welcomeUser) {
        welcomeUser.textContent = '';
        welcomeUser.style.display = 'none';
    }
    if (authSection) authSection.style.display = 'block';
    
    // Clear localStorage
    localStorage.removeItem('loggedInUser');
    localStorage.removeItem('isAdminUser');
    localStorage.removeItem('user_logged_in'); // Clear login status for booking enforcement
    localStorage.removeItem('walletBalance');
}

// Function to refresh wallet balance
function refreshWalletBalance() {
    if (localStorage.getItem('loggedInUser')) {
        fetch('php/session_status.php')
            .then(res => res.json())
            .then(data => {
                if (data.logged_in && data.wallet_balance !== undefined) {
                    const welcomeUser = document.getElementById('welcome-user');
                    if (welcomeUser && welcomeUser.style.display !== 'none') {
                        const username = localStorage.getItem('loggedInUser');
                        welcomeUser.innerHTML = `
                            <div class="welcome-text">Welcome, ${username}!</div>
                            <div class="wallet-balance">💰 Wallet: ₹${parseFloat(data.wallet_balance || 0).toFixed(2)}</div>
                        `;
                    }
                    localStorage.setItem('walletBalance', data.wallet_balance || 0);
                }
            })
            .catch(err => {
                console.error('Failed to refresh wallet balance:', err);
            });
    }
}

// Show login required modal
function showLoginRequiredModal() {
    const modal = document.getElementById('login-required-modal');
    if (modal) {
        modal.style.display = 'flex';
        
        document.getElementById('close-login-required-modal').onclick = function() {
            document.getElementById('login-required-modal').style.display = 'none';
        };
    }
}

// ===== PLANNER SECTION FUNCTIONALITY =====

// Planner section functionality
document.addEventListener('DOMContentLoaded', function() {
  // Plan type selection
  const destinationOnlyBtn = document.getElementById('destination-only-btn');
  const completeJourneyBtn = document.getElementById('complete-journey-btn');
  const destinationOnlyForm = document.getElementById('destination-only-form');
  const completeJourneyForm = document.getElementById('complete-journey-form');

  if (destinationOnlyBtn && completeJourneyBtn) {
    destinationOnlyBtn.addEventListener('click', function() {
      destinationOnlyForm.style.display = 'block';
      completeJourneyForm.style.display = 'none';
      destinationOnlyBtn.style.background = '#0077cc';
      completeJourneyBtn.style.background = '#6c757d';
    });

    completeJourneyBtn.addEventListener('click', function() {
      completeJourneyForm.style.display = 'block';
      destinationOnlyForm.style.display = 'none';
      completeJourneyBtn.style.background = '#2193b0';
      destinationOnlyBtn.style.background = '#6c757d';
    });
  }

  // Destination only form submission
  if (destinationOnlyForm) {
    destinationOnlyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      generateDestinationOnlyPlan(this);
    });
  }

  // Complete journey form submission
  if (completeJourneyForm) {
    completeJourneyForm.addEventListener('submit', function(e) {
      e.preventDefault();
      generateCompleteJourneyPlan(this);
    });
  }
});

// Unify plan generation for both plan types
function generateDestinationOnlyPlan(form) {
    // Extract form data for both destination-only and complete journey
    const formData = new FormData(form);
    const fromCity = formData.get('from_city') || formData.get('city');
    const toCity = formData.get('to_city') || formData.get('city');
    const travelers = formData.get('travelers') || 1;
    const travelStyle = formData.get('travel_style') || 'budget';
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const currency = formData.get('currency') || 'INR';

    if (!toCity || !travelers || !travelStyle || !startDate || !endDate) {
        alert('Please fill in all required fields');
        return;
    }

    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Generating Plan...';
    submitBtn.disabled = true;

    // Send to planner.php
    const requestData = new FormData();
    requestData.append('from_city', fromCity);
    requestData.append('to_city', toCity);
    requestData.append('travelers', travelers);
    requestData.append('travel_style', travelStyle);
    requestData.append('start_date', startDate);
    requestData.append('end_date', endDate);
    requestData.append('currency', currency);

    fetch('php/planner.php', {
        method: 'POST',
        body: requestData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        if (data.status === 'success') {
            displayPlanUnified(data);
        } else {
            alert('Error: ' + (data.msg || 'Failed to generate plan'));
        }
    })
    .catch(error => {
        console.error('Plan generation error:', error);
        // No alert shown to user
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Generate complete journey plan
function generateCompleteJourneyPlan(form) {
    const formData = new FormData(form);
    const fromCity = formData.get('from_city');
    const toCity = formData.get('to_city');
    const travelers = formData.get('travelers');
    const travelStyle = formData.get('travel_style');
    const preferredTransport = formData.get('preferred_transport');
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const currency = formData.get('currency');
    
    if (!fromCity || !toCity || !travelers || !travelStyle || !startDate || !endDate) {
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Generating Plan...';
    submitBtn.disabled = true;
    
    // Send to enhanced planner
    const requestData = new FormData();
    requestData.append('from_city', fromCity);
    requestData.append('to_city', toCity);
    requestData.append('travelers', travelers);
    requestData.append('travel_style', travelStyle);
    requestData.append('preferred_transport', preferredTransport);
    requestData.append('start_date', startDate);
    requestData.append('end_date', endDate);
    requestData.append('currency', currency);
    
    fetch('php/planner.php', {
        method: 'POST',
        body: requestData
    })
    .then(res => {
        if (!res.ok) throw new Error('Network response was not ok');
        return res.json();
    })
    .then(data => {
        console.log('Planner response:', data);
        if (data.status === 'success') {
            displayPlan(data, 'enhanced');
        }
    })
    // .catch(error => {
    //     // Only show alert if there is a real fetch/network error
    //     alert('Failed to generate plan. Please try again.');
    // })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Generate plan for destination only
function generatePlanForDestination(destination, travelers, budget, currency, startDate, endDate) {
  const destinations = {
    // Domestic Destinations
    'mumbai': {
      name: 'Mumbai',
      country: 'India',
      description: 'The financial capital of India with beautiful beaches and historic monuments',
      sights: [
        { name: 'Gateway of India', cost: 0, time: '2 hours', description: 'Historic monument and popular tourist spot' },
        { name: 'Marine Drive', cost: 0, time: '1 hour', description: 'Queen\'s Necklace - beautiful curved boulevard' },
        { name: 'Juhu Beach', cost: 0, time: '3 hours', description: 'Famous beach for sunset views and street food' },
        { name: 'Elephanta Caves', cost: 500, time: '4 hours', description: 'Ancient cave temples with rock-cut sculptures' },
        { name: 'Bandra-Worli Sea Link', cost: 100, time: '1 hour', description: 'Engineering marvel connecting Bandra to Worli' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 1500, rating: 3, amenities: 'AC, WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 3000, rating: 4, amenities: 'AC, WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 8000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Vada Pav', cost: 20, description: 'Mumbai\'s favorite street food' },
        { name: 'Pav Bhaji', cost: 80, description: 'Spicy vegetable curry with bread' },
        { name: 'Seafood', cost: 400, description: 'Fresh coastal seafood dishes' },
        { name: 'Street Food Tour', cost: 300, description: 'Guided tour of famous street food spots' }
      ],
      transport: [
        { mode: 'Local Train', cost: 50, time: '30 min', description: 'Fastest way to travel in Mumbai' },
        { mode: 'Metro', cost: 40, time: '25 min', description: 'Modern metro rail system' },
        { mode: 'Taxi', cost: 200, time: '20 min', description: 'Convenient point-to-point travel' },
        { mode: 'Bus', cost: 30, time: '45 min', description: 'Economical public transport' }
      ]
    },
    'delhi': {
      name: 'Delhi',
      country: 'India',
      description: 'The capital city with rich history and diverse culture',
      sights: [
        { name: 'Red Fort', cost: 500, time: '3 hours', description: 'UNESCO World Heritage Site' },
        { name: 'Qutub Minar', cost: 300, time: '2 hours', description: 'Tallest brick minaret in the world' },
        { name: 'India Gate', cost: 0, time: '1 hour', description: 'War memorial and national monument' },
        { name: 'Humayun\'s Tomb', cost: 400, time: '2 hours', description: 'Mughal architecture masterpiece' },
        { name: 'Lotus Temple', cost: 0, time: '1 hour', description: 'Bahá\'í House of Worship' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 1200, rating: 3, amenities: 'AC, WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 2500, rating: 4, amenities: 'AC, WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 7000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Butter Chicken', cost: 300, description: 'Delhi\'s famous Mughlai dish' },
        { name: 'Chaat', cost: 100, description: 'Spicy and tangy street food' },
        { name: 'Kebabs', cost: 250, description: 'Grilled meat delicacies' },
        { name: 'Street Food Tour', cost: 400, description: 'Explore Delhi\'s food culture' }
      ],
      transport: [
        { mode: 'Metro', cost: 50, time: '20 min', description: 'Modern and efficient metro system' },
        { mode: 'Auto Rickshaw', cost: 150, time: '25 min', description: 'Three-wheeler for short distances' },
        { mode: 'Taxi', cost: 250, time: '15 min', description: 'Comfortable private transport' },
        { mode: 'Bus', cost: 40, time: '35 min', description: 'Extensive bus network' }
      ]
    },
    'jaipur': {
      name: 'Jaipur',
      country: 'India',
      description: 'The Pink City with royal heritage and stunning architecture',
      sights: [
        { name: 'Amber Fort', cost: 500, time: '3 hours', description: 'Magnificent hilltop fort' },
        { name: 'Hawa Mahal', cost: 200, time: '1 hour', description: 'Palace of Winds with intricate facade' },
        { name: 'City Palace', cost: 400, time: '2 hours', description: 'Royal residence and museum' },
        { name: 'Jantar Mantar', cost: 200, time: '1 hour', description: 'Astronomical observatory' },
        { name: 'Nahargarh Fort', cost: 100, time: '2 hours', description: 'Scenic views of the city' }
      ],
      hotels: [
        { name: 'Heritage Hotel', price: 3500, rating: 4, amenities: 'AC, WiFi, Pool, Restaurant' },
        { name: 'Standard Hotel', price: 2000, rating: 3, amenities: 'AC, WiFi, Restaurant' },
        { name: 'Luxury Palace', price: 15000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Dal Baati Churma', cost: 200, description: 'Traditional Rajasthani dish' },
        { name: 'Laal Maas', cost: 350, description: 'Spicy red meat curry' },
        { name: 'Ghewar', cost: 100, description: 'Traditional sweet dessert' },
        { name: 'Rajasthani Thali', cost: 300, description: 'Complete traditional meal' }
      ],
      transport: [
        { mode: 'Auto Rickshaw', cost: 100, time: '20 min', description: 'Convenient for city tours' },
        { mode: 'Taxi', cost: 200, time: '15 min', description: 'Comfortable private transport' },
        { mode: 'Bus', cost: 30, time: '30 min', description: 'Public transport option' },
        { mode: 'Cycle Rickshaw', cost: 50, time: '25 min', description: 'Eco-friendly city tour' }
      ]
    },
    'goa': {
      name: 'Goa',
      country: 'India',
      description: 'Tropical paradise with beaches, churches, and Portuguese heritage',
      sights: [
        { name: 'Calangute Beach', cost: 0, time: '4 hours', description: 'Queen of Beaches' },
        { name: 'Basilica of Bom Jesus', cost: 0, time: '1 hour', description: 'UNESCO World Heritage Site' },
        { name: 'Fort Aguada', cost: 100, time: '2 hours', description: '17th-century Portuguese fort' },
        { name: 'Dudhsagar Falls', cost: 800, time: '6 hours', description: 'Four-tiered waterfall' },
        { name: 'Spice Plantation', cost: 600, time: '3 hours', description: 'Traditional spice gardens' }
      ],
      hotels: [
        { name: 'Beach Resort', price: 4000, rating: 4, amenities: 'AC, WiFi, Pool, Beach Access' },
        { name: 'Standard Hotel', price: 2500, rating: 3, amenities: 'AC, WiFi, Restaurant' },
        { name: 'Luxury Resort', price: 12000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Beach Access' }
      ],
      foods: [
        { name: 'Fish Curry', cost: 350, description: 'Traditional Goan seafood' },
        { name: 'Prawn Balchao', cost: 450, description: 'Spicy prawn pickle' },
        { name: 'Bebinca', cost: 150, description: 'Traditional layered dessert' },
        { name: 'Seafood Platter', cost: 800, description: 'Assorted seafood dishes' }
      ],
      transport: [
        { mode: 'Scooter Rental', cost: 300, time: 'Flexible', description: 'Best way to explore Goa' },
        { mode: 'Taxi', cost: 400, time: '30 min', description: 'Convenient for longer distances' },
        { mode: 'Bus', cost: 100, time: '45 min', description: 'Public transport option' },
        { mode: 'Bike Rental', cost: 500, time: 'Flexible', description: 'Adventure motorcycle tours' }
      ]
    },
    'udaipur': {
      name: 'Udaipur',
      country: 'India',
      description: 'City of Lakes with romantic palaces and scenic beauty',
      sights: [
        { name: 'Lake Palace', cost: 1000, time: '2 hours', description: 'Floating palace on Lake Pichola' },
        { name: 'City Palace', cost: 500, time: '3 hours', description: 'Grand palace complex' },
        { name: 'Jag Mandir', cost: 300, time: '1 hour', description: 'Island palace' },
        { name: 'Sajjangarh (Monsoon Palace)', cost: 200, time: '2 hours', description: 'Hilltop palace with panoramic views' },
        { name: 'Fateh Sagar Lake', cost: 0, time: '2 hours', description: 'Beautiful artificial lake' }
      ],
      hotels: [
        { name: 'Lake View Hotel', price: 3000, rating: 4, amenities: 'AC, WiFi, Lake View' },
        { name: 'Heritage Hotel', price: 5000, rating: 4, amenities: 'AC, WiFi, Pool, Heritage' },
        { name: 'Luxury Palace Hotel', price: 18000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Palace' }
      ],
      foods: [
        { name: 'Dal Baati Churma', cost: 200, description: 'Traditional Rajasthani dish' },
        { name: 'Laal Maas', cost: 350, description: 'Spicy red meat curry' },
        { name: 'Gatte ki Sabzi', cost: 150, description: 'Gram flour dumplings in curry' },
        { name: 'Rajasthani Thali', cost: 300, description: 'Complete traditional meal' }
      ],
      transport: [
        { mode: 'Boat Ride', cost: 200, time: '1 hour', description: 'Scenic lake tours' },
        { mode: 'Auto Rickshaw', cost: 100, time: '20 min', description: 'City transport' },
        { mode: 'Taxi', cost: 200, time: '15 min', description: 'Private transport' },
        { mode: 'Cycle Rickshaw', cost: 50, time: '25 min', description: 'Eco-friendly tours' }
      ]
    },
    'kerala': {
      name: 'Kerala',
      country: 'India',
      description: 'God\'s Own Country with backwaters, beaches, and Ayurveda',
      sights: [
        { name: 'Alleppey Backwaters', cost: 800, time: '6 hours', description: 'Houseboat cruise' },
        { name: 'Munnar Tea Gardens', cost: 600, time: '4 hours', description: 'Tea plantation tours' },
        { name: 'Kovalam Beach', cost: 0, time: '3 hours', description: 'Famous beach destination' },
        { name: 'Periyar Wildlife Sanctuary', cost: 500, time: '5 hours', description: 'Wildlife and nature' },
        { name: 'Ayurveda Treatment', cost: 2000, time: '2 hours', description: 'Traditional healing' }
      ],
      hotels: [
        { name: 'Backwater Resort', price: 3500, rating: 4, amenities: 'AC, WiFi, Backwater View' },
        { name: 'Tea Estate Hotel', price: 4000, rating: 4, amenities: 'AC, WiFi, Tea Garden View' },
        { name: 'Luxury Ayurveda Resort', price: 15000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Ayurveda' }
      ],
      foods: [
        { name: 'Kerala Fish Curry', cost: 300, description: 'Traditional fish curry' },
        { name: 'Appam with Stew', cost: 150, description: 'Rice pancakes with curry' },
        { name: 'Malabar Biryani', cost: 250, description: 'Spicy rice dish' },
        { name: 'Kerala Sadya', cost: 400, description: 'Traditional feast' }
      ],
      transport: [
        { mode: 'Houseboat', cost: 800, time: '6 hours', description: 'Backwater cruise' },
        { mode: 'Taxi', cost: 300, time: '30 min', description: 'Private transport' },
        { mode: 'Bus', cost: 100, time: '45 min', description: 'Public transport' },
        { mode: 'Auto Rickshaw', cost: 150, time: '25 min', description: 'Local transport' }
      ]
    },
    'varanasi': {
      name: 'Varanasi',
      country: 'India',
      description: 'Spiritual capital with ancient temples and Ganga ghats',
      sights: [
        { name: 'Ganga Aarti', cost: 0, time: '1 hour', description: 'Evening prayer ceremony' },
        { name: 'Kashi Vishwanath Temple', cost: 0, time: '2 hours', description: 'Sacred temple' },
        { name: 'Sarnath', cost: 200, time: '3 hours', description: 'Buddhist pilgrimage site' },
        { name: 'Ghats Tour', cost: 300, time: '2 hours', description: 'Boat ride on Ganga' },
        { name: 'Banaras Hindu University', cost: 0, time: '1 hour', description: 'Educational institution' }
      ],
      hotels: [
        { name: 'Ghat View Hotel', price: 2000, rating: 3, amenities: 'AC, WiFi, Ghat View' },
        { name: 'Standard Hotel', price: 1500, rating: 3, amenities: 'AC, WiFi, Restaurant' },
        { name: 'Luxury Hotel', price: 8000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Ghat View' }
      ],
      foods: [
        { name: 'Banarasi Paan', cost: 50, description: 'Traditional betel leaf preparation' },
        { name: 'Kachori Sabzi', cost: 100, description: 'Spicy breakfast dish' },
        { name: 'Lassi', cost: 80, description: 'Sweet yogurt drink' },
        { name: 'Street Food Tour', cost: 300, description: 'Local food exploration' }
      ],
      transport: [
        { mode: 'Boat Ride', cost: 200, time: '1 hour', description: 'Ganga river cruise' },
        { mode: 'Auto Rickshaw', cost: 100, time: '20 min', description: 'Local transport' },
        { mode: 'Cycle Rickshaw', cost: 50, time: '30 min', description: 'Eco-friendly transport' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best way to explore ghats' }
      ]
    },
    'shimla': {
      name: 'Shimla',
      country: 'India',
      description: 'Queen of Hills with colonial charm and mountain views',
      sights: [
        { name: 'Mall Road', cost: 0, time: '2 hours', description: 'Famous shopping street' },
        { name: 'Christ Church', cost: 0, time: '1 hour', description: 'Historic church' },
        { name: 'Jakhu Temple', cost: 100, time: '3 hours', description: 'Hanuman temple on hilltop' },
        { name: 'Kufri', cost: 300, time: '4 hours', description: 'Adventure activities' },
        { name: 'Viceregal Lodge', cost: 200, time: '2 hours', description: 'British colonial building' }
      ],
      hotels: [
        { name: 'Mountain View Hotel', price: 2500, rating: 3, amenities: 'AC, WiFi, Mountain View' },
        { name: 'Heritage Hotel', price: 4000, rating: 4, amenities: 'AC, WiFi, Heritage' },
        { name: 'Luxury Resort', price: 12000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Mountain View' }
      ],
      foods: [
        { name: 'Himachali Dham', cost: 300, description: 'Traditional feast' },
        { name: 'Siddu', cost: 100, description: 'Steamed bread with stuffing' },
        { name: 'Chha Gosht', cost: 250, description: 'Spicy meat curry' },
        { name: 'Local Thali', cost: 200, description: 'Traditional meal' }
      ],
      transport: [
        { mode: 'Toy Train', cost: 150, time: '2 hours', description: 'Scenic mountain railway' },
        { mode: 'Taxi', cost: 300, time: '20 min', description: 'Private transport' },
        { mode: 'Bus', cost: 100, time: '30 min', description: 'Public transport' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for Mall Road' }
      ]
    },
    'manali': {
      name: 'Manali',
      country: 'India',
      description: 'Adventure capital with snow-capped mountains and adventure sports',
      sights: [
        { name: 'Solang Valley', cost: 500, time: '4 hours', description: 'Adventure sports hub' },
        { name: 'Hadimba Temple', cost: 50, time: '1 hour', description: 'Ancient wooden temple' },
        { name: 'Rohtang Pass', cost: 800, time: '6 hours', description: 'Snow activities' },
        { name: 'Old Manali', cost: 0, time: '2 hours', description: 'Hippie culture area' },
        { name: 'Beas River', cost: 0, time: '1 hour', description: 'River rafting' }
      ],
      hotels: [
        { name: 'Mountain Resort', price: 3000, rating: 3, amenities: 'AC, WiFi, Mountain View' },
        { name: 'Adventure Camp', price: 2000, rating: 3, amenities: 'Basic, Adventure Activities' },
        { name: 'Luxury Resort', price: 15000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Mountain View' }
      ],
      foods: [
        { name: 'Himachali Dham', cost: 300, description: 'Traditional feast' },
        { name: 'Siddu', cost: 100, description: 'Steamed bread with stuffing' },
        { name: 'Chha Gosht', cost: 250, description: 'Spicy meat curry' },
        { name: 'Local Thali', cost: 200, description: 'Traditional meal' }
      ],
      transport: [
        { mode: 'Taxi', cost: 400, time: '30 min', description: 'Private transport' },
        { mode: 'Bus', cost: 150, time: '45 min', description: 'Public transport' },
        { mode: 'Bike Rental', cost: 600, time: 'Flexible', description: 'Adventure motorcycle tours' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for Old Manali' }
      ]
    },
    'ooty': {
      name: 'Ooty',
      country: 'India',
      description: 'Queen of Hill Stations with tea gardens and colonial charm',
      sights: [
        { name: 'Botanical Gardens', cost: 100, time: '2 hours', description: 'Beautiful gardens' },
        { name: 'Ooty Lake', cost: 150, time: '2 hours', description: 'Boat rides and activities' },
        { name: 'Tea Museum', cost: 200, time: '1 hour', description: 'Tea plantation history' },
        { name: 'Doddabetta Peak', cost: 50, time: '2 hours', description: 'Highest peak in Nilgiris' },
        { name: 'Rose Garden', cost: 100, time: '1 hour', description: 'Famous rose varieties' }
      ],
      hotels: [
        { name: 'Hill Station Hotel', price: 2500, rating: 3, amenities: 'AC, WiFi, Hill View' },
        { name: 'Tea Estate Hotel', price: 4000, rating: 4, amenities: 'AC, WiFi, Tea Garden View' },
        { name: 'Luxury Resort', price: 12000, rating: 5, amenities: 'AC, WiFi, Pool, Spa, Hill View' }
      ],
      foods: [
        { name: 'Ooty Varkey', cost: 50, description: 'Traditional snack' },
        { name: 'Chocolate', cost: 200, description: 'Famous Ooty chocolates' },
        { name: 'Tea', cost: 30, description: 'Fresh tea from plantations' },
        { name: 'Local Thali', cost: 150, description: 'Traditional meal' }
      ],
      transport: [
        { mode: 'Toy Train', cost: 200, time: '2 hours', description: 'Scenic mountain railway' },
        { mode: 'Taxi', cost: 300, time: '20 min', description: 'Private transport' },
        { mode: 'Bus', cost: 100, time: '30 min', description: 'Public transport' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    
    // International Destinations
    'paris': {
      name: 'Paris',
      country: 'France',
      description: 'City of Light with iconic landmarks and rich culture',
      sights: [
        { name: 'Eiffel Tower', cost: 2000, time: '3 hours', description: 'Iconic symbol of Paris' },
        { name: 'Louvre Museum', cost: 1500, time: '4 hours', description: 'World\'s largest art museum' },
        { name: 'Notre-Dame Cathedral', cost: 0, time: '2 hours', description: 'Gothic masterpiece' },
        { name: 'Arc de Triomphe', cost: 800, time: '1 hour', description: 'Historic monument' },
        { name: 'Champs-Élysées', cost: 0, time: '2 hours', description: 'Famous avenue' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 8000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 15000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 45000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Croissant', cost: 300, description: 'French pastry' },
        { name: 'Escargot', cost: 800, description: 'Snails in garlic butter' },
        { name: 'Coq au Vin', cost: 1200, description: 'Chicken in wine sauce' },
        { name: 'French Wine', cost: 1500, description: 'Premium French wine' }
      ],
      transport: [
        { mode: 'Metro', cost: 200, time: '15 min', description: 'Efficient metro system' },
        { mode: 'Taxi', cost: 800, time: '10 min', description: 'Convenient transport' },
        { mode: 'Bus', cost: 150, time: '20 min', description: 'Public transport' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    'london': {
      name: 'London',
      country: 'United Kingdom',
      description: 'Historic capital with royal heritage and modern culture',
      sights: [
        { name: 'Big Ben', cost: 0, time: '1 hour', description: 'Famous clock tower' },
        { name: 'Buckingham Palace', cost: 1500, time: '2 hours', description: 'Royal residence' },
        { name: 'Tower of London', cost: 2000, time: '3 hours', description: 'Historic castle' },
        { name: 'British Museum', cost: 0, time: '4 hours', description: 'World-famous museum' },
        { name: 'London Eye', cost: 1800, time: '1 hour', description: 'Giant observation wheel' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 10000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 18000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 50000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Fish and Chips', cost: 800, description: 'Traditional British dish' },
        { name: 'Afternoon Tea', cost: 1200, description: 'British tradition' },
        { name: 'Sunday Roast', cost: 1500, description: 'Traditional Sunday meal' },
        { name: 'English Breakfast', cost: 1000, description: 'Full English breakfast' }
      ],
      transport: [
        { mode: 'Underground', cost: 250, time: '15 min', description: 'London\'s metro system' },
        { mode: 'Taxi', cost: 1000, time: '10 min', description: 'Black cabs' },
        { mode: 'Bus', cost: 200, time: '20 min', description: 'Double-decker buses' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    'new_york': {
      name: 'New York',
      country: 'USA',
      description: 'The Big Apple with skyscrapers, culture, and endless entertainment',
      sights: [
        { name: 'Statue of Liberty', cost: 1500, time: '3 hours', description: 'Iconic American symbol' },
        { name: 'Times Square', cost: 0, time: '2 hours', description: 'Famous intersection' },
        { name: 'Central Park', cost: 0, time: '3 hours', description: 'Urban oasis' },
        { name: 'Empire State Building', cost: 2000, time: '2 hours', description: 'Famous skyscraper' },
        { name: 'Broadway Show', cost: 3000, time: '3 hours', description: 'World-famous theater' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 12000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 20000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 60000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Pizza', cost: 800, description: 'New York style pizza' },
        { name: 'Hot Dog', cost: 300, description: 'Street food classic' },
        { name: 'Bagel', cost: 200, description: 'New York bagel' },
        { name: 'Cheesecake', cost: 400, description: 'New York cheesecake' }
      ],
      transport: [
        { mode: 'Subway', cost: 200, time: '15 min', description: 'New York metro' },
        { mode: 'Taxi', cost: 800, time: '10 min', description: 'Yellow cabs' },
        { mode: 'Bus', cost: 150, time: '20 min', description: 'Public buses' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    'tokyo': {
      name: 'Tokyo',
      country: 'Japan',
      description: 'Modern metropolis with traditional culture and cutting-edge technology',
      sights: [
        { name: 'Senso-ji Temple', cost: 0, time: '2 hours', description: 'Ancient Buddhist temple' },
        { name: 'Tokyo Skytree', cost: 1500, time: '2 hours', description: 'Tallest tower in Japan' },
        { name: 'Shibuya Crossing', cost: 0, time: '1 hour', description: 'World\'s busiest intersection' },
        { name: 'Tsukiji Fish Market', cost: 0, time: '2 hours', description: 'Famous fish market' },
        { name: 'Meiji Shrine', cost: 0, time: '2 hours', description: 'Shinto shrine' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 8000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 15000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 45000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Sushi', cost: 1200, description: 'Fresh Japanese sushi' },
        { name: 'Ramen', cost: 600, description: 'Japanese noodle soup' },
        { name: 'Tempura', cost: 800, description: 'Battered and fried food' },
        { name: 'Matcha Tea', cost: 300, description: 'Traditional green tea' }
      ],
      transport: [
        { mode: 'JR Train', cost: 200, time: '15 min', description: 'Japan Railways' },
        { mode: 'Metro', cost: 150, time: '10 min', description: 'Tokyo metro' },
        { mode: 'Taxi', cost: 1000, time: '10 min', description: 'Expensive but convenient' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    'dubai': {
      name: 'Dubai',
      country: 'UAE',
      description: 'Modern city with skyscrapers, luxury shopping, and desert adventures',
      sights: [
        { name: 'Burj Khalifa', cost: 2500, time: '2 hours', description: 'World\'s tallest building' },
        { name: 'Palm Jumeirah', cost: 0, time: '3 hours', description: 'Artificial island' },
        { name: 'Dubai Mall', cost: 0, time: '4 hours', description: 'World\'s largest mall' },
        { name: 'Desert Safari', cost: 1500, time: '6 hours', description: 'Desert adventure' },
        { name: 'Dubai Fountain', cost: 0, time: '1 hour', description: 'Musical fountain show' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 6000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 12000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 35000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Shawarma', cost: 200, description: 'Middle Eastern wrap' },
        { name: 'Hummus', cost: 150, description: 'Chickpea dip' },
        { name: 'Falafel', cost: 100, description: 'Fried chickpea balls' },
        { name: 'Arabic Coffee', cost: 50, description: 'Traditional coffee' }
      ],
      transport: [
        { mode: 'Metro', cost: 100, time: '15 min', description: 'Dubai metro' },
        { mode: 'Taxi', cost: 300, time: '10 min', description: 'Convenient transport' },
        { mode: 'Bus', cost: 50, time: '20 min', description: 'Public transport' },
        { mode: 'Tram', cost: 100, time: '15 min', description: 'Dubai tram' }
      ]
    },
    'singapore': {
      name: 'Singapore',
      country: 'Singapore',
      description: 'Modern city-state with diverse culture and stunning architecture',
      sights: [
        { name: 'Marina Bay Sands', cost: 2000, time: '3 hours', description: 'Iconic hotel and casino' },
        { name: 'Gardens by the Bay', cost: 800, time: '3 hours', description: 'Botanical gardens' },
        { name: 'Sentosa Island', cost: 1500, time: '6 hours', description: 'Entertainment island' },
        { name: 'Chinatown', cost: 0, time: '2 hours', description: 'Cultural district' },
        { name: 'Singapore Zoo', cost: 1200, time: '4 hours', description: 'World-class zoo' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 7000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 14000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 40000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Chicken Rice', cost: 400, description: 'Singapore\'s national dish' },
        { name: 'Laksa', cost: 300, description: 'Spicy noodle soup' },
        { name: 'Chilli Crab', cost: 800, description: 'Famous seafood dish' },
        { name: 'Ice Kachang', cost: 150, description: 'Shaved ice dessert' }
      ],
      transport: [
        { mode: 'MRT', cost: 100, time: '10 min', description: 'Singapore metro' },
        { mode: 'Taxi', cost: 400, time: '10 min', description: 'Convenient transport' },
        { mode: 'Bus', cost: 80, time: '15 min', description: 'Public transport' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for exploring' }
      ]
    },
    'bangkok': {
      name: 'Bangkok',
      country: 'Thailand',
      description: 'Vibrant city with temples, street food, and modern attractions',
      sights: [
        { name: 'Grand Palace', cost: 1000, time: '3 hours', description: 'Royal palace complex' },
        { name: 'Wat Phra Kaew', cost: 500, time: '2 hours', description: 'Temple of Emerald Buddha' },
        { name: 'Chatuchak Market', cost: 0, time: '4 hours', description: 'World\'s largest weekend market' },
        { name: 'Khao San Road', cost: 0, time: '2 hours', description: 'Backpacker street' },
        { name: 'Chao Phraya River', cost: 300, time: '2 hours', description: 'River cruise' }
      ],
      hotels: [
        { name: 'Budget Hotel', price: 3000, rating: 3, amenities: 'WiFi, Restaurant' },
        { name: 'Standard Hotel', price: 6000, rating: 4, amenities: 'WiFi, Pool, Restaurant' },
        { name: 'Luxury Hotel', price: 20000, rating: 5, amenities: 'WiFi, Pool, Spa, Gym, Restaurant' }
      ],
      foods: [
        { name: 'Pad Thai', cost: 200, description: 'Stir-fried rice noodles' },
        { name: 'Tom Yum', cost: 300, description: 'Spicy sour soup' },
        { name: 'Green Curry', cost: 250, description: 'Spicy curry dish' },
        { name: 'Mango Sticky Rice', cost: 150, description: 'Traditional dessert' }
      ],
      transport: [
        { mode: 'BTS Skytrain', cost: 100, time: '15 min', description: 'Elevated train' },
        { mode: 'MRT', cost: 100, time: '15 min', description: 'Underground metro' },
        { mode: 'Tuk-tuk', cost: 200, time: '20 min', description: 'Three-wheeler taxi' },
        { mode: 'Boat', cost: 50, time: '30 min', description: 'River transport' }
      ]
    },
    'bali': {
      name: 'Bali',
      country: 'Indonesia',
      description: 'Island paradise with beaches, temples, and spiritual culture',
      sights: [
        { name: 'Tanah Lot Temple', cost: 500, time: '2 hours', description: 'Sea temple on rock' },
        { name: 'Ubud Monkey Forest', cost: 300, time: '2 hours', description: 'Sacred monkey sanctuary' },
        { name: 'Rice Terraces', cost: 200, time: '3 hours', description: 'Tegalalang rice fields' },
        { name: 'Mount Batur', cost: 800, time: '6 hours', description: 'Volcano trekking' },
        { name: 'Nusa Penida', cost: 1500, time: '8 hours', description: 'Island day trip' }
      ],
      hotels: [
        { name: 'Beach Resort', price: 5000, rating: 4, amenities: 'WiFi, Pool, Beach Access' },
        { name: 'Villa', price: 8000, rating: 4, amenities: 'WiFi, Pool, Private Villa' },
        { name: 'Luxury Resort', price: 25000, rating: 5, amenities: 'WiFi, Pool, Spa, Beach Access' }
      ],
      foods: [
        { name: 'Nasi Goreng', cost: 200, description: 'Indonesian fried rice' },
        { name: 'Satay', cost: 150, description: 'Grilled meat skewers' },
        { name: 'Gado-gado', cost: 180, description: 'Vegetable salad with peanut sauce' },
        { name: 'Babi Guling', cost: 400, description: 'Suckling pig' }
      ],
      transport: [
        { mode: 'Scooter Rental', cost: 300, time: 'Flexible', description: 'Best way to explore' },
        { mode: 'Taxi', cost: 400, time: '30 min', description: 'Convenient transport' },
        { mode: 'Private Driver', cost: 800, time: 'Flexible', description: 'Full-day tours' },
        { mode: 'Walking', cost: 0, time: 'Flexible', description: 'Best for villages' }
      ]
    }
  };
  
  const dest = destinations[destination.toLowerCase()];
  if (!dest) {
    console.log('Destination not found:', destination);
    alert('Destination not found. Please select a valid destination.');
    return null;
  }
  
  return {
    type: 'destination-only',
    destination: dest.name,
    country: dest.country,
    description: dest.description,
    travelers: parseInt(travelers),
    budget: budget,
    sights: dest.sights,
    hotels: dest.hotels,
    foods: dest.foods,
    transport: dest.transport,
    currency: currency,
    startDate: startDate,
    endDate: endDate
  };
}

// Unified plan display for both plan types, only Download and Back buttons
function displayPlanUnified(planData) {
    // Remove any existing modal
    const oldModal = document.querySelector('.plan-modal-unified');
    if (oldModal) oldModal.remove();

    const modal = document.createElement('div');
    modal.className = 'plan-modal-unified';
    modal.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.8);z-index:10000;display:flex;align-items:center;justify-content:center;';

    const content = document.createElement('div');
    content.style.cssText = 'background:#fff;padding:32px 32px 24px 32px;border-radius:18px;max-width:600px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,0.18);text-align:center;position:relative;';

    // Plan HTML
    content.innerHTML = `
        <div style="margin-bottom:24px;">
            <h2 style="color:#0077cc;margin-bottom:10px;">Your Travel Plan</h2>
            <div style="text-align:left;max-height:300px;overflow:auto;margin-bottom:18px;">${planData.html}</div>
        </div>
        <div style="display:flex;gap:16px;justify-content:center;">
            <button class="btn download-btn" style="background:#28a745;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-size:1.1em;cursor:pointer;transition:background 0.2s;" onclick="downloadPlanPDFUnified()">⬇️ Download Plan</button>
            <button class="btn back-btn" style="background:#0077cc;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-size:1.1em;cursor:pointer;transition:background 0.2s;" onclick="closeUnifiedPlanModal()">🔙 Back to Planner</button>
        </div>
    `;
    modal.appendChild(content);
    document.body.appendChild(modal);

    // Download handler
    window.downloadPlanPDFUnified = function() {
        // You can customize this to use your existing PDF/HTML download logic
        const blob = new Blob([planData.html], {type: 'text/html'});
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'TravelPlan.html';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };
    // Close handler
    window.closeUnifiedPlanModal = function() {
        modal.remove();
    };
}

// ===== ENHANCED TRAVEL PLAN FUNCTIONS =====

// Unified booking function for all plan types
function bookPlan(planDataString) {
    try {
        const planData = JSON.parse(decodeURIComponent(planDataString));
        showEnhancedBookingForm(planData);
    } catch (error) {
        alert('Error loading booking form. Please try again.');
    }
}

// Normalize plan data for modal and backend
function normalizePlanData(plan) {
    if (plan.type === 'destination-only') {
        return {
            plan_type: 'destination-only',
            from_city: '',
            to_city: plan.destination,
            start_date: plan.startDate,
            end_date: plan.endDate,
            travelers: plan.travelers,
            travel_style: plan.budget ? (plan.budget.includes('luxury') ? 'luxury' : plan.budget.includes('budget') ? 'budget' : 'standard') : 'standard',
            is_international: false,
            total_cost_per_person: 5000,
            total_cost_for_all: 5000 * plan.travelers,
            source_to_dest_cost: 0,
            source_to_dest_mode: '',
            currency: plan.currency || 'INR'
        };
    } else if (plan.plan_data) {
        // Enhanced plan structure (already normalized)
        return {
            plan_type: 'enhanced_travel_plan',
            from_city: plan.plan_data.from_city || '',
            to_city: plan.plan_data.to_city,
            start_date: plan.plan_data.start_date,
            end_date: plan.plan_data.end_date,
            travelers: plan.plan_data.travelers,
            travel_style: plan.plan_data.travel_style,
            is_international: plan.plan_data.is_international,
            total_cost_per_person: plan.plan_data.total_cost_per_person,
            total_cost_for_all: plan.plan_data.total_cost_for_all,
            source_to_dest_cost: plan.plan_data.source_to_dest_cost,
            source_to_dest_mode: plan.plan_data.source_to_dest_mode,
            currency: plan.plan_data.currency
        };
    } else {
        // Complete journey plan structure
        return {
            plan_type: 'complete-journey',
            from_city: plan.from_city || '',
            to_city: plan.to_city,
            start_date: plan.start_date,
            end_date: plan.end_date,
            travelers: plan.travelers,
            travel_style: plan.travel_style,
            is_international: plan.is_international || false,
            total_cost_per_person: plan.total_cost_per_person || 5000,
            total_cost_for_all: plan.total_cost_for_all || (5000 * plan.travelers),
            source_to_dest_cost: plan.source_to_dest_cost || 0,
            source_to_dest_mode: plan.source_to_dest_mode || '',
            currency: plan.currency || 'INR'
        };
    }
}

// Enhanced booking modal for all plans
function showEnhancedBookingForm(plan) {
    const loggedInUser = localStorage.getItem('loggedInUser');
    if (!loggedInUser) {
        showLoginRequiredModal();
        return;
    }
    const normalizedPlan = normalizePlanData(plan);
    const modal = document.createElement('div');
    modal.className = 'enhanced-travel-booking-modal';
    modal.style.cssText = `position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;`;
    const content = document.createElement('div');
    content.className = 'enhanced-travel-booking-content';
    content.style.cssText = `background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 30px; max-width: 900px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.3); color: white; position: relative;`;
    const isInternational = normalizedPlan.is_international || false;
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = `position: absolute; top: 15px; right: 20px; background: rgba(255,255,255,0.2); border: none; color: white; font-size: 24px; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;`;
    closeBtn.onmouseover = () => closeBtn.style.background = 'rgba(255,255,255,0.3)';
    closeBtn.onmouseout = () => closeBtn.style.background = 'rgba(255,255,255,0.2)';
    closeBtn.onclick = () => modal.remove();
    content.appendChild(closeBtn);
    let html = `
        <style>/* ... (same CSS as before) ... */</style>
        <div class="trip-summary">
            <h4>🎯 Trip Summary</h4>
            <p><strong>Plan Type:</strong> ${normalizedPlan.plan_type}</p>
            <p><strong>Destination:</strong> ${normalizedPlan.to_city}</p>
            <p><strong>Travel Type:</strong> ${isInternational ? 'International' : 'Domestic'}</p>
            <p><strong>Travel Style:</strong> ${normalizedPlan.travel_style}</p>
            <p><strong>Duration:</strong> ${normalizedPlan.start_date} to ${normalizedPlan.end_date}</p>
            <p><strong>Travelers:</strong> ${normalizedPlan.travelers}</p>
            <p><strong>Total Cost:</strong> ₹${normalizedPlan.total_cost_for_all.toLocaleString()}</p>
        </div>
        <form class="enhanced-travel-booking-form" onsubmit="submitEnhancedTravelBooking(this, ${JSON.stringify(normalizedPlan).replace(/"/g, '&quot;')}); return false;">
            <h3 style="color: #ffd700; margin-bottom: 20px;">📋 Enhanced Travel Booking Form</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_mobile">📱 Contact Mobile *</label>
                    <input type="tel" id="contact_mobile" name="contact_mobile" required pattern="[0-9]{10}" placeholder="10-digit mobile number">
                </div>
                <div class="form-group">
                    <label for="contact_email">📧 Contact Email *</label>
                    <input type="email" id="contact_email" name="contact_email" required placeholder="your.email@example.com">
                </div>
            </div>
            <div class="form-group">
                <label for="travel_date">📅 Travel Date *</label>
                <input type="date" id="travel_date" name="travel_date" required min="${new Date().toISOString().split('T')[0]}">
            </div>
            <div class="form-group">
                <label for="special_requirements">📝 Special Requirements</label>
                <textarea id="special_requirements" name="special_requirements" rows="3" placeholder="Any special requirements, dietary restrictions, accessibility needs, etc."></textarea>
            </div>
            <h4 style="color: #ffd700; margin: 30px 0 20px 0;">👥 Traveler Details</h4>
            <div id="travelers-container">
                ${generateTravelerFields(normalizedPlan.travelers, isInternational)}
            </div>
            <div class="enhanced-travel-booking-actions">
                <button type="submit" class="btn">✅ Confirm Enhanced Travel Booking</button>
                <button type="button" class="btn btn-cancel" onclick="this.closest('.enhanced-travel-booking-modal').remove()">❌ Cancel</button>
            </div>
        </form>
    `;
    content.innerHTML += html;
    modal.appendChild(content);
    document.body.appendChild(modal);
    modal.addEventListener('click', function(e) { if (e.target === modal) { modal.remove(); } });
}

// Generate traveler fields for enhanced travel booking
function generateTravelerFields(count, isInternational) {
    let html = '';
    for (let i = 1; i <= count; i++) {
        html += `
            <div class="traveler-card">
                <h4>👤 Traveler ${i}</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="traveler_name_${i}">Full Name *</label>
                        <input type="text" id="traveler_name_${i}" name="traveler_name_${i}" required 
                               placeholder="Enter full name">
                    </div>
                    <div class="form-group">
                        <label for="traveler_age_${i}">Age *</label>
                        <input type="number" id="traveler_age_${i}" name="traveler_age_${i}" required 
                               min="1" max="120" placeholder="Age">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="traveler_gender_${i}">Gender *</label>
                        <select id="traveler_gender_${i}" name="traveler_gender_${i}" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    ${isInternational ? `
                        <div class="form-group">
                            <label for="traveler_nationality_${i}">Nationality *</label>
                            <select id="traveler_nationality_${i}" name="traveler_nationality_${i}" required>
                                <option value="">Select Nationality</option>
                                <option value="Indian">Indian</option>
                                <option value="American">American</option>
                                <option value="British">British</option>
                                <option value="Canadian">Canadian</option>
                                <option value="Australian">Australian</option>
                                <option value="German">German</option>
                                <option value="French">French</option>
                                <option value="Japanese">Japanese</option>
                                <option value="Chinese">Chinese</option>
                                <option value="Korean">Korean</option>
                                <option value="Singaporean">Singaporean</option>
                                <option value="Malaysian">Malaysian</option>
                                <option value="Thai">Thai</option>
                                <option value="Vietnamese">Vietnamese</option>
                                <option value="Filipino">Filipino</option>
                                <option value="Indonesian">Indonesian</option>
                                <option value="Brazilian">Brazilian</option>
                                <option value="Mexican">Mexican</option>
                                <option value="Argentine">Argentine</option>
                                <option value="Chilean">Chilean</option>
                                <option value="South African">South African</option>
                                <option value="Egyptian">Egyptian</option>
                                <option value="Nigerian">Nigerian</option>
                                <option value="Kenyan">Kenyan</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    ` : ''}
                </div>
                ${isInternational ? `
                    <div class="form-group">
                        <label for="traveler_passport_${i}">Passport Number *</label>
                        <input type="text" id="traveler_passport_${i}" name="traveler_passport_${i}" required 
                               pattern="[A-Z0-9]{6,9}" placeholder="6-9 characters, letters and numbers only"
                               oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')">
                        <div class="passport-format">Format: 6-9 characters, letters and numbers only</div>
                    </div>
                ` : ''}
            </div>
        `;
    }
    return html;
}

// Submit booking for all plans
function submitEnhancedTravelBooking(form, plan) {
    const formData = new FormData(form);
    if (plan.is_international) {
        const travelers = parseInt(plan.travelers);
        for (let i = 1; i <= travelers; i++) {
            const passport = formData.get(`traveler_passport_${i}`);
            if (passport && !/^[A-Z0-9]{6,9}$/.test(passport)) {
                alert(`Invalid passport number for Traveler ${i}. Please use 6-9 characters, letters and numbers only.`);
                return;
            }
        }
    }
    formData.append('plan_type', plan.plan_type);
    formData.append('from_city', plan.from_city || '');
    formData.append('to_city', plan.to_city);
    formData.append('start_date', plan.start_date);
    formData.append('end_date', plan.end_date);
    formData.append('travel_date', formData.get('travel_date'));
    formData.append('travel_style', plan.travel_style);
    formData.append('is_international', plan.is_international);
    formData.append('total_cost_per_person', plan.total_cost_per_person);
    formData.append('total_cost_for_all', plan.total_cost_for_all);
    formData.append('source_to_dest_cost', plan.source_to_dest_cost);
    formData.append('source_to_dest_mode', plan.source_to_dest_mode);
    formData.append('currency', plan.currency);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = '🔄 Processing...';
    submitBtn.disabled = true;
    fetch('php/book_plan.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const bookingModal = document.querySelector('.enhanced-travel-booking-modal');
            if (bookingModal) bookingModal.remove();
            showBookingConfirmationModal(data);
        } else {
            alert('❌ Enhanced travel booking failed: ' + (data.msg || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('❌ Enhanced travel booking failed: ' + error.message + '. Please check your internet connection and try again.');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Confirmation modal with download ticket options
function showBookingConfirmationModal(data) {
    const plan = data.plan_details || {};
    plan.booking_id = data.booking_id || '';
    const modal = document.createElement('div');
    modal.className = 'booking-confirmation-modal';
    modal.style.cssText = `position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 10000; display: flex; align-items: center; justify-content: center; padding: 20px;`;
    const content = document.createElement('div');
    content.style.cssText = `background: #fff; border-radius: 15px; padding: 30px; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.3); color: #222;`;
    content.innerHTML = `
        <h2 style="color: #0077cc;">✅ Booking Confirmed!</h2>
        <p><b>Booking ID:</b> ${plan.booking_id}</p>
        <p><b>Destination:</b> ${plan.destination || plan.to_city}</p>
        <p><b>Travel Dates:</b> ${plan.start_date} to ${plan.end_date}</p>
        <p><b>Travelers:</b> ${plan.travelers}</p>
        <p><b>Total Cost:</b> ₹${plan.total_cost_for_all ? plan.total_cost_for_all.toLocaleString() : ''}</p>
        <div style="margin: 20px 0;">
            <button onclick="downloadTicket('html', '${encodeURIComponent(JSON.stringify(plan))}')" style="margin-right:10px;">Download HTML Ticket</button>
            <button onclick="downloadTicket('pdf', '${encodeURIComponent(JSON.stringify(plan))}')">Download PDF Ticket</button>
        </div>
        <button onclick="this.closest('.booking-confirmation-modal').remove()" style="margin-top:10px;">Close</button>
    `;
    modal.appendChild(content);
    document.body.appendChild(modal);
    modal.addEventListener('click', function(e) { if (e.target === modal) modal.remove(); });
}

function downloadTicket(type, planString) {
    const plan = JSON.parse(decodeURIComponent(planString));
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `php/download_ticket.php?type=${type}`;
    form.target = '_blank';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'plan';
    input.value = JSON.stringify(plan);
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

// Function to close plan display
function closePlanDisplay() {
    const suggestionsDiv = document.getElementById('suggestions');
    if (suggestionsDiv) {
        suggestionsDiv.style.display = 'none';
        suggestionsDiv.innerHTML = '';
    }
}

// Initialize enhanced planner on page load
document.addEventListener('DOMContentLoaded', function() {
    setupEnhancedPlannerForms();
    
    // Set minimum dates for all date inputs to prevent past dates
    setupDateValidation();
    
    // Also setup the plan type selection buttons
    const destinationOnlyBtn = document.getElementById('destination-only-btn');
    const completeJourneyBtn = document.getElementById('complete-journey-btn');
    const destinationOnlyForm = document.getElementById('destination-only-form');
    const completeJourneyForm = document.getElementById('complete-journey-form');
    
    if (destinationOnlyBtn && completeJourneyBtn) {
        destinationOnlyBtn.addEventListener('click', function() {
            destinationOnlyForm.style.display = 'block';
            completeJourneyForm.style.display = 'none';
            destinationOnlyBtn.style.background = '#0077cc';
            completeJourneyBtn.style.background = '#6c757d';
        });
        
        completeJourneyBtn.addEventListener('click', function() {
            completeJourneyForm.style.display = 'block';
            destinationOnlyForm.style.display = 'none';
            completeJourneyBtn.style.background = '#2193b0';
            destinationOnlyBtn.style.background = '#6c757d';
        });
    }
});

// Setup date validation to prevent past dates
function setupDateValidation() {
    const today = new Date().toISOString().split('T')[0];
    
    // Set min date for all date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = today;
        
        // Add change event to validate end date is after start date
        if (input.name === 'end_date') {
            input.addEventListener('change', function() {
                const startDateInput = this.closest('form').querySelector('input[name="start_date"]');
                if (startDateInput && startDateInput.value) {
                    if (this.value <= startDateInput.value) {
                        alert('End date must be after start date');
                        this.value = '';
                    }
                }
            });
        }
        
        // Add change event to validate start date
        if (input.name === 'start_date') {
            input.addEventListener('change', function() {
                const endDateInput = this.closest('form').querySelector('input[name="end_date"]');
                if (endDateInput && endDateInput.value) {
                    if (endDateInput.value <= this.value) {
                        alert('End date must be after start date');
                        endDateInput.value = '';
                    }
                }
            });
        }
    });
}

// Function to download plan as PDF
function downloadPlanPDF(planDataString) {
    try {
        const planData = JSON.parse(decodeURIComponent(planDataString));
        console.log('Downloading PDF for plan:', planData);
        
        // Create a form to submit the data to the PDF generation endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'php/generate_plan_pdf.php';
        form.target = '_blank';
        
        // Add the plan data as a hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'plan_data';
        input.value = JSON.stringify(planData);
        form.appendChild(input);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
    } catch (error) {
        console.error('Error downloading PDF:', error);
        alert('Error downloading PDF: ' + error.message + '. Please try again.');
    }
}

// --- Planner Toggle Logic ---
document.addEventListener('DOMContentLoaded', function() {
    const destBtn = document.getElementById('toggle-dest-btn');
    const journeyBtn = document.getElementById('toggle-journey-btn');
    const destForm = document.getElementById('destination-only-form');
    const journeyForm = document.getElementById('complete-journey-form');

    if (destBtn && journeyBtn && destForm && journeyForm) {
        destBtn.onclick = function() {
            destBtn.classList.add('active');
            journeyBtn.classList.remove('active');
            destForm.style.display = 'block';
            journeyForm.style.display = 'none';
        };
        journeyBtn.onclick = function() {
            journeyBtn.classList.add('active');
            destBtn.classList.remove('active');
            journeyForm.style.display = 'block';
            destForm.style.display = 'none';
        };
    }
});

// Add missing functions to prevent console errors
function setupPlannerForms() {
    // This function was referenced but not defined
    // Add basic planner form setup if needed
    console.log('setupPlannerForms called');
}

function setupEnhancedPlannerForms() {
    // This function was referenced but not defined
    // Add enhanced planner form setup if needed
    console.log('setupEnhancedPlannerForms called');
}

function setupPlannerToggles() {
    // This function was referenced but not defined
    // Add planner toggle setup if needed
    console.log('setupPlannerToggles called');
}

// ===== WALLET CHECK & TOP-UP MODAL (GLOBAL) =====
function showWalletTopUpModal(requiredAmount, walletBalance, shortfall, onSuccess) {
    // Remove existing modal if any
    const existing = document.getElementById('wallet-topup-modal');
    if (existing) existing.remove();
    // Create modal
    const modal = document.createElement('div');
    modal.id = 'wallet-topup-modal';
    modal.style = 'position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;';
    modal.innerHTML = `
      <div style="background:#fff;padding:32px 28px 24px 28px;border-radius:18px;box-shadow:0 8px 32px rgba(0,0,0,0.10);max-width:350px;width:100%;display:block;text-align:center;">
        <h3 style="color:#0077cc;margin-bottom:18px;">Insufficient Wallet Balance</h3>
        <p style="margin-bottom:10px;">Required: ₹${requiredAmount}</p>
        <p style="margin-bottom:10px;">Your Wallet: ₹${walletBalance}</p>
        <p style="margin-bottom:18px;font-weight:bold;">Add ₹${shortfall} to your wallet to proceed.</p>
        <button id="add-to-wallet-btn" class="btn" style="background:#0077cc;color:#fff;margin-bottom:10px;width:100%;">Add ₹${shortfall} & Pay by Razorpay</button><br>
        <button id="cancel-topup-btn" class="btn" style="background:#ccc;color:#333;width:100%;">Cancel</button>
      </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('cancel-topup-btn').onclick = () => modal.remove();
    document.getElementById('add-to-wallet-btn').onclick = () => {
        modal.remove();
        startWalletTopUp(shortfall, onSuccess);
    };
}

function startWalletTopUp(amount, onSuccess) {
    // Create Razorpay order for wallet top-up
    fetch('php/create_wallet_order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ amount: amount })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' && data.order_id && data.key_id) {
            const options = {
                key: data.key_id,
                amount: data.amount * 100,
                currency: 'INR',
                name: 'TravelPlanner',
                description: 'Wallet Top-up',
                order_id: data.order_id,
                handler: function (response) {
                    // Verify payment and credit wallet
                    fetch('php/add_to_wallet.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `amount=${amount}&razorpay_payment_id=${response.razorpay_payment_id}&razorpay_order_id=${response.razorpay_order_id}`
                    })
                    .then(res => res.json())
                    .then(resp => {
                        if (resp.status === 'success') {
                            alert('Wallet credited! You can now proceed with booking.');
                            if (onSuccess) onSuccess();
                        } else {
                            alert('Wallet top-up failed: ' + (resp.message || 'Unknown error'));
                        }
                    });
                },
                theme: { color: '#0077cc' }
            };
            const rzp = new Razorpay(options);
            rzp.open();
        } else {
            alert('Failed to initialize wallet top-up.');
        }
    });
}

// Helper to check wallet before booking (improved: pay only shortfall)
function checkWalletAndProceed(requiredAmount, onProceed) {
    fetch('php/session_status.php')
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) {
                alert('Please login to continue.');
                return;
            }
            const walletBalance = parseFloat(data.wallet_balance) || 0;
            if (walletBalance >= requiredAmount) {
                onProceed();
            } else {
                const shortfall = Math.ceil(requiredAmount - walletBalance);
                showWalletTopUpModal(requiredAmount, walletBalance, shortfall, onProceed);
            }
        });
}

// ===== INJECT WALLET CHECK INTO ALL BOOKING FLOWS =====

// 1. INDEX.HTML (submitBooking)
if (typeof submitBooking === 'function' && !window._walletCheckInjected_index) {
    window._walletCheckInjected_index = true;
    const origSubmitBooking = submitBooking;
    window.submitBooking = function() {
        const form = document.getElementById('traveler-form');
        if (!form) return origSubmitBooking();
        const numTravelers = parseInt(document.getElementById('num_travelers').value);
        const transportMode = form.querySelector('[name="transport_mode"]')?.value;
        const destination = document.getElementById('destination').value;
        const isInternational = ['paris','london','new_york','tokyo','dubai','singapore','bangkok','bali'].includes(destination.toLowerCase());
        const baseFares = { flight: isInternational ? 25000 : 3000, train: isInternational ? 0 : 800, bus: isInternational ? 0 : 500 };
        const perPerson = baseFares[transportMode] || 0;
        const totalFare = perPerson * numTravelers;
        checkWalletAndProceed(totalFare, origSubmitBooking);
    };
}

// 2. BOOKING.HTML (travelerForm submit)
if (typeof bookingData !== 'undefined' && document.getElementById('traveler-form') && !window._walletCheckInjected_booking) {
    window._walletCheckInjected_booking = true;
    const travelerForm = document.getElementById('traveler-form');
    const origHandler = travelerForm.onsubmit;
    travelerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const totalFare = bookingData.selectedFare * bookingData.num_travelers;
        checkWalletAndProceed(totalFare, () => origHandler ? origHandler.call(travelerForm, e) : null);
    });
}

// 3. BOOKING_PLAN.HTML (bookingForm submit)
if (document.getElementById('booking-form') && window.location.pathname.includes('booking_plan') && !window._walletCheckInjected_plan) {
    window._walletCheckInjected_plan = true;
    const bookingForm = document.getElementById('booking-form');
    const origHandler = bookingForm.onsubmit;
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        let totalCost = 0;
        try {
            const planData = JSON.parse(decodeURIComponent(document.getElementById('plan-data').value));
            totalCost = parseFloat(planData.totalCost || planData.totalAmount || 0);
        } catch {}
        if (!totalCost || totalCost <= 0) totalCost = 5000;
        checkWalletAndProceed(totalCost, () => origHandler ? origHandler.call(bookingForm, e) : bookingForm.submit());
    });
}

// 4. BOOKING_NORMAL.HTML (bookingForm submit)
if (document.getElementById('booking-form') && window.location.pathname.includes('booking_normal') && !window._walletCheckInjected_normal) {
    window._walletCheckInjected_normal = true;
    const bookingForm = document.getElementById('booking-form');
    const origHandler = bookingForm.onsubmit;
    bookingForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const numTravelers = parseInt(document.getElementById('num_travelers').value);
        const transportMode = bookingForm.querySelector('[name="transport_mode"]')?.value;
        const destination = document.getElementById('destination').value;
        const isInternational = ['paris','london','new_york','tokyo','dubai','singapore','bangkok','bali'].includes(destination.toLowerCase());
        const baseFares = { flight: isInternational ? 25000 : 3000, train: isInternational ? 0 : 800, bus: isInternational ? 0 : 500 };
        const perPerson = baseFares[transportMode] || 0;
        const totalFare = perPerson * numTravelers;
        checkWalletAndProceed(totalFare, () => origHandler ? origHandler.call(bookingForm, e) : bookingForm.submit());
    });
}

// 5. PACKAGE_BOOKING.HTML (processBookingAndPayment)
if (typeof processBookingAndPayment === 'function' && !window._walletCheckInjected_package) {
    window._walletCheckInjected_package = true;
    const origProcessBookingAndPayment = processBookingAndPayment;
    window.processBookingAndPayment = function() {
        let totalAmount = 0;
        try { totalAmount = parseFloat(bookingData.total_amount); } catch {}
        if (!totalAmount || totalAmount <= 0) totalAmount = 5000;
        checkWalletAndProceed(totalAmount, origProcessBookingAndPayment);
    };
}

// === Ensure wallet check on traveler form submit (index.html) ===
if (window.location.pathname.endsWith('index.html') && document.getElementById('traveler-form')) {
    const travelerForm = document.getElementById('traveler-form');
    // Remove all previous submit event listeners by replacing the form
    const newForm = travelerForm.cloneNode(true);
    travelerForm.parentNode.replaceChild(newForm, travelerForm);
    newForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Calculate fare for wallet check
        const numTravelers = parseInt(document.getElementById('num_travelers').value);
        const transportMode = newForm.querySelector('[name="transport_mode"]')?.value;
        const destination = document.getElementById('destination').value;
        const isInternational = ['paris','london','new_york','tokyo','dubai','singapore','bangkok','bali'].includes(destination.toLowerCase());
        const baseFares = { flight: isInternational ? 25000 : 3000, train: isInternational ? 0 : 800, bus: isInternational ? 0 : 500 };
        const perPerson = baseFares[transportMode] || 0;
        const totalFare = perPerson * numTravelers;
        checkWalletAndProceed(totalFare, submitBooking);
    });
}

// View/Download Ticket button logic
$(document).on('click', '.view-ticket-btn', function() {
    const bookingId = $(this).data('id');
    window.open('php/view_ticket_details.php?booking_id=' + bookingId, '_blank');
});

// Enhanced traveller details modal logic
$(document).on('click', '.view-traveller-details', function() {
    const bookingId = $(this).data('id');
    const row = allBookings.find(b => b.id == bookingId);
    if (!row) {
        $('#travellerDetailsModal .modal-body').html('<div class="text-danger">Booking not found.</div>');
        return;
    }
    $('#travellerDetailsModal .modal-body').html(`
      <table class="table table-bordered">
        <tr><th>Name</th><td>${row.traveller_name}</td></tr>
        <tr><th>Email</th><td>${row.traveller_email}</td></tr>
        <tr><th>Mobile</th><td>${row.traveller_mobile}</td></tr>
        <tr><th>Age</th><td>${row.age ?? '-'}</td></tr>
        <tr><th>Gender</th><td>${row.gender ?? '-'}</td></tr>
        <tr><th>Email Verified</th><td>${row.email_verified}</td></tr>
        <tr><th>Booking Type</th><td>${row.category}</td></tr>
        <tr><th>From</th><td>${row.from}</td></tr>
        <tr><th>To</th><td>${row.to}</td></tr>
        <tr><th>Date</th><td>${row.dates}</td></tr>
        <tr><th>Travellers</th><td>${row.travelers}</td></tr>
        <tr><th>Amount</th><td>${row.amount}</td></tr>
        <tr><th>Status</th><td>${row.status}</td></tr>
        <tr><th>Payment ID</th><td>${row.razorpay_payment_id ?? '-'}</td></tr>
        <tr><th>Payment Date</th><td>${row.payment_date ?? '-'}</td></tr>
      </table>
    `);
    $('#travellerDetailsModal').modal('show');
});

$(document).on('click', '.verify-payment-btn', function() {
    const bookingId = $(this).data('id');
    // Fetch booking details via AJAX
    $.get('php/get_booking_details.php', { booking_id: bookingId }, function(data) {
        $('#verifyPaymentModalBody').html(data);
        $('#verifyPaymentModal').modal('show');
        // Store bookingId for later actions
        $('#verifyPaymentModal').data('booking-id', bookingId);
    });
});

$('#markAsPaidBtn').on('click', function() {
    const bookingId = $('#verifyPaymentModal').data('booking-id');
    const paymentRef = $('#adminPaymentRef').val();
    const adminRemarks = $('#adminRemarks').val();
    $.post('php/admin_update_payment.php', {
        booking_id: bookingId,
        action: 'mark_paid',
        payment_ref: paymentRef,
        remarks: adminRemarks
    }, function(response) {
        if (response === 'success') {
            $('#verifyPaymentModal').modal('hide');
            // Optionally update the row in the table
            location.reload();
        } else {
            alert('Failed to update payment status: ' + response);
        }
    });
});

$('#cancelBookingBtn').on('click', function() {
    const bookingId = $('#verifyPaymentModal').data('booking-id');
    const adminRemarks = $('#adminRemarks').val();
    if (!confirm('Are you sure you want to cancel this booking?')) return;
    $.post('php/admin_update_payment.php', {
        booking_id: bookingId,
        action: 'cancel',
        remarks: adminRemarks
    }, function(response) {
        if (response === 'success') {
            $('#verifyPaymentModal').modal('hide');
            location.reload();
        } else {
            alert('Failed to cancel booking: ' + response);
        }
    });
});
