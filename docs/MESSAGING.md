Messaging configuration (email + SMS)

Overview

This project sends notification messages after account creation using either Twilio (SMS) or the Laravel mail system (email). To avoid accidental external sends in local development the code now supports a TWILIO_ENABLED toggle and a safe local example (.env.local.example).

Local (recommended)

- Use Mailpit or MailHog to capture outgoing e-mails. A simple docker-compose service for Mailpit will bind on port 1025/8025.
- Disable SMS by setting TWILIO_ENABLED=false in your local .env (or leave TWILIO_* empty).

.mailpit docker-compose example

services:
  mailpit:
    image: axiros/mailpit:latest
    ports:
      - "1025:1025"
      - "8025:8025"

.env (local)

Copy .env.local.example -> .env and adjust as needed. Key local values:

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
TWILIO_ENABLED=false

Production

- Use real SMTP provider credentials (e.g., Mailgun, SendGrid, or Gmail app password). Set MAIL_MAILER=smtp and fill MAIL_USERNAME and MAIL_PASSWORD.
- Enable Twilio by setting TWILIO_ENABLED=true and providing TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN and TWILIO_FROM.

Notes about Gmail

- If using Gmail for SMTP, create an "App Password" (if your account has 2FA) and use it as MAIL_PASSWORD. Gmail may have sending limits; for production prefer a transactional mail service.

Twilio testing

- Twilio provides test credentials that won't actually send SMS. See Twilio docs for test credentials and how to use the special TEST_AUTH_TOKEN and a test-from number.
- To avoid sending real SMS in development either set TWILIO_ENABLED=false or use Twilio test credentials.

How the code decides which service to use

- If TWILIO_ENABLED is explicitly false -> a NullMessageService is used (no-op; logs message attempts).
- If TWILIO_ENABLED is true but Twilio credentials are missing or client construction fails -> falls back to EmailMessageService.

Quick checks

1) Clear config cache after changing .env:
   php artisan config:clear

2) Run a smoke test for account creation (use token auth as in your workflow). If MAIL_MAILER=log, you can inspect laravel.log for email content instead of Mailpit.

3) If you want to enable SMS in a non-production environment, set TWILIO_ENABLED=true and fill TWILIO_* credentials.
