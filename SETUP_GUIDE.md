# Real Payment Integration Setup

## Quick Start

Your website now has **complete, real payment integration** with Stripe. Follow these steps to get it working:

## Step 1: Get Your Stripe Keys (5 minutes)

1. Create account at https://dashboard.stripe.com
2. Go to **Developers** → **API Keys**
3. Copy these two keys:
   - `Publishable Key` (looks like: `pk_test_...`)
   - `Secret Key` (looks like: `sk_test_...`)

## Step 2: Setup Webhook (5 minutes)

1. In Stripe Dashboard, go to **Developers** → **Webhooks**
2. Click **Add endpoint**
3. Enter your URL: `https://yourdomain.com/webhook.php`
4. Select events:
   - ✓ `checkout.session.completed`
   - ✓ `charge.failed`
   - ✓ `payment_intent.payment_failed`
5. Copy the **Signing secret** (looks like: `whsec_test_...`)

## Step 3: Update .env File (2 minutes)

Edit your `.env` file and add:

```env
# Stripe Keys
STRIPE_PUBLIC_KEY=pk_test_YOUR_KEY_HERE
STRIPE_SECRET_KEY=sk_test_YOUR_KEY_HERE
STRIPE_WEBHOOK_SECRET=whsec_test_YOUR_KEY_HERE

# Your website URL (important for redirects)
APP_URL=https://yourdomain.com
```

Replace `YOUR_KEY_HERE` with actual keys from Stripe.

## Step 4: Create Database Table (1 minute)

Run this in phpMyAdmin or your database tool:

```sql
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    package_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    payment_intent VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_session (session_id),
    INDEX idx_status (status),
    INDEX idx_payment_intent (payment_intent)
);
```

## Step 5: Test It! (2 minutes)

1. Open your website
2. Click **"Get Started"** on any package
3. Use this test card: `4242 4242 4242 4242`
4. Any future date for expiry (e.g., `12/25`)
5. Any 3 digits for CVC (e.g., `123`)

✅ **It should show: "Payment Successful!"**

## How It Works - User Sees This:

```
1. Customer clicks "Get Started"
        ↓
2. Payment modal opens with:
   - Package name
   - Price
   - Email field
   - Card input
        ↓
3. Customer enters email & card
        ↓
4. Clicks "Pay Now"
        ↓
5. Redirected to Stripe checkout
        ↓
6. After payment → Success page
        ↓
7. Confirmation email sent
```

## Error Handling - Built In

These errors are automatically handled:

| Error | User Sees |
|-------|-----------|
| Insufficient funds | "Insufficient funds. Please use a different payment method." |
| Card declined | "Your card was declined. Please use a different card." |
| Expired card | "Your card has expired. Please use a different card." |
| Invalid email | "Please enter a valid email address" |
| Network error | "Network error. Please check your connection" |

## Test Different Scenarios

### Test Successful Payment
- Card: `4242 4242 4242 4242`
- Result: ✅ Payment succeeds

### Test Card Decline
- Card: `4000 0000 0000 0002`
- Result: ❌ Card declined error

### Test Insufficient Funds
- Card: `4000 0000 0000 9995`
- Result: ❌ Insufficient funds error

### Test Expired Card
- Card: `4000 0069 0000 0009`
- Result: ❌ Card expired error

**Expiry:** Any future date (12/25, 05/30, etc.)  
**CVC:** Any 3 digits (123, 999, etc.)

## Important Files

| File | Purpose |
|------|---------|
| `payment.php` | Handles payment creation |
| `check-payment.php` | Verifies payment status |
| `webhook.php` | Receives updates from Stripe |
| `payment-success.html` | Shows payment result |
| `config-frontend.php` | Provides Stripe key to frontend |
| `migrations_orders.sql` | Database schema |

## Email Notifications (Optional)

To send confirmation emails, update `.env` with SMTP details:

```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

> For Gmail: Generate an "App Password" instead of using regular password

## Database - What Gets Saved

When a customer makes a payment, this is automatically saved:

| Field | Example |
|-------|---------|
| email | customer@example.com |
| package_name | Professional |
| package_type | web-dev |
| amount | 1499.00 |
| session_id | cs_test_123... |
| status | completed / pending / failed |
| created_at | 2026-04-12 10:30:00 |

Check orders in your database:
```sql
SELECT * FROM orders;
```

## Common Issues & Solutions

### Issue: "Invalid API Key"
**Solution:** 
- Check your `.env` file
- Make sure you copied the key correctly
- Test keys start with `pk_test_` or `sk_test_`

### Issue: Payment Modal Doesn't Open
**Solution:**
- Open browser console (F12)
- Check for JavaScript errors
- Make sure Stripe.js loaded

### Issue: "Insufficient funds" even with test card
**Solution:**
- Use the exact test card: `4242 4242 4242 4242`
- Not real funds needed for test cards

### Issue: Webhook Not Working
**Solution:**
- Make sure webhook URL is HTTPS
- Check webhook secret is correct in `.env`
- Verify endpoint selected in Stripe dashboard

## Moving to Production

When you're ready for real money:

1. Get **live keys** from Stripe (start with `pk_live_`)
2. Update `.env` with live keys
3. Test with small real payment ($1)
4. Update `APP_URL` to your live domain
5. Enable 3D Secure in Stripe settings

⚠️ **Keep secret keys private! Never share or commit to git.**

## Need Help?

- **Stripe Docs**: https://stripe.com/docs
- **Test Cards**: https://stripe.com/docs/testing
- **Email Issues**: Check `PAYMENT_INTEGRATION.md`

---

**You now have professional payment processing! 🎉**

Customers can securely pay for your services with automatic order tracking and email confirmations.
