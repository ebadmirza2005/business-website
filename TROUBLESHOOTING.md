# Payment Integration Troubleshooting Guide

## Quick Diagnosis

### Problem: Payment modal doesn't open

**Check these:**

1. Open browser console (F12 → Console tab)
2. Look for red error messages
3. Check if `.package-btn` buttons exist on page

**Solutions:**

```javascript
// In console, test if elements exist:
document.querySelectorAll('.package-btn').length > 0 // Should be > 0

// Test Stripe loaded:
typeof Stripe // Should be 'function'

// Test config loaded:
fetch('config-frontend.php').then(r => r.json()).then(console.log)
```

---

## Problem: "Stripe is not defined"

**Cause:** Stripe JavaScript library didn't load

**Fix:**
1. Check `script src="https://js.stripe.com/v3/"` in `index.php` head
2. Reload page (Ctrl+F5 for hard refresh)
3. Check internet connection

---

## Problem: "Invalid public key"

**Cause:** Stripe public key not configured properly

**Check:**
1. Open `config-frontend.php` in browser
2. Should show JSON with `stripePublicKey`
3. Should NOT contain "your_public_key"

**Fix:**
1. Edit `.env` file
2. Add: `STRIPE_PUBLIC_KEY=pk_test_YOUR_ACTUAL_KEY`
3. Save file
4. Reload browser (Ctrl+F5)

---

## Problem: "Failed to create checkout session"

**Cause:** Backend payment.php failed

**Check in browser console:**
```javascript
// Test payment endpoint:
fetch('payment.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    packageName: 'Test',
    amount: 9999,
    email: 'test@example.com',
    packageType: 'web-dev'
  })
}).then(r => r.json()).then(console.log)
```

**Common errors:**
- "Invalid email" → Email format wrong
- "Missing required fields" → Check packageName, amount, email
- "Database error" → Database connection issue

---

## Problem: "Payment declined" even with test card

**Solution:**
- Test card MUST be exactly: `4242 4242 4242 4242`
- Not: 4242-4242-4242-4242 or similar
- Enter it exactly as: `4242424242424242` (no spaces/dashes)
- Expiry: Any future date (e.g., 12/25)
- CVC: Any 3 digits (e.g., 123)

---

## Problem: Webhook not triggering

**Check:**
1. Go to Stripe Dashboard → Developers → Webhooks
2. Find your endpoint
3. Check "Recent attempts" at bottom
4. Does it show 200 OK or errors?

**Common webhook errors:**

| Error | Solution |
|-------|----------|
| 404 Not Found | Webhook URL wrong in Stripe dashboard |
| 403 Forbidden | Website firewall blocking Stripe |
| 500 Error | PHP error on webhook.php |
| Signature failed | STRIPE_WEBHOOK_SECRET mismatch in .env |

**Debug webhook:**
1. Add logging to top of `webhook.php`:
```php
error_log('Webhook received: ' . file_get_contents('php://input'));
```
2. Check PHP error logs
3. Trigger test event in Stripe dashboard

---

## Problem: Orders not saved to database

**Check:**
1. Database connection in `.env` correct?
```bash
# Test from terminal:
mysql -h localhost -u root -p database_name -e "SHOW TABLES;"
```

2. Orders table exists?
```sql
SELECT * FROM orders LIMIT 1;
```

3. Database user has INSERT permission?
```sql
GRANT INSERT ON database.* TO 'user'@'localhost';
```

**Fix:**
1. Verify `.env` database credentials
2. Run migration: `migrations_orders.sql`
3. Check MySQL user permissions

---

## Problem: Email not sending

**Check:**
1. SMTP configured in `.env`?
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

2. For Gmail, use "App Password" not regular password:
   - https://myaccount.google.com/apppasswords

**Verify PHPMailer:**
```php
// In webhook.php or test file:
require 'vendor/autoload.php';
$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->isSMTP();
$mail->Host = getenv('SMTP_HOST');
$mail->Port = getenv('SMTP_PORT');
$mail->SMTPAuth = true;
$mail->Username = getenv('SMTP_USER');
$mail->Password = getenv('SMTP_PASS');
$mail->SMTPSecure = 'tls';
// Try to connect
if ($mail->smtpConnect()) {
    echo "✓ SMTP connection successful";
} else {
    echo "✗ SMTP connection failed: " . $mail->ErrorInfo;
}
```

---

## Problem: "Session ID" shows on success page but not order details

**Cause:** check-payment.php failed to verify

In browser console:
```javascript
// Session retrieval might fail:
const sessionId = new URLSearchParams(window.location.search).get('session_id');
fetch('check-payment.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({sessionId})
}).then(r => r.json()).then(console.log)
```

**Solutions:**
1. Verify `STRIPE_SECRET_KEY` in `.env`
2. Check database `orders` table exists
3. Check Stripe API keys are correct

---

## Problem: Multiple payment attempts for same email

**Cause:** Duplicate payment protection not working

**Check in database:**
```sql
SELECT email, COUNT(*) as attempts, status 
FROM orders 
GROUP BY email 
HAVING COUNT(*) > 1;
```

**Fix:** Check payment.php line ~35:
```php
// Select statements should prevent duplicates
```

If not working, manually clean up duplicate pending orders:
```sql
DELETE FROM orders 
WHERE status = 'pending' 
AND created_at < NOW() - INTERVAL 24 HOUR;
```

---

## Problem: "Your card has expired" for valid card

**Cause:** Using wrong test card

**Use these test cards:**

```
EXPIRED:        4000 0069 0000 0009
TO TEST DECLINE: 4000 0000 0000 0002
VALID:          4242 4242 4242 4242
NO FUNDS:       4000 0000 0000 9995
```

**Check:**
- Card number exact?
- Future expiry date? (e.g., 12/26, 05/27)
- Not testing with real card?

---

## Problem: Amount wrong on success page

**Debug:**
```javascript
// In browser console on payment-success.html:
fetch('config-frontend.php').then(r => r.json()).then(console.log)
// Check packageAmount calculation
```

**Check:**
1. Amount in cents? (e.g., 9999 for $99.99)
2. Calculation: `amount / 100` should equal displayed price

---

## Problem: Webhook test from Stripe sends but doesn't process

**Debug webhook.php:**
```php
// Add to top of webhook.php:
error_log('Webhook received at: ' . date('Y-m-d H:i:s'));
error_log('Body: ' . file_get_contents('php://input'));
error_log('Headers: ' . json_encode(getallheaders()));

// This will log to PHP error log
// Check your server error logs
```

**Test webhook endpoint:**
```bash
# From terminal:
curl -X POST https://yourdomain.com/webhook.php \
  -H "Content-Type: application/json" \
  -d '{"type":"test"}'
```

Should return: `{"success":false}` (expected for invalid signature)

---

## Enable Debug Logging

### For payment.php:
```php
error_log('Payment attempt: ' . json_encode($input));
error_log('Session created: ' . $session->id);
error_log('Order saved for: ' . $email);
```

### For webhook.php:
```php
error_log('Webhook type: ' . $event->type);
error_log('Order status updated: ' . $session->id);
```

### View logs:
```bash
# Usually at:
# /var/log/php-errors.log
# /usr/local/var/log/php-errors.log
# Or check php.ini for error_log path

tail -f /var/log/php-errors.log
```

---

## Test Complete Flow

### Step by step:

1. **Test Stripe connection:**
```php
// Create test file: test-stripe.php
<?php
require 'vendor/autoload.php';
try {
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    $balance = \Stripe\Balance::retrieve();
    echo "✓ Stripe connected";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
?>
```

2. **Test Database:**
```sql
SELECT TABLE_NAME FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders';
```

3. **Test Payment:**
- Navigate to website
- Click "Get Started"
- Try test payment with: `4242 4242 4242 4242`

4. **Check Logs:**
- Browser console (F12)
- PHP errors
- Stripe dashboard

---

## Contact Support

If issues persist:

1. **Gather Info:**
   - Browser console errors
   - PHP error logs
   - Stripe dashboard logs
   - .env file (without actual keys)

2. **Report To:**
   - Stripe Support: https://support.stripe.com
   - Your hosting provider (for PHP/database issues)

3. **Double Check:**
   - All keys copied correctly (no extra spaces)
   - Database able to connect
   - Webhook URL using HTTPS
   - Correct test cards (4242...)

---

**Last Updated:** April 12, 2026
