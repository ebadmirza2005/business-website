# 🔐 Stripe Configuration Guide

Your payment system is installed, but **Stripe keys are not configured yet**. This is why the payment method field is not showing.

## Quick Setup (2 minutes)

### Step 1: Get Your Stripe Keys

1. Go to [https://dashboard.stripe.com/](https://dashboard.stripe.com/)
2. Sign up or log in to your Stripe account
3. Go to **Developers** (bottom left) → **API Keys**
4. You'll see two keys:
   - **Publishable Key** (starts with `pk_test_` or `pk_live_`)
   - **Secret Key** (starts with `sk_test_` or `sk_live_`)

### Step 2: Add Keys to .env File

Open `.env` file and add these lines:

```env
DB_HOST=localhost
DB_USER=u918387447_users
DB_PASS=Faazpro@123
DB_NAME=u918387447_users

STRIPE_PUBLIC_KEY=pk_test_YOUR_PUBLISHABLE_KEY_HERE
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY_HERE
STRIPE_WEBHOOK_SECRET=whsec_test_YOUR_WEBHOOK_SECRET_HERE
```

Replace:
- `pk_test_YOUR_PUBLISHABLE_KEY_HERE` → Your actual publishable key
- `sk_test_YOUR_SECRET_KEY_HERE` → Your actual secret key
- `whsec_test_YOUR_WEBHOOK_SECRET_HERE` → Webhook secret (get from Developers → Webhooks)

### Step 3: Verify Configuration

After updating `.env`, do a **hard refresh** (Ctrl+F5) on your website.

Then visit: `http://localhost/bussiness-website/test-payment.html`

You should see:
- ✓ Stripe.js Library
- ✓ Frontend Config
- ✓ Stripe Initialization
- ✓ Card Element Container

## Testing with Test Cards

Once configured, use these test cards:

| Test Scenario | Card Number | Expiry | CVC |
|---|---|---|---|
| Successful Payment | `4242 4242 4242 4242` | Any future date | Any 3 digits |
| Card Declined | `4000 0000 0000 0002` | Any future date | Any 3 digits |
| Insufficient Funds | `4000 0000 0000 9995` | Any future date | Any 3 digits |

## Webhook Setup (Optional for Testing)

For testing locally, webhooks are optional. For production:

1. Go to **Developers** → **Webhooks**
2. Click **Add an endpoint**
3. URL: `https://yourdomain.com/webhook.php`
4. Events: Select `charge.failed`, `checkout.session.completed`, `payment_intent.payment_failed`
5. Copy the webhook secret into `.env` as `STRIPE_WEBHOOK_SECRET`

## Common Issues

### ❌ Card Element Still Not Showing?

1. **Check browser console** (F12 → Console tab):
   - Should say `✓ Stripe initialized with public key`
   - Should say `✓ Stripe card element mounted successfully`
   
2. **If you see error about "your_public_key"**:
   - STRIPE_PUBLIC_KEY is not set in .env
   - Make sure you updated .env with real key
   - Hard refresh the page (Ctrl+F5)

3. **Check .env syntax**:
   - No quotes around values: `STRIPE_PUBLIC_KEY=pk_test_xyz` ✓
   - Not `STRIPE_PUBLIC_KEY="pk_test_xyz"` ✗

### ❌ Payment Not Processing?

1. Check that STRIPE_SECRET_KEY is set in .env
2. Verify database table exists: `CREATE TABLE orders (...)`
3. Check webhook signature in webhook.php

## Production Deployment

When moving to production:

1. Generate live Stripe keys (not test keys)
2. Update .env with live keys:
   ```env
   STRIPE_PUBLIC_KEY=pk_live_YOUR_LIVE_KEY
   STRIPE_SECRET_KEY=sk_live_YOUR_LIVE_KEY
   ```
3. Update webhook URL to live domain
4. Set `APP_URL` in .env to your production domain

## Support

If issues persist:
1. Check `test-payment.html` for diagnostic results
2. Look at browser console (F12) for error messages
3. Check browser Network tab to see if config-frontend.php returns your key
4. Verify Stripe.js library loads from CDN

---

**Status**: Once STRIPE_PUBLIC_KEY is configured, card element will display automatically.
