# Implementation Checklist - How to Use .env in Your Code

## Step 1: Update Your Files

### In `payment.php` (add at TOP):
```php
<?php
require_once __DIR__ . '/config-env.php';

$stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
```

### In `webhook.php` (add at TOP):
```php
<?php
require_once __DIR__ . '/config-env.php';

\Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
$webhook_secret = env('STRIPE_WEBHOOK_SECRET');
```

### In `smtp_config.php` (update return array):
```php
<?php
require_once __DIR__ . '/config-env.php';

return [
    "host" => env('SMTP_HOST'),
    "port" => env('SMTP_PORT'),
    "username" => env('SMTP_USER'),
    "password" => env('SMTP_PASS'),
    "from_email" => env('SMTP_FROM_EMAIL'),
    "from_name" => env('SMTP_FROM_NAME'),
    "encryption" => env('SMTP_ENCRYPTION'),
    "debug" => env('SMTP_DEBUG', false),
];
```

### In `db.php` (update database connection):
```php
<?php
require_once __DIR__ . '/config-env.php';

$host = env('DB_HOST', 'localhost');
$user = env('DB_USER');
$pass = env('DB_PASS');
$name = env('DB_NAME');

$conn = new mysqli($host, $user, $pass, $name);
```

---

## Step 2: Folder Structure

```
bussiness-website/
├── .env              ← LOCAL (git ignored) - YOUR REAL CREDENTIALS
├── .env.example      ← TEMPLATE (in git) - Placeholders only
├── config-env.php    ← Helper to load .env
├── config-frontend.php
├── db.php
├── payment.php
├── webhook.php
└── smtp_config.php
```

---

## Quick Test

Create file: `test-env.php`

```php
<?php
require_once __DIR__ . '/config-env.php';

echo "<pre>";
echo "STRIPE_PUBLIC_KEY: " . env('STRIPE_PUBLIC_KEY') . "\n";
echo "STRIPE_SECRET_KEY: " . (env('STRIPE_SECRET_KEY') ? "✓ Loaded" : "✗ Missing") . "\n";
echo "DB_HOST: " . env('DB_HOST') . "\n";
echo "SMTP_HOST: " . env('SMTP_HOST') . "\n";
echo "</pre>";
```

Visit: `http://localhost/bussiness-website/test-env.php`

Should show all values loaded ✅

---

Done! Now your code will use `.env` safely! 🎉
