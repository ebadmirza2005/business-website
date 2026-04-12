# Payment Integration Guide - Faaz Pro Tech

## Overview
This project includes **complete real payment integration** using **Stripe**. The system includes:
- ✅ Real payment processing via Stripe Checkout
- ✅ Webhook handling for payment verification
- ✅ Comprehensive error handling (insufficient funds, declined cards, etc.)
- ✅ Email notifications on success/failure
- ✅ Order tracking system
- ✅ Payment status verification
- ✅ Database order management

## Setup Instructions

### 1. Get Stripe API Keys and Webhook Secret

#### Get Stripe Keys:
1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Navigate to **Developers** → **API Keys**
3. Copy:
   - **Publishable Key** (starts with `pk_`)
   - **Secret Key** (starts with `sk_`)

#### Get Webhook Secret:
1. Go to **Developers** → **Webhooks**
2. Click **Add endpoint**
3. URL: `https://yourdomain.com/webhook.php`
4. Select these events:
   - `checkout.session.completed`
   - `charge.failed`
   - `payment_intent.payment_failed`
5. Copy the **Signing secret** (starts with `whsec_`)

### 2. Update Environment Variables

Update your `.env` file:

```env
# Database
DB_HOST=localhost
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=your_db_name

# Stripe API Keys
STRIPE_PUBLIC_KEY=pk_test_your_public_key
STRIPE_SECRET_KEY=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_test_your_webhook_secret

# App URL (for success/cancel redirects)
APP_URL=http://localhost

# SMTP (for email notifications)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
```

⚠️ **IMPORTANT**: Keep `STRIPE_SECRET_KEY` and `STRIPE_WEBHOOK_SECRET` private! Never commit to git.

### 3. Install Dependencies

Stripe PHP library is included in `composer.json`. Install if needed:

```bash
composer install
```

### 4. Create Orders Database Table

Run the migration in your phpMyAdmin or via terminal:

```bash
mysql -u root -p your_db_name < migrations_orders.sql
```

Or manually execute the SQL in your database.

### 5. Update script.js with Stripe Public Key

In `script.js`, find this line and replace with your **public key**:

```javascript
const stripe = Stripe('pk_test_your_public_key');
```

## How the Payment Flow Works

### Customer Journey:
1. **Customer clicks "Get Started"** on any package
2. **Payment modal opens** with package details
3. **Customer enters email** and card information
4. **On submit**, request sent to `payment.php`
5. **Backend validates** the request and creates Stripe Checkout session
6. **Customer redirected** to Stripe's secure checkout page
7. **After payment**, redirected to `payment-success.html`
8. **Frontend verifies** payment status
9. **Webhook triggered** by Stripe to update order status in database
10. **Email notification** sent to customer

### Error Handling:
The system handles these errors gracefully:
- ❌ **Insufficient Funds** → Clear error message
- ❌ **Card Declined** → Advise to use different card
- ❌ **Expired Card** → Request valid card
- ❌ **Invalid Email** → Validate email format
- ❌ **Network Error** → Retry option
- ❌ **Duplicate Payment** → Prevent double payments

## File Structure

```
├── payment.php              # Creates Stripe Checkout session
│                           # Validates input and saves order
│
├── check-payment.php        # Verifies payment status from Stripe
│                           # Updates order in database
│
├── webhook.php              # Handles Stripe webhook events
│                           # Updates order status
│                           # Sends email notifications
│
├── order-status.php         # API to check order status
│
├── payment-success.html     # Success/failure page with verification
│
├── migrations_orders.sql    # Database schema
│
└── script.js               # Frontend payment handling
                           # Error messages and validation
```

## Testing Payment Integration

### Use These Test Card Numbers:

| Scenario | Card Number | Result |
|----------|-------------|--------|
| Success | 4242 4242 4242 4242 | ✅ Payment succeeds |
| Declined | 4000 0000 0000 0002 | ❌ Card declined |
| Insufficient Funds | 4000 0000 0000 9995 | ❌ Insufficient funds |
| Expired Card | 4000 0069 0000 0009 | ❌ Card expired |
| 3D Secure Required | 4000 0025 0000 3155 | ⚠️ 3D Secure auth |

**Test Expiry:** Any future date (e.g., 12/25)  
**Test CVC:** Any 3 digits (e.g., 123)

### Test Flow:
1. Click "Get Started" on any package
2. Enter email and test card details
3. See the error handling in action or "Payment Successful" page

## Backend API Endpoints

### 1. POST `/payment.php`
Creates Stripe Checkout session.

**Request:**
```json
{
    "packageName": "Professional",
    "amount": 149900,
    "email": "customer@example.com",
    "packageType": "web-dev"
}
```

**Success Response:**
```json
{
    "success": true,
    "sessionId": "cs_test_...",
    "sessionUrl": "https://checkout.stripe.com/pay/...",
    "message": "Checkout session created successfully"
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Invalid email address",
    "type": "validation_error"
}
```

### 2. POST `/check-payment.php`
Verifies payment status.

**Request:**
```json
{
    "sessionId": "cs_test_..."
}
```

**Success Response:**
```json
{
    "success": true,
    "isPaid": true,
    "status": "paid",
    "statusMessage": "Payment completed successfully",
    "packageName": "Professional",
    "amount": 149900,
    "customer_email": "customer@example.com"
}
```

### 3. POST `/order-status.php`
Get order details.

**Request:**
```json
{
    "sessionId": "cs_test_..."
}
```

**Response:**
```json
{
    "success": true,
    "order": {
        "id": 1,
        "email": "customer@example.com",
        "package_name": "Professional",
        "status": "completed",
        "amount": "1499.00",
        "created_at": "2026-04-12 10:30:00"
    }
}
```

### 4. POST `/webhook.php` (Stripe)
Receives webhook events from Stripe. Events handled:
- `checkout.session.completed` → Updates order to "completed"
- `charge.failed` → Updates order to "failed" with error message
- `payment_intent.payment_failed` → Updates order to "failed"

## Email Notifications

When a payment succeeds, the customer receives an email with:
- ✅ Payment confirmation
- 📦 Package details
- 💰 Amount paid
- 📧 Next steps information

This is handled by `webhook.php` → `sendPaymentSuccessEmail()` function using PHPMailer.

## Database Schema

### orders table

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| email | VARCHAR(255) | Customer email |
| package_name | VARCHAR(255) | Package purchased |
| package_type | VARCHAR(50) | web-dev or app-dev |
| amount | DECIMAL(10,2) | Amount in dollars |
| session_id | VARCHAR(255) | Stripe session ID (UNIQUE) |
| payment_intent | VARCHAR(255) | Stripe payment intent ID |
| status | VARCHAR(50) | pending/completed/failed |
| error_message | TEXT | Error details if failed |
| created_at | TIMESTAMP | Order creation time |
| updated_at | TIMESTAMP | Last update time |

## Security Features

✅ **PCI Compliance**: Card details handled by Stripe, not stored on server  
✅ **HTTPS Required**: All payment communication encrypted  
✅ **Webhook Verification**: Stripe signature validated  
✅ **Input Validation**: All inputs sanitized and validated  
✅ **Email Validation**: Proper email format checking  
✅ **Amount Validation**: Prevent invalid amounts  
✅ **Duplicate Prevention**: Check for pending orders within 1 hour  

## Troubleshooting

### "Invalid API Key"
- Check `.env` file has correct `STRIPE_SECRET_KEY`
- Verify you're using the same environment (test vs live)
- Test keys start with `pk_test_` or `sk_test_`

### Payment modal not opening
- Check browser console for JavaScript errors
- Verify Stripe.js library is loaded: `https://js.stripe.com/v3/`
- Check that `.package-btn` elements exist in HTML

### Orders not being saved
- Confirm database connection in `.env`
- Verify `orders` table exists: `SHOW TABLES;`
- Check MySQL user has INSERT permission: `GRANT INSERT ON database.* TO 'user'@'localhost';`

### Webhook not triggering
- Verify webhook secret in `.env`
- Check webhook URL is publicly accessible
- Verify SSL/HTTPS certificate is valid
- Monitor webhook logs in Stripe Dashboard

### Email not sending
- Verify SMTP credentials in `.env`
- Check PHPMailer installation: `composer require phpmailer/phpmailer`
- For Gmail: Use App Password, not regular password
- Check server firewall allows SMTP port 587

## Production Checklist

Before going live, ensure:

- [ ] Switch to **live keys** (starts with `pk_live_` and `sk_live_`)
- [ ] Update `APP_URL` to your production domain
- [ ] Set up webhook with production URL
- [ ] Configure SMTP for email notifications
- [ ] Test with real payment method (small amount)
- [ ] Review Stripe Dashboard settings
- [ ] Enable 3D Secure for card security
- [ ] Set up email templates for notifications
- [ ] Create refund/dispute procedures
- [ ] Monitor transaction logs regularly

## Next Steps

1. **Invoice Generation** - Create PDF invoices after payment (optional)
2. **Subscription Support** - Add recurring billing (if needed)
3. **Admin Dashboard** - View and manage orders
4. **Refund Processing** - Handle customer refunds
5. **Analytics** - Track payment metrics

## Support & Resources

- **Stripe Docs**: https://stripe.com/docs
- **Stripe Test Mode**: https://stripe.com/docs/testing
- **PHPMailer**: https://github.com/PHPMailer/PHPMailer
- **Stripe Webhooks**: https://stripe.com/docs/webhooks

---

**Last Updated:** April 12, 2026  
**Version:** 1.0 - Complete Real Payment Integration
