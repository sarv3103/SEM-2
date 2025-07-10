# 🚀 TravelPlanner - Complete Setup Guide

## 📋 Prerequisites Check

Before starting, ensure you have these installed on your laptop:

### 1. **XAMPP Installation**
- Download XAMPP from: https://www.apachefriends.org/download.html
- Install XAMPP (includes Apache, MySQL, PHP, phpMyAdmin)
- **Minimum Requirements:**
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
  - Apache 2.4 or higher

### 2. **Composer Installation**
- Download Composer from: https://getcomposer.org/download/
- Install Composer globally
- Verify installation: `composer --version`

## 🔧 Step-by-Step Setup Commands

### Step 1: Start XAMPP Services
```bash
# Open XAMPP Control Panel
# Click "Start" for Apache and MySQL
# Or use command line (if available):
# xampp start
```

**Verify Services:**
- Apache: http://localhost (should show XAMPP welcome page)
- MySQL: Check XAMPP Control Panel (should show green status)
- phpMyAdmin: http://localhost/phpmyadmin

### Step 2: Navigate to Project Directory
```bash
# Open Command Prompt/Terminal
cd C:\xampp\htdocs\travelplanner
```

### Step 3: Install PHP Dependencies
```bash
# Install Composer dependencies
composer install

# If composer install fails, try:
composer update
```

### Step 4: Database Setup

#### Option A: Using setup.php (Recommended)
```bash
# Open in browser:
http://localhost/travelplanner/setup.php
```

#### Option B: Using phpMyAdmin
1. Open: http://localhost/phpmyadmin
2. Click "New" to create database
3. Name: `travelplanner`
4. Click "Create"
5. Go to "Import" tab
6. Choose file: `database.sql`
7. Click "Go"

#### Option C: Using Command Line
```bash
# If you have MySQL command line client:
mysql -u root -p < database.sql
```

### Step 5: Test PDF Generation
```bash
# Open in browser:
http://localhost/travelplanner/test_mpdf.php
```
**Expected Result:** Should download or display a PDF file

### Step 6: Verify Database Connection
```bash
# Open in browser:
http://localhost/travelplanner/simple_test.php
```
**Expected Result:** Should show database connection status

## 🎯 Access Your Application

### Main Application
```
http://localhost/travelplanner/index.html
```

### Login Page
```
http://localhost/travelplanner/login.html
```

### Admin Dashboard
```
http://localhost/travelplanner/admin_dashboard.php
```

## 🔑 Default Login Credentials

### Admin User
- **Username:** `admin`
- **Password:** `admin123`
- **Email:** `admin@travelplanner.local`

### Test User
- **Username:** `test`
- **Password:** `test123`
- **Email:** `test@travelplanner.local`

## 🗄️ Database Structure

The application creates these tables:

1. **users** - User accounts and authentication
2. **bookings** - Travel bookings and tickets
3. **plans** - Trip plans and itineraries
4. **contact_messages** - Contact form submissions
5. **packages** - Tour packages data
6. **destinations** - Destination information

## 🔍 Troubleshooting

### Common Issues & Solutions

#### 1. **XAMPP Services Won't Start**
```bash
# Check if ports are in use:
netstat -ano | findstr :80
netstat -ano | findstr :3306

# Kill processes using these ports if needed
taskkill /PID <process_id> /F
```

#### 2. **Composer Install Fails**
```bash
# Clear Composer cache
composer clear-cache

# Update Composer
composer self-update

# Try again
composer install
```

#### 3. **Database Connection Error**
```bash
# Check MySQL service is running
# Verify credentials in php/config.php:
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travelplanner";
```

#### 4. **PDF Generation Fails**
```bash
# Check temp directory permissions
# Create directory manually:
mkdir vendor\mpdf\mpdf\tmp

# Set write permissions (Windows):
icacls "vendor\mpdf\mpdf\tmp" /grant Everyone:F
```

#### 5. **Permission Issues (Windows)**
```bash
# Run Command Prompt as Administrator
# Navigate to project directory
cd C:\xampp\htdocs\travelplanner

# Set permissions
icacls . /grant Everyone:F /T
```

## 📱 Testing the Application

### 1. **Test Login**
- Go to: http://localhost/travelplanner/login.html
- Use admin credentials: `admin` / `admin123`
- Should redirect to admin dashboard

### 2. **Test Booking Flow**
- Go to: http://localhost/travelplanner/index.html
- Scroll to booking section
- Try the 3-step booking process:
  1. Enter basic details
  2. View fare options
  3. Enter traveler details
  4. Download ticket

### 3. **Test Destinations**
- Go to: http://localhost/travelplanner/destinations.html
- Test search and filter functionality
- Click on destination cards

### 4. **Test Packages**
- Go to: http://localhost/travelplanner/packages.html
- Browse domestic and international packages
- Test package booking

## 🚨 Important Notes

### File Permissions
- Ensure `vendor/mpdf/mpdf/tmp/` directory is writable
- PHP needs write access to create PDF files

### Browser Compatibility
- Tested on: Chrome, Firefox, Safari, Edge
- Enable JavaScript in browser
- Allow popups for PDF downloads

### Security
- Change default admin password after first login
- Keep XAMPP updated
- Don't expose to public internet without proper security

## 📞 Support

If you encounter issues:

1. **Check XAMPP Error Logs:**
   - Apache: `C:\xampp\apache\logs\error.log`
   - MySQL: `C:\xampp\mysql\data\mysql_error.log`

2. **Check PHP Error Logs:**
   - `C:\xampp\php\logs\php_error_log`

3. **Enable Error Reporting:**
   - Add to PHP files: `error_reporting(E_ALL); ini_set('display_errors', 1);`

## ✅ Verification Checklist

- [ ] XAMPP Apache and MySQL services running
- [ ] Database `travelplanner` created
- [ ] Composer dependencies installed
- [ ] PDF test working (`test_mpdf.php`)
- [ ] Login working (admin/admin123)
- [ ] Booking flow functional
- [ ] Destinations page loading
- [ ] Packages page loading
- [ ] PDF downloads working
- [ ] HTML downloads working

**🎉 Congratulations! Your TravelPlanner application is now ready to use!** 