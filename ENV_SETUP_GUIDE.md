# 🔐 Environment Setup Guide - Local + Hostinger

## Part 1: LOCAL Development Setup (Your Computer)

### Step 1: Create `.env` File Locally

```bash
# Navigate to your project
cd c:\xampp\htdocs\bussiness-website

# Copy template
copy .env.example .env
```

### Step 2: Edit `.env` with Real Credentials

Open `.env` file and add your REAL keys:

```env
DB_HOST=localhost
DB_USER=your_database_user
DB_PASS=your_database_password
DB_NAME=your_database_name

# Your REAL Stripe Keys (from dashboard.stripe.com)
STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLIC_KEY
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_test_YOUR_WEBHOOK_SECRET

APP_URL=http://localhost/bussiness-website

# Hostinger SMTP
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=your_hostinger_email@domain.com
SMTP_PASS=your_hostinger_password
SMTP_FROM_EMAIL=your_hostinger_email@domain.com
SMTP_FROM_NAME=Your Company Name
SMTP_ENCRYPTION=tls
SMTP_DEBUG=false
```

### Step 3: Load `.env` in Your PHP Code

Create this file: `config-env.php` (ya use existing db.php)

```php
<?php
// Load .env file
$env_file = __DIR__ . '/.env';

if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && $line[0] !== '#') {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Now use in code:
$stripe_secret = $_ENV['STRIPE_SECRET_KEY'];
$smtp_host = $_ENV['SMTP_HOST'];
```

---

## Part 2: HOSTINGER Production Setup

### Step 1: Login to Hostinger Control Panel

1. Go to: https://hpanel.hostinger.com
2. Login with your credentials

### Step 2: Find File Manager or SSH

**Option A: Using File Manager (Easiest)**
- Go to **File Manager**
- Navigate to your domain folder (public_html or www)
- Create `.env` file there

**Option B: Using SSH (Recommended)**
```bash
# SSH into your Hostinger server
ssh username@your-server-ip

# Navigate to your project
cd public_html/business-website

# Create .env using nano
nano .env
```

### Step 3: Add Production Values in `.env`

```env
DB_HOST=your-hostinger-db-host
DB_USER=your_hostinger_db_user
DB_PASS=your_hostinger_db_password
DB_NAME=your_hostinger_database

# PRODUCTION Stripe Keys (Different from test!)
STRIPE_PUBLIC_KEY=pk_live_YOUR_LIVE_PUBLIC_KEY
STRIPE_SECRET_KEY=sk_live_YOUR_LIVE_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_live_YOUR_LIVE_WEBHOOK

APP_URL=https://yourdomain.com

# SMTP - already configured for Hostinger
SMTP_HOST=smtp.hostinger.com
SMTP_PORT=587
SMTP_USER=your_email@yourdomain.com
SMTP_PASS=your_hostinger_email_password
SMTP_FROM_EMAIL=your_email@yourdomain.com
SMTP_FROM_NAME=Your Company Name
SMTP_ENCRYPTION=tls
```

### Step 4: Set Proper Permissions (SSH)

```bash
# Make .env readable but not world-readable (security)
chmod 600 .env

# Verify
ls -la .env
# Output: -rw------- (means only owner can read)
```

### Step 5: Update PHP Code to Use Environment Variables

In your payment files (payment.php, webhook.php):

```php
<?php
// At the top of file
require_once __DIR__ . '/config-env.php';

// Use like this:
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
```

---

## Part 3: Find Your Hostinger Database Details

1. **Hostinger Panel** → **Databases**
2. You'll see:
   - Database Host (usually `localhost` or IP)
   - Database Name
   - Database User
   - Database Password

Put these in `.env`

---

## Part 4: Get Stripe Live Keys

1. Go to: https://dashboard.stripe.com/apikeys
2. Toggle "Viewing test data" OFF (top right)
3. Copy **LIVE** keys:
   - `pk_live_xxxx` → STRIPE_PUBLIC_KEY
   - `sk_live_xxxx` → STRIPE_SECRET_KEY

4. Update Webhook:
   - Go to **Developers** → **Webhooks**
   - Add endpoint for production: `https://faazprotech.com/webhook.php`
   - Copy new signing secret → STRIPE_WEBHOOK_SECRET

---

## 🔄 Complete Workflow

```
┌──────────────────────────────────────┐
│ 1. Local Development                 │
│    .env (git ignored) ✅              │
│    Has: REAL test/dev keys           │
│    Used: localhost:8000              │
└──────────────────────────────────────┘
       ↓ git push/pull (safe)
       ↓ .env.example only (placeholders)
       
┌──────────────────────────────────────┐
│ 2. Production (Hostinger)            │
│    .env (created manually on server) │
│    Has: REAL live keys               │
│    Used: faazprotech.com             │
└──────────────────────────────────────┘
```

---

## ❌ DO NOT

- ❌ Commit `.env` to GitHub (already ignored)
- ❌ Share real keys in Slack/Email
- ❌ Mix test/live keys in production
- ❌ Use localhost URL on production

---

## ✅ Quick Checklist

- [ ] Created `.env` locally with real test keys
- [ ] `.env` file is in `.gitignore` (safe from git)
- [ ] Code loads variables with `$_ENV['KEY_NAME']`
- [ ] Created `.env` on Hostinger server
- [ ] Hostinger `.env` has production/live keys
- [ ] Database credentials are correct
- [ ] `.env` permissions are 600 on server
- [ ] Tested payment with live keys

Done! 🚀
