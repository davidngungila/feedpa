# ClickPesa API Setup Guide

## Prerequisites

You need to have ClickPesa API credentials to enable live payment processing.

## Configuration Steps

### 1. Get ClickPesa API Credentials

Contact ClickPesa to get:
- API Key
- Client ID
- Callback Secret (optional)

### 2. Update .env File

Add the following environment variables to your `.env` file:

```env
# ClickPesa API Configuration
CLICKPESA_API_BASE_URL=https://api.clickpesa.com/v2
CLICKPESA_API_KEY=your_api_key_here
CLICKPESA_CLIENT_ID=your_client_id_here
CLICKPESA_CALLBACK_SECRET=your_callback_secret_here
CLICKPESA_CALLBACK_URL=/webhooks/clickpesa
CLICKPESA_DEFAULT_CURRENCY=TZS

# Payment Settings
CLICKPESA_MIN_AMOUNT=100
CLICKPESA_MAX_AMOUNT=1000000
CLICKPESA_PAYMENT_TIMEOUT=300

# Logging
CLICKPESA_LOGGING_ENABLED=true
CLICKPESA_LOG_LEVEL=info
CLICKPESA_LOG_CHANNEL=default
```

### 3. Replace Placeholder Values

Replace the placeholder values with your actual ClickPesa credentials:

- `your_api_key_here` - Your ClickPesa API key
- `your_client_id_here` - Your ClickPesa client ID
- `your_callback_secret_here` - Your ClickPesa callback secret (if provided)

### 4. Clear Cache

After updating the .env file, clear the Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test the API

Test the payment system by:
1. Going to `http://127.0.0.1:8001/payment`
2. Fill in the payment form with test data
3. Submit the form
4. Check if you receive a real USSD prompt

## API Features Enabled

With live API configured, you get:

- **Real USSD Push**: Actual USSD prompts sent to customer phones
- **Live Payment Processing**: Real payment transactions
- **Payment Status Updates**: Live status updates from ClickPesa
- **SMS Notifications**: Automatic SMS notifications for payments
- **Webhook Support**: Callback handling for payment status updates

## Testing

### Test Phone Numbers
Use valid Tanzania phone numbers in format: `255712345678`

### Test Amounts
- Minimum: 100 TZS
- Maximum: 1,000,000 TZS

### Test Scenarios
1. **Successful Payment**: Valid phone number with sufficient funds
2. **Insufficient Funds**: Phone number with insufficient balance
3. **Invalid Phone Number**: Wrong format or invalid number
4. **Network Issues**: Test error handling

## Troubleshooting

### Common Issues

1. **"ClickPesa API haimewekwa" error**
   - Check if API credentials are set in .env
   - Run `php artisan config:clear`

2. **"Hakuna njia za malipo zinazopatikana" error**
   - Phone number format is incorrect
   - Phone number doesn't support mobile payments

3. **"Insufficient Funds" error**
   - Customer has insufficient balance
   - SMS notification will be sent

4. **Network/Connection errors**
   - Check internet connection
   - Verify ClickPesa API status
   - Check API credentials

### Debug Information

The system provides detailed debug information in error responses:
- Error type and message
- File and line number
- Full stack trace
- Request data

Check browser console and Laravel logs for detailed debugging.

## Security Notes

- Keep your API credentials secure
- Never commit .env file to version control
- Use HTTPS in production
- Implement proper webhook validation
- Monitor API usage and costs

## Support

For ClickPesa API support:
- Contact ClickPesa support team
- Check ClickPesa documentation
- Review API logs for issues

## Production Deployment

When deploying to production:

1. Use production API credentials
2. Set up proper webhook URLs
3. Configure SSL certificates
4. Monitor payment transactions
5. Set up error alerting
6. Implement proper logging
