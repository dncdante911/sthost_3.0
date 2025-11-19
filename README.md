# StormHosting UA - Complete Documentation

**Version:** 3.0
**Author:** StormHosting UA Team
**Last Updated:** 2025-11-19

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Features](#features)
4. [Database Schema](#database-schema)
5. [File Structure](#file-structure)
6. [Pages & Functionality](#pages--functionality)
7. [API Endpoints](#api-endpoints)
8. [Configuration](#configuration)
9. [WHMCS Integration](#whmcs-integration)
10. [Proxmox Integration](#proxmox-integration)
11. [Deployment Guide](#deployment-guide)
12. [Development](#development)

---

## 🚀 Project Overview

StormHosting UA is a modern hosting and domain management platform built with PHP, MySQL, and modern JavaScript. The platform provides:

- **Domain Tools**: DNS lookup, WHOIS queries, domain transfer requests
- **VPS Management**: Proxmox VE integration for virtual machine provisioning
- **Billing Integration**: WHMCS API integration for payment processing
- **Client Portal**: User dashboard for managing services
- **Modern UI**: Responsive design with gradient hero sections and animations

### Technology Stack

- **Backend**: PHP 7.4+ (PDO for database)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: Vanilla JavaScript (ES6+), Bootstrap Icons
- **CSS**: Custom CSS with modern gradients and animations
- **External APIs**: WHOIS protocol, DNS queries, WHMCS API, Proxmox API

---

## 🏗️ Architecture

### Application Structure

```
sthost_3.0/
├── pages/              # Main application pages
├── api/                # RESTful API endpoints
├── assets/             # Static resources (CSS, JS, images)
├── includes/           # Shared PHP includes
├── config/             # Configuration files
├── cache/              # Cache storage
├── logs/               # Application logs
└── migrations/         # Database migrations
```

### Request Flow

```
User Browser
    ↓
pages/*.php (Frontend)
    ↓
assets/js/*.js (JavaScript)
    ↓
api/*.php (Backend API)
    ↓
includes/db_connect.php (Database)
    ↓
MySQL Database
```

### Security Features

- **SQL Injection Protection**: PDO prepared statements throughout
- **XSS Prevention**: `htmlspecialchars()` on all user outputs
- **CSRF Protection**: Session-based tokens
- **Direct Access Protection**: `SECURE_ACCESS` constant checks
- **Input Validation**: Server-side and client-side validation
- **Rate Limiting**: Anti-spam measures on forms

---

## ✨ Features

### Domain Services

1. **DNS Lookup** (`/pages/domains/dns.php`)
   - Query A, AAAA, MX, CNAME, TXT, NS, SOA, SRV records
   - Real-time validation
   - Modern green gradient UI

2. **WHOIS Lookup** (`/pages/domains/whois.php`)
   - Universal domain support (all TLDs)
   - IANA fallback for unknown zones
   - Parse registration dates, registrar, name servers
   - Modern blue gradient UI

3. **Domain Transfer** (`/pages/domains/transfer.php`)
   - Database-driven pricing
   - Email notifications (admin + client)
   - Auth code validation
   - Modern purple gradient UI

### VPS Services

- **Proxmox Integration**: VM/CT provisioning (planned)
- **Resource Management**: CPU, RAM, disk allocation
- **Template-based Deployment**: Ubuntu, Debian, CentOS

### Client Portal

- **Dashboard**: Service overview
- **Domain Management**: DNS records, WHOIS privacy
- **Billing**: Invoice history, payment methods

---

## 🗄️ Database Schema

### Core Tables

#### `domain_zones`
```sql
CREATE TABLE domain_zones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zone VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    price_registration DECIMAL(10,2),
    price_transfer DECIMAL(10,2),
    price_renewal DECIMAL(10,2),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Purpose**: Stores domain zone information and pricing

**Example Data**:
```sql
INSERT INTO domain_zones (zone, price_registration, price_transfer, price_renewal) VALUES
('.ua', 300.00, 300.00, 300.00),
('.com', 450.00, 400.00, 450.00),
('.net', 500.00, 450.00, 500.00);
```

#### `domain_transfer_requests`
```sql
CREATE TABLE domain_transfer_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) NOT NULL,
    zone VARCHAR(50) NOT NULL,
    auth_code VARCHAR(255) NOT NULL,
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255) NOT NULL,
    client_phone VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_domain (domain),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);
```

**Purpose**: Tracks domain transfer requests

**Status Flow**:
- `pending` → New request submitted
- `processing` → Admin reviewing/processing
- `completed` → Transfer successful
- `failed` → Transfer failed/rejected

#### `users`
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `vps_instances`
```sql
CREATE TABLE vps_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    proxmox_vmid INT,
    hostname VARCHAR(255),
    ip_address VARCHAR(45),
    cpu_cores INT,
    ram_mb INT,
    disk_gb INT,
    os_template VARCHAR(100),
    status ENUM('active', 'suspended', 'terminated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Migration File

See `/migrations/001_create_domain_zones.sql` for complete schema.

---

## 📁 File Structure

### Pages (`/pages/`)

```
pages/
├── domains/
│   ├── dns.php           # DNS lookup tool
│   ├── whois.php         # WHOIS lookup tool
│   ├── transfer.php      # Domain transfer form
│   └── register.php      # Domain registration
├── client/
│   ├── dashboard.php     # Client dashboard
│   ├── domains.php       # Domain management
│   └── billing.php       # Billing & invoices
└── vps/
    ├── create.php        # VPS creation wizard
    └── manage.php        # VPS management
```

### API Endpoints (`/api/`)

```
api/
├── domains/
│   ├── dns.php           # DNS query API
│   ├── whois.php         # WHOIS query API
│   └── transfer.php      # Transfer submission API
└── vps/
    └── provision.php     # VPS provisioning API
```

### Assets (`/assets/`)

```
assets/
├── css/
│   ├── main.css          # Global styles
│   ├── pages/
│   │   ├── dns-lookup.css
│   │   ├── whois-lookup.css
│   │   └── transfer-form.css
│   └── components/
│       └── hero.css
├── js/
│   ├── dns-lookup.js
│   ├── whois-lookup.js
│   └── transfer-form.js
└── images/
    └── logos/
```

### Includes (`/includes/`)

```
includes/
├── config.php            # Main configuration
├── db_connect.php        # Database connection
├── header.php            # Global header
├── footer.php            # Global footer
└── functions.php         # Helper functions
```

### Configuration

Configuration is managed through:
- `/includes/config.php` - Main site configuration with database, Proxmox, and WHMCS settings
- `/includes/classes/ProxmoxManager.php` - Proxmox VE 9 integration class (fully configured)

---

## 📄 Pages & Functionality

### DNS Lookup (`/pages/domains/dns.php`)

**Purpose**: Query DNS records for any domain

**Features**:
- Support for 8 record types: A, AAAA, MX, CNAME, TXT, NS, SOA, SRV
- Real-time domain validation
- Quick type selector buttons
- Responsive results table
- Green gradient hero section

**API Integration**:
- Endpoint: `/api/domains/dns.php`
- Method: POST
- Parameters: `domain`, `record_type`

**Code Flow**:
```javascript
// assets/js/dns-lookup.js
fetch('/api/domains/dns.php', {
    method: 'POST',
    body: formData
})
→ api/domains/dns.php
→ dns_get_record($domain, $type)
→ Returns JSON with DNS records
```

### WHOIS Lookup (`/pages/domains/whois.php`)

**Purpose**: Query domain registration information

**Features**:
- Universal domain support (ALL TLDs via IANA)
- Parse creation date, expiration, registrar
- Extract name servers
- Privacy protection showcase
- Blue gradient hero section

**API Integration**:
- Endpoint: `/api/domains/whois.php`
- Method: POST
- Parameters: `domain`

**WHOIS Server Logic**:
```php
// api/domains/whois.php
$whois_servers = [
    '.ua' => 'whois.ua',
    '.com' => 'whois.verisign-grs.com',
    '.club' => 'whois.nic.club',
    // ... 20+ popular TLDs
];

// Universal fallback for unknown TLDs
$whois_server = $whois_servers[$tld] ?? 'whois.iana.org';
```

**Socket Connection**:
```php
$fp = fsockopen($server, 43, $errno, $errstr, 10);
fputs($fp, $domain . "\r\n");
$response = stream_get_contents($fp);
```

### Domain Transfer (`/pages/domains/transfer.php`)

**Purpose**: Submit domain transfer requests

**Features**:
- Database-driven pricing from `domain_zones` table
- Real-time price calculation
- Auth code validation
- Client information form
- Email notifications to admin and client
- Purple gradient hero section

**API Integration**:
- Endpoint: `/api/domains/transfer.php`
- Method: POST
- Parameters: `domain`, `auth_code`, `client_name`, `client_email`, `client_phone`

**Price Calculation**:
```php
// pages/domains/transfer.php
$stmt = $pdo->query("
    SELECT zone, price_transfer, price_renewal
    FROM domain_zones
    WHERE is_active = 1 AND price_transfer > 0
");

// Fallback if DB unavailable
$default_prices = [
    '.ua' => 300,
    '.com' => 400,
    '.net' => 450
];
```

**Email Notifications**:
```php
// api/domains/transfer.php
// Admin notification
mail($admin_email, "New Domain Transfer Request", $message);

// Client confirmation
mail($client_email, "Transfer Request Received", $confirmation);
```

---

## 🔌 API Endpoints

### DNS Query API

**Endpoint**: `/api/domains/dns.php`

**Method**: POST

**Request**:
```json
{
    "domain": "example.com",
    "record_type": "A"
}
```

**Response** (Success):
```json
{
    "success": true,
    "domain": "example.com",
    "record_type": "A",
    "results": [
        {
            "type": "A",
            "host": "example.com",
            "ip": "93.184.216.34",
            "ttl": 3600
        }
    ],
    "timestamp": "2025-11-19 12:00:00"
}
```

**Response** (Error):
```json
{
    "error": "Невірний формат домену"
}
```

### WHOIS Query API

**Endpoint**: `/api/domains/whois.php`

**Method**: POST

**Request**:
```json
{
    "domain": "example.com"
}
```

**Response**:
```json
{
    "success": true,
    "domain": "example.com",
    "whois_server": "whois.verisign-grs.com",
    "data": {
        "status": "registered",
        "creation_date": "1995-08-14",
        "expiration_date": "2026-08-13",
        "registrar": "RESERVED-Internet Assigned Numbers Authority",
        "name_servers": ["a.iana-servers.net", "b.iana-servers.net"],
        "raw_data": "..."
    },
    "timestamp": "2025-11-19 12:00:00"
}
```

### Transfer Submission API

**Endpoint**: `/api/domains/transfer.php`

**Method**: POST

**Request**:
```json
{
    "domain": "example.com",
    "auth_code": "ABC123XYZ",
    "client_name": "John Doe",
    "client_email": "john@example.com",
    "client_phone": "+380501234567"
}
```

**Response** (Success):
```json
{
    "success": true,
    "message": "Запит на трансфер успішно отримано",
    "domain": "example.com",
    "price": 400,
    "request_id": 123
}
```

**Response** (Error):
```json
{
    "error": "Невірний формат домену"
}
```

---

## ⚙️ Configuration

### Main Configuration (`/includes/config.php`)

```php
<?php
// Site settings
define('SITE_NAME', 'StormHosting UA');
define('SITE_URL', 'https://sthost.pro');
define('ADMIN_EMAIL', 'admin@sthost.pro');

// Database settings (see db_connect.php)
define('DB_HOST', 'localhost');
define('DB_NAME', 'sthost_db');
define('DB_USER', 'sthost_user');
define('DB_PASS', 'your_password_here');

// Email settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@sthost.pro');
define('SMTP_PASS', 'your_smtp_password');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('SECURE_ACCESS', true);
```

### Database Connection (`/includes/db_connect.php`)

```php
<?php
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    define('DB_AVAILABLE', true);
} catch (PDOException $e) {
    define('DB_AVAILABLE', false);
    error_log("Database connection failed: " . $e->getMessage());
}
```

---

## 💳 WHMCS / FossBilling Integration

### Overview

The platform integrates with WHMCS/FossBilling for billing and client management:

- Automated invoice generation
- Payment processing
- Client account synchronization
- Service provisioning

### Configuration

WHMCS/FossBilling is configured through constants in `/includes/config.php` and environment variables:

```php
// In /includes/config.php
define('WHMCS_URL', 'https://bill.sthost.pro');
define('WHMCS_API_ENABLED', true);

// In .env file (recommended for security)
FOSSBILLING_API_KEY=your_api_key_here
```

**Integration Points:**

1. **VPS List API** (`/api/vps/get_list.php`) - Fetches VPS orders from FossBilling
2. **Client Synchronization** - Maps WHMCS client IDs to local users
3. **Payment Links** - Redirects to billing portal for payments

### How to Configure FossBilling API

1. **Get API Key:**
   - Login to FossBilling admin panel: `https://bill.sthost.pro/admin`
   - Navigate to: **System → Settings → API**
   - Generate new API key
   - Copy the key

2. **Configure Environment Variable:**

   Create `/home/user/sthost_3.0/.env` (if not exists):

   ```bash
   FOSSBILLING_API_KEY=your_api_key_here
   ```

3. **Usage in Code:**

   The API key is loaded via `env()` function:

   ```php
   $api_key = env('FOSSBILLING_API_KEY', '');
   ```

### Integration Usage

**VPS List Fetching** (`/api/vps/get_list.php`):

```php
// Get VPS orders from FossBilling
$api_url = 'https://bill.sthost.pro/api/admin/order/get_list';
$params = ['client_id' => $fossbilling_client_id];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url . '?key=' . $api_key);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
$response = curl_exec($ch);
```

**Client Synchronization:**

Users table includes `whmcs_client_id` field that maps to FossBilling client ID:

```php
$fossbilling_client_id = getFossBillingClientId(); // From session
```

### Testing FossBilling Integration

```bash
# Test API connection
curl -X POST "https://bill.sthost.pro/api/admin/order/get_list?key=YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"client_id": 1}'
```

---

## 🖥️ Proxmox VE 9 Integration

### Overview

**Proxmox integration is ALREADY FULLY CONFIGURED and working!**

The platform uses `/includes/classes/ProxmoxManager.php` (700+ lines) for complete VPS management:

- ✅ Automated VPS provisioning
- ✅ VM/Container management (start/stop/restart/delete)
- ✅ Resource monitoring and usage statistics
- ✅ Template-based deployment
- ✅ Snapshot creation and management
- ✅ VNC console access
- ✅ Disk and RAM resizing

### Configuration

Proxmox is configured through constants in `/includes/config.php`:

```php
// Proxmox VE 9 Configuration
define('PROXMOX_HOST', 'proxmox.sthost.pro');
define('PROXMOX_PORT', 8006);
define('PROXMOX_NODE', 'pve');
define('PROXMOX_USER', 'root');
define('PROXMOX_REALM', 'pam');
define('PROXMOX_PASSWORD', 'your_password'); // OR use token (recommended)
define('PROXMOX_TOKEN_ID', 'root@pam!api-token-id');
define('PROXMOX_TOKEN_SECRET', 'your_token_secret');
define('PROXMOX_VERIFY_SSL', false); // Set true in production
```

### How to Get API Token

1. Open Proxmox web interface: `https://proxmox.sthost.pro:8006`
2. Navigate to: **Datacenter → Permissions → API Tokens**
3. Click **"Add"**
4. Fill in:
   - **User**: `root@pam`
   - **Token ID**: `api-token-sthost`
   - **Privilege Separation**: ✅ **Uncheck**
5. Copy the **Token ID** and **Secret** immediately
6. Update constants in `/includes/config.php`

### ProxmoxManager Class Usage

The `ProxmoxManager` class is already integrated into VPS APIs:

**Available Methods:**
- `createVPS($config)` - Create new VM/CT
- `controlVPS($vmid, $action)` - Start/stop/restart/reset
- `getVPSStatus($vmid)` - Get current status and resources
- `deleteVPS($vmid)` - Delete VM/CT
- `getVNCInfo($vmid)` - Get VNC console access
- `reinstallVPS($vmid, $template_id)` - Reinstall from template
- `createSnapshot($vmid, $name)` - Create backup snapshot
- `resizeRAM($vmid, $new_mb)` - Change RAM allocation
- `resizeDisk($vmid, $disk, $size_gb)` - Expand disk
- `getResourceUsage($vmid)` - CPU/RAM/disk usage
- `listAllVPS()` - List all VMs/CTs
- `getTemplates()` - List available templates

**Example Usage:**

```php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

// Authenticate
if (!$proxmox->authenticate()) {
    die('Authentication failed');
}

// Create VPS
$result = $proxmox->createVPS([
    'name' => 'web-server-01',
    'memory' => 4096,
    'cpu_cores' => 2,
    'disk_size' => 50,
    'os_template' => 'ubuntu',
    'ip_address' => '192.168.1.100',
    'gateway' => '192.168.1.1'
]);

// Start VPS
$proxmox->controlVPS($vmid, 'start');

// Get status
$status = $proxmox->getVPSStatus($vmid);
echo "CPU Usage: {$status['cpu_usage']}%\n";
```

### Integration Points

#### VPS Control API (`/api/vps/control.php`)

Already configured to use ProxmoxManager for start/stop/restart operations.

```php
$proxmox = new ProxmoxManager();
$result = $proxmox->controlVPS($proxmox_vmid, $action, $proxmox_node);
```

#### VPS Deletion API (`/api/vps/delete.php`)

Handles VM deletion through Proxmox and database cleanup.

```php
$proxmox = new ProxmoxManager();
$result = $proxmox->deleteVPS($proxmox_vmid);
```

#### Client VPS Panel (`/client/vps.php`)

Fully functional interface for:
- Viewing VPS list
- Starting/stopping servers
- VNC console access
- Resource monitoring
- VPS deletion

### Testing

Test Proxmox connection:

```bash
# Create test file
cat > /tmp/test_proxmox.php << 'EOF'
<?php
require_once '/var/www/sthost_3.0/includes/config.php';
require_once '/var/www/sthost_3.0/includes/classes/ProxmoxManager.php';

$proxmox = new ProxmoxManager();

if ($proxmox->authenticate()) {
    echo "✅ Proxmox connection successful!\n";
    $result = $proxmox->listAllVPS();
    print_r($result);
} else {
    echo "❌ Proxmox connection failed\n";
}
EOF

php /tmp/test_proxmox.php
```

---

## 🚀 Deployment Guide

### Prerequisites

- **PHP**: 7.4 or higher
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Let's Encrypt or commercial
- **PHP Extensions**: `pdo_mysql`, `curl`, `json`, `mbstring`

### Step 1: Server Setup

#### Install LAMP Stack (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install PHP
sudo apt install php8.1 php8.1-mysql php8.1-curl php8.1-mbstring php8.1-xml -y

# Enable Apache modules
sudo a2enmod rewrite ssl
sudo systemctl restart apache2
```

### Step 2: Deploy Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/yourusername/sthost_3.0.git
cd sthost_3.0

# Set permissions
sudo chown -R www-data:www-data /var/www/sthost_3.0
sudo chmod -R 755 /var/www/sthost_3.0
sudo chmod -R 777 /var/www/sthost_3.0/cache
sudo chmod -R 777 /var/www/sthost_3.0/logs
```

### Step 3: Configure Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE sthost_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sthost_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON sthost_db.* TO 'sthost_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u sthost_user -p sthost_db < /var/www/sthost_3.0/migrations/001_create_domain_zones.sql
```

### Step 4: Configure Application

```bash
# Copy config template
cp /var/www/sthost_3.0/includes/config.example.php /var/www/sthost_3.0/includes/config.php

# Edit configuration
sudo nano /var/www/sthost_3.0/includes/config.php
```

Update database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sthost_db');
define('DB_USER', 'sthost_user');
define('DB_PASS', 'STRONG_PASSWORD_HERE');
```

### Step 5: Configure Apache

```bash
# Create virtual host
sudo nano /etc/apache2/sites-available/sthost.conf
```

Add configuration:

```apache
<VirtualHost *:80>
    ServerName sthost.pro
    ServerAlias www.sthost.pro
    DocumentRoot /var/www/sthost_3.0

    <Directory /var/www/sthost_3.0>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/sthost_error.log
    CustomLog ${APACHE_LOG_DIR}/sthost_access.log combined
</VirtualHost>
```

Enable site:

```bash
sudo a2ensite sthost.conf
sudo systemctl reload apache2
```

### Step 6: Install SSL Certificate

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain certificate
sudo certbot --apache -d sthost.pro -d www.sthost.pro

# Auto-renewal (already configured by Certbot)
sudo certbot renew --dry-run
```

### Step 7: Configure WHMCS (Optional)

1. Follow [WHMCS Integration](#whmcs-integration) section
2. Update `/config/whmcs.php` with your credentials
3. Test API connection: `/test/whmcs_test.php`

### Step 8: Configure Proxmox (Optional)

1. Follow [Proxmox Integration](#proxmox-integration) section
2. Generate API token in Proxmox
3. Update `/config/proxmox.php`
4. Create VM templates
5. Test API connection: `/test/proxmox_test.php`

### Step 9: Populate Domain Zones

```sql
INSERT INTO domain_zones (zone, description, price_registration, price_transfer, price_renewal, is_active) VALUES
('.ua', 'Ukrainian domain', 300.00, 300.00, 300.00, 1),
('.com.ua', 'Ukrainian commercial', 120.00, 120.00, 120.00, 1),
('.kiev.ua', 'Kyiv regional', 150.00, 150.00, 150.00, 1),
('.com', 'Commercial', 450.00, 400.00, 450.00, 1),
('.net', 'Network', 500.00, 450.00, 500.00, 1),
('.org', 'Organization', 450.00, 400.00, 450.00, 1),
('.pro', 'Professional', 600.00, 550.00, 600.00, 1),
('.club', 'Club/Community', 450.00, 400.00, 450.00, 1),
('.online', 'Online business', 350.00, 300.00, 350.00, 1);
```

### Step 10: Final Checks

```bash
# Test DNS lookup
curl -X POST https://sthost.pro/api/domains/dns.php \
  -d "domain=google.com&record_type=A"

# Test WHOIS lookup
curl -X POST https://sthost.pro/api/domains/whois.php \
  -d "domain=google.com"

# Check logs
tail -f /var/www/sthost_3.0/logs/error.log

# Check permissions
ls -la /var/www/sthost_3.0/cache
ls -la /var/www/sthost_3.0/logs
```

---

## 🛠️ Development

### Local Development Setup

```bash
# Clone repository
git clone https://github.com/yourusername/sthost_3.0.git
cd sthost_3.0

# Install dependencies (if using Composer)
composer install

# Start PHP development server
php -S localhost:8000

# Access site
open http://localhost:8000
```

### Code Style

- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: ES6+ with strict mode
- **CSS**: BEM naming convention for classes
- **Indentation**: 4 spaces (PHP), 4 spaces (JS/CSS)

### Security Best Practices

1. **Never commit sensitive data**:
   - Add `/config/whmcs.php` and `/config/proxmox.php` to `.gitignore`
   - Use environment variables for production credentials

2. **Always use prepared statements**:
   ```php
   // Good ✅
   $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);

   // Bad ❌
   $result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
   ```

3. **Escape all output**:
   ```php
   // Good ✅
   echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

   // Bad ❌
   echo $user_input;
   ```

4. **Validate all input**:
   ```php
   // Domain validation
   if (!preg_match('/^([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\\.)+[a-z]{2,}$/i', $domain)) {
       die('Invalid domain');
   }
   ```

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/new-domain-tool

# Make changes and commit
git add .
git commit -m "Add new domain availability checker"

# Push to remote
git push origin feature/new-domain-tool

# Create pull request on GitHub
```

### Database Migrations

Create new migration files in `/migrations/`:

```sql
-- migrations/002_add_vps_tables.sql
CREATE TABLE vps_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    proxmox_vmid INT,
    hostname VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Apply migrations:

```bash
mysql -u sthost_user -p sthost_db < migrations/002_add_vps_tables.sql
```

---

## 📞 Support & Maintenance

### Log Files

- **Application Logs**: `/logs/error.log`
- **WHMCS Logs**: `/logs/whmcs.log`
- **Proxmox Logs**: `/logs/proxmox.log`
- **Apache Logs**: `/var/log/apache2/sthost_*.log`

### Common Issues

#### Issue: DNS lookup returns empty results

**Solution**: Check PHP `dns_get_record()` is enabled:

```bash
php -r "print_r(dns_get_record('google.com', DNS_A));"
```

#### Issue: WHOIS timeout errors

**Solution**: Increase socket timeout in `/api/domains/whois.php`:

```php
$fp = @fsockopen($server, $port, $errno, $errstr, 30); // Increase from 10 to 30
```

#### Issue: Transfer prices showing as 0

**Solution**: Check database connection and `domain_zones` table:

```sql
SELECT zone, price_transfer FROM domain_zones WHERE is_active = 1;
```

### Backup Strategy

#### Database Backup (Daily)

```bash
# Add to crontab: crontab -e
0 2 * * * /usr/bin/mysqldump -u sthost_user -p'PASSWORD' sthost_db > /backups/sthost_db_$(date +\%Y\%m\%d).sql
```

#### File Backup (Weekly)

```bash
# Add to crontab
0 3 * * 0 tar -czf /backups/sthost_files_$(date +\%Y\%m\%d).tar.gz /var/www/sthost_3.0
```

### Monitoring

#### Uptime Monitoring

Use services like:
- UptimeRobot: https://uptimerobot.com
- Pingdom: https://www.pingdom.com

#### Performance Monitoring

```bash
# Install New Relic (optional)
sudo apt install newrelic-php5
```

---

## 📄 License

Copyright © 2025 StormHosting UA. All rights reserved.

---

## 🙏 Credits

- **Design**: Custom modern gradient themes
- **Icons**: Bootstrap Icons
- **Fonts**: System fonts (optimized for performance)

---

## 📝 Changelog

### Version 3.0 (2025-11-19)

- ✨ Complete rewrite of DNS, WHOIS, and Transfer pages
- 🎨 New modern gradient hero sections (purple, blue, green)
- 🔧 Database-driven pricing system
- 🌍 Universal WHOIS support for all TLDs via IANA
- 📧 Email notifications for domain transfers
- ⚙️ WHMCS integration configuration
- 🖥️ Proxmox VE integration configuration
- 📚 Complete documentation

### Version 2.0 (Previous)

- Basic domain tools
- Client portal
- Database schema

---

**For questions or support, contact: admin@sthost.pro**
