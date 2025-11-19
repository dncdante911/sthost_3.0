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

### Configuration (`/config/`)

```
config/
├── whmcs.php             # WHMCS API configuration
└── proxmox.php           # Proxmox API configuration
```

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

## 💳 WHMCS Integration

### Overview

WHMCS (Web Host Manager Complete Solution) is a billing and client management platform. This integration allows:

- Automated invoice generation for domain transfers
- Payment processing
- Client account synchronization
- Service provisioning

### Configuration File

**Location**: `/config/whmcs.php`

```php
<?php
return [
    // WHMCS API Configuration
    'api' => [
        'enabled' => true,              // Enable/disable integration
        'url' => 'https://bill.sthost.pro',  // Your WHMCS installation URL
        'identifier' => 'YOUR_API_IDENTIFIER',  // See below how to get
        'secret' => 'YOUR_API_SECRET',          // See below how to get
        'access_key' => '',                     // Optional, for IP restriction
    ],

    // WHMCS Product IDs
    'products' => [
        'domain_registration' => 1,     // Product ID for domain registration
        'domain_transfer' => 2,         // Product ID for domain transfer
        'domain_renewal' => 3,          // Product ID for domain renewal
    ],

    // Auto-redirect settings
    'redirect' => [
        'enabled' => true,
        'cart_url' => 'https://bill.sthost.pro/cart.php',
        'domain_transfer_action' => 'a=add&domain=transfer',
    ],

    // Price synchronization
    'prices' => [
        'sync_enabled' => true,         // Sync prices from WHMCS
        'sync_interval' => 3600,        // Sync every hour
        'cache_file' => __DIR__ . '/../cache/whmcs_prices.json',
    ],

    // Webhook settings
    'webhooks' => [
        'enabled' => true,
        'secret' => 'RANDOM_SECRET_KEY_HERE',  // Generate secure random key
        'events' => [
            'DomainTransferCompleted',
            'DomainTransferFailed',
            'DomainRegistered',
        ],
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/whmcs.log',
        'level' => 'info',              // debug, info, warning, error
    ],
];
```

### Getting WHMCS API Credentials

#### Step 1: Generate API Credentials

1. Log into your WHMCS admin panel
2. Navigate to: **Setup → Staff Management → API Credentials**
3. Click **"Generate New API Credential"**
4. Fill in the form:
   - **Credential Name**: "StHost Website Integration"
   - **Generated Credential**: Copy the **Identifier** and **Secret**
5. Under **API Access Control**:
   - Add your website IP address (e.g., `192.168.1.100`)
   - Or use `0.0.0.0/0` for testing (NOT recommended for production)
6. Click **"Save Changes"**

#### Step 2: Update Configuration

Edit `/config/whmcs.php`:

```php
'api' => [
    'enabled' => true,
    'url' => 'https://bill.sthost.pro',          // Your WHMCS URL
    'identifier' => 'AbCdEfGh123456',             // From Step 1
    'secret' => 'xYz789AbCdEf....',               // From Step 1
],
```

### Getting Product IDs

#### Option 1: Via WHMCS Admin

1. Go to: **Setup → Products/Services → Products/Services**
2. Find your domain transfer product
3. Click **Edit**
4. Look at the URL: `configproducts.php?action=edit&id=2`
5. The number after `id=` is your Product ID (e.g., `2`)

#### Option 2: Via Database

```sql
SELECT id, name, type
FROM tblproducts
WHERE type = 'domain';
```

Update in `/config/whmcs.php`:

```php
'products' => [
    'domain_registration' => 1,   // ID from WHMCS
    'domain_transfer' => 2,       // ID from WHMCS
    'domain_renewal' => 3,        // ID from WHMCS
],
```

### Integration Points

#### 1. Domain Transfer Integration

**File**: `/api/domains/transfer.php`

Add WHMCS order creation:

```php
// After successful transfer request submission
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/config/whmcs.php')) {
    $whmcs_config = require $_SERVER['DOCUMENT_ROOT'] . '/config/whmcs.php';

    if ($whmcs_config['api']['enabled']) {
        // Create WHMCS order
        $whmcs_result = createWHMCSOrder([
            'clientid' => getOrCreateWHMCSClient($client_email, $client_name),
            'pid' => $whmcs_config['products']['domain_transfer'],
            'domain' => $domain,
            'billingcycle' => 'annually',
            'paymentmethod' => 'banktransfer'
        ]);

        // Redirect to WHMCS cart
        if ($whmcs_config['redirect']['enabled'] && $whmcs_result['success']) {
            $redirect_url = $whmcs_config['redirect']['cart_url']
                          . '?' . $whmcs_config['redirect']['domain_transfer_action']
                          . '&domain=' . urlencode($domain);
        }
    }
}
```

#### 2. WHMCS API Helper Function

Create `/includes/whmcs_api.php`:

```php
<?php
function whmcsAPI($command, $params = []) {
    $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/whmcs.php';

    if (!$config['api']['enabled']) {
        return ['result' => 'error', 'message' => 'WHMCS API disabled'];
    }

    $post_data = array_merge([
        'identifier' => $config['api']['identifier'],
        'secret' => $config['api']['secret'],
        'action' => $command,
        'responsetype' => 'json',
    ], $params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api']['url'] . '/includes/api.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

    $response = curl_exec($ch);

    if (curl_error($ch)) {
        error_log('WHMCS API Error: ' . curl_error($ch));
        return ['result' => 'error', 'message' => curl_error($ch)];
    }

    curl_close($ch);

    return json_decode($response, true);
}

function createWHMCSOrder($params) {
    return whmcsAPI('AddOrder', $params);
}

function getOrCreateWHMCSClient($email, $name) {
    // Check if client exists
    $result = whmcsAPI('GetClientsDetails', ['email' => $email]);

    if ($result['result'] == 'success') {
        return $result['client']['id'];
    }

    // Create new client
    $name_parts = explode(' ', $name, 2);
    $create_result = whmcsAPI('AddClient', [
        'firstname' => $name_parts[0],
        'lastname' => $name_parts[1] ?? '',
        'email' => $email,
        'password2' => bin2hex(random_bytes(16)),
    ]);

    return $create_result['clientid'] ?? null;
}
```

### Testing WHMCS Integration

#### Test API Connection

Create `/test/whmcs_test.php`:

```php
<?php
require_once '../includes/whmcs_api.php';

// Test GetOrders
$result = whmcsAPI('GetOrders', ['limitnum' => 1]);

echo "<pre>";
print_r($result);
echo "</pre>";

if ($result['result'] == 'success') {
    echo "✅ WHMCS API connection successful!";
} else {
    echo "❌ WHMCS API connection failed: " . $result['message'];
}
```

Access: `https://sthost.pro/test/whmcs_test.php`

### Webhook Configuration (Optional)

To receive notifications from WHMCS when transfers complete:

#### 1. Create Webhook Endpoint

**File**: `/api/webhooks/whmcs.php`

```php
<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';

$config = require $_SERVER['DOCUMENT_ROOT'] . '/config/whmcs.php';

// Verify webhook secret
$headers = getallheaders();
$signature = $headers['X-WHMCS-Signature'] ?? '';

if (!hash_equals($config['webhooks']['secret'], $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

// Parse webhook data
$event = $_POST['event'] ?? '';
$data = $_POST;

// Log webhook
error_log("WHMCS Webhook: $event - " . json_encode($data));

// Handle events
switch ($event) {
    case 'DomainTransferCompleted':
        $domain = $data['domain'];
        // Update database
        $stmt = $pdo->prepare("
            UPDATE domain_transfer_requests
            SET status = 'completed'
            WHERE domain = ?
        ");
        $stmt->execute([$domain]);
        break;

    case 'DomainTransferFailed':
        $domain = $data['domain'];
        $stmt = $pdo->prepare("
            UPDATE domain_transfer_requests
            SET status = 'failed', notes = ?
            WHERE domain = ?
        ");
        $stmt->execute([$data['reason'], $domain]);
        break;
}

http_response_code(200);
echo json_encode(['success' => true]);
```

#### 2. Configure in WHMCS

1. Go to: **Setup → Automation Settings → Webhook Configuration**
2. Click **"Add New Webhook"**
3. Fill in:
   - **URL**: `https://sthost.pro/api/webhooks/whmcs.php`
   - **Events**: Select `DomainTransferCompleted`, `DomainTransferFailed`
   - **Secret**: Copy your secret from `/config/whmcs.php`
4. Save

---

## 🖥️ Proxmox Integration

### Overview

Proxmox Virtual Environment (VE) is an open-source virtualization platform. This integration allows:

- Automated VPS provisioning
- VM/Container management
- Resource monitoring
- Template-based deployment

### Configuration File

**Location**: `/config/proxmox.php`

```php
<?php
return [
    // Proxmox API Configuration
    'api' => [
        'enabled' => true,                      // Enable/disable integration
        'host' => 'proxmox.sthost.pro',         // Proxmox hostname/IP
        'port' => 8006,                         // API port (usually 8006)
        'node' => 'pve',                        // Node name (default: pve)
        'realm' => 'pam',                       // Authentication realm (pam/pve)
        'verify_ssl' => true,                   // Verify SSL certificate
    ],

    // Authentication
    'auth' => [
        'method' => 'token',                    // 'token' (recommended) or 'password'

        // For method = 'token'
        'token_id' => 'root@pam!api-token-id',  // See below how to generate
        'token_secret' => 'YOUR_TOKEN_SECRET',  // See below how to generate

        // For method = 'password' (NOT recommended for production)
        'username' => 'root@pam',
        'password' => '',
    ],

    // VM/CT Defaults
    'defaults' => [
        'storage' => 'local-lvm',               // Storage for disks
        'network_bridge' => 'vmbr0',            // Network bridge
        'nameserver' => '8.8.8.8 8.8.4.4',      // DNS servers
        'searchdomain' => 'sthost.pro',
        'ostype' => 'l26',                      // OS type (l26 for Linux 2.6+)
    ],

    // Templates
    'templates' => [
        'ubuntu_22_04' => [
            'name' => 'Ubuntu 22.04 LTS',
            'template_id' => 9000,              // Template VMID
            'type' => 'lxc',                    // lxc or qemu
            'min_disk' => 8,                    // GB
            'min_ram' => 512,                   // MB
        ],
        'debian_12' => [
            'name' => 'Debian 12',
            'template_id' => 9001,
            'type' => 'lxc',
            'min_disk' => 8,
            'min_ram' => 512,
        ],
        'centos_stream_9' => [
            'name' => 'CentOS Stream 9',
            'template_id' => 9002,
            'type' => 'lxc',
            'min_disk' => 10,
            'min_ram' => 1024,
        ],
    ],

    // Limits and Quotas
    'limits' => [
        'max_vm_per_user' => 5,
        'max_cpu_cores' => 8,
        'max_ram_mb' => 16384,                  // 16 GB
        'max_disk_gb' => 500,
    ],

    // Backup Configuration
    'backup' => [
        'enabled' => true,
        'storage' => 'backup-storage',
        'retention' => 7,                       // Days to keep backups
        'schedule' => '02:00',                  // Backup time (HH:MM)
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'file' => __DIR__ . '/../logs/proxmox.log',
        'level' => 'info',
    ],
];
```

### Generating Proxmox API Token

#### Step 1: Access Proxmox Web Interface

1. Open browser: `https://proxmox.sthost.pro:8006`
2. Login with root credentials

#### Step 2: Create API Token

1. Navigate to: **Datacenter → Permissions → API Tokens**
2. Click **"Add"**
3. Fill in:
   - **User**: `root@pam`
   - **Token ID**: `api-token-sthost` (choose any name)
   - **Privilege Separation**: ✅ **Uncheck** (token will have same permissions as user)
4. Click **"Add"**
5. **IMPORTANT**: Copy the **Secret** immediately (shown only once!)

Example output:
```
Token ID: root@pam!api-token-sthost
Secret: 12345678-1234-1234-1234-123456789abc
```

#### Step 3: Update Configuration

Edit `/config/proxmox.php`:

```php
'auth' => [
    'method' => 'token',
    'token_id' => 'root@pam!api-token-sthost',          // From Step 2
    'token_secret' => '12345678-1234-1234-1234-123456789abc',  // From Step 2
],
```

### Creating VM Templates

Before provisioning, you need to create templates in Proxmox.

#### Option 1: LXC Container Template (Recommended)

```bash
# SSH into Proxmox node
ssh root@proxmox.sthost.pro

# Download Ubuntu 22.04 template
pveam update
pveam download local ubuntu-22.04-standard_22.04-1_amd64.tar.zst

# Create container from template
pct create 9000 local:vztmpl/ubuntu-22.04-standard_22.04-1_amd64.tar.zst \
    --hostname ubuntu-template \
    --memory 512 \
    --net0 name=eth0,bridge=vmbr0,ip=dhcp \
    --storage local-lvm \
    --rootfs local-lvm:8

# Convert to template
pct template 9000
```

#### Option 2: QEMU VM Template

```bash
# Download Ubuntu cloud image
wget https://cloud-images.ubuntu.com/jammy/current/jammy-server-cloudimg-amd64.img

# Create VM
qm create 9000 \
    --name ubuntu-22.04-template \
    --memory 2048 \
    --net0 virtio,bridge=vmbr0 \
    --scsihw virtio-scsi-pci

# Import disk
qm importdisk 9000 jammy-server-cloudimg-amd64.img local-lvm

# Attach disk
qm set 9000 --scsi0 local-lvm:vm-9000-disk-0

# Set boot order
qm set 9000 --boot c --bootdisk scsi0

# Add cloud-init
qm set 9000 --ide2 local-lvm:cloudinit

# Convert to template
qm template 9000
```

### Integration Implementation

#### Proxmox API Helper

Create `/includes/proxmox_api.php`:

```php
<?php
class ProxmoxAPI {
    private $config;
    private $ticket;
    private $csrf_token;

    public function __construct() {
        $this->config = require $_SERVER['DOCUMENT_ROOT'] . '/config/proxmox.php';
    }

    private function getAuthHeaders() {
        if ($this->config['auth']['method'] == 'token') {
            return [
                'Authorization: PVEAPIToken=' . $this->config['auth']['token_id']
                    . '=' . $this->config['auth']['token_secret']
            ];
        } else {
            // Password-based auth (requires login first)
            return [
                'CSRFPreventionToken: ' . $this->csrf_token,
                'Cookie: PVEAuthCookie=' . $this->ticket
            ];
        }
    }

    public function request($endpoint, $method = 'GET', $data = []) {
        $url = 'https://' . $this->config['api']['host']
             . ':' . $this->config['api']['port']
             . '/api2/json' . $endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->config['api']['verify_ssl']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getAuthHeaders());

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code >= 400) {
            throw new Exception("Proxmox API error: HTTP $http_code");
        }

        return json_decode($response, true);
    }

    public function createContainer($params) {
        $node = $this->config['api']['node'];
        $vmid = $this->getNextVMID();

        $data = array_merge([
            'vmid' => $vmid,
            'ostemplate' => 'local:vztmpl/ubuntu-22.04-standard.tar.zst',
            'hostname' => 'ct-' . $vmid,
            'memory' => 512,
            'swap' => 512,
            'cores' => 1,
            'storage' => $this->config['defaults']['storage'],
            'net0' => 'name=eth0,bridge=' . $this->config['defaults']['network_bridge'] . ',ip=dhcp',
            'nameserver' => $this->config['defaults']['nameserver'],
            'password' => bin2hex(random_bytes(16)),
        ], $params);

        return $this->request("/nodes/$node/lxc", 'POST', $data);
    }

    public function getNextVMID() {
        $result = $this->request('/cluster/nextid');
        return $result['data'];
    }

    public function getVMStatus($vmid) {
        $node = $this->config['api']['node'];
        return $this->request("/nodes/$node/lxc/$vmid/status/current");
    }

    public function startVM($vmid) {
        $node = $this->config['api']['node'];
        return $this->request("/nodes/$node/lxc/$vmid/status/start", 'POST');
    }

    public function stopVM($vmid) {
        $node = $this->config['api']['node'];
        return $this->request("/nodes/$node/lxc/$vmid/status/stop", 'POST');
    }
}
```

#### VPS Provisioning API

**File**: `/api/vps/provision.php`

```php
<?php
define('SECURE_ACCESS', true);
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db_connect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/proxmox_api.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

// Validate input
$hostname = trim($_POST['hostname'] ?? '');
$cpu = (int)($_POST['cpu'] ?? 1);
$ram = (int)($_POST['ram'] ?? 512);
$disk = (int)($_POST['disk'] ?? 10);
$template = $_POST['template'] ?? 'ubuntu_22_04';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    exit(json_encode(['error' => 'Authentication required']));
}

try {
    $proxmox = new ProxmoxAPI();
    $config = require $_SERVER['DOCUMENT_ROOT'] . '/config/proxmox.php';

    // Check limits
    if ($cpu > $config['limits']['max_cpu_cores']) {
        throw new Exception('CPU limit exceeded');
    }

    // Create container
    $result = $proxmox->createContainer([
        'hostname' => $hostname,
        'cores' => $cpu,
        'memory' => $ram,
        'rootfs' => $config['defaults']['storage'] . ':' . $disk,
    ]);

    $vmid = $result['data'];

    // Save to database
    $stmt = $pdo->prepare("
        INSERT INTO vps_instances
        (user_id, proxmox_vmid, hostname, cpu_cores, ram_mb, disk_gb, os_template, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->execute([$user_id, $vmid, $hostname, $cpu, $ram, $disk, $template]);

    // Start container
    $proxmox->startVM($vmid);

    echo json_encode([
        'success' => true,
        'vmid' => $vmid,
        'hostname' => $hostname
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

### Testing Proxmox Integration

Create `/test/proxmox_test.php`:

```php
<?php
require_once '../includes/proxmox_api.php';

try {
    $proxmox = new ProxmoxAPI();

    // Test: Get cluster status
    $result = $proxmox->request('/cluster/status');

    echo "<pre>";
    print_r($result);
    echo "</pre>";

    echo "✅ Proxmox API connection successful!";

} catch (Exception $e) {
    echo "❌ Proxmox API connection failed: " . $e->getMessage();
}
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
