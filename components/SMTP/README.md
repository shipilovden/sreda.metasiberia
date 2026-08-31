# SREDA SMTP Notifications

This component routes OSSN notifications through an authenticated SMTP server.
It uses the PHPMailer implementation already included in OSSN and does not
store credentials in the repository.

For REG.RU hosting mail use the mailbox host, port `465`, and SSL. The SMTP
username must be the complete mailbox address. Port `587` with STARTTLS is
also supported.
