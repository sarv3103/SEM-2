@echo off
echo Starting ngrok tunnel for TravelPlanner...
echo.
echo This will create a secure HTTPS tunnel to your localhost
echo The URL will be displayed below - use this for Razorpay webhooks
echo.
echo Press Ctrl+C to stop the tunnel
echo.

ngrok http 80

pause 