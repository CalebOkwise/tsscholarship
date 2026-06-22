# TeamSource Scholarship Backend

This backend extends the existing static landing page. It does not redesign the frontend.

## Added Architecture

- `public/submit.php` handles application form submissions.
- `config/db.php` manages MySQL connection through PDO.
- `config/mail.php` sends admin notifications with PHPMailer over SMTP.
- `includes/functions.php` contains sanitization, CSRF, rate limiting, IP detection, and helpers.
- `includes/auth.php` contains session-based admin authentication.
- `admin/` contains login, dashboard, leads list, lead detail, status update, and logout pages.
- `sql/schema.sql` creates the required MySQL tables and indexes.

## Deployment Steps

1. Create a MySQL database, for example `teamsource_scholarship`.
2. Import `sql/schema.sql` into that database.
3. Copy `.env.example` to `.env` and fill in database and SMTP values.
4. Install PHPMailer:

```bash
composer install --no-dev --optimize-autoloader
```

5. Create the first admin password hash:

```bash
php -r "echo password_hash('YourStrongPasswordHere', PASSWORD_DEFAULT), PHP_EOL;"
```

6. Insert the admin user using the generated hash:

```sql
INSERT INTO admin_users (username, password_hash)
VALUES ('admin', 'PASTE_GENERATED_HASH_HERE');
```

7. Serve the project through PHP-capable hosting. The existing landing page posts to `public/submit.php`.
8. Visit `/admin/login.php` and sign in.

## Cloudflare Tunnel Notes

- Keep MySQL bound to localhost/private network only.
- Expose only the web server through Cloudflare Tunnel.
- Ensure the public hostname points to the PHP web root for this project.
- Use HTTPS through Cloudflare. The PHP session helper respects `X-Forwarded-Proto`.
- Configure Cloudflare WAF/rate limiting for extra protection during paid ad traffic bursts.

Example tunnel flow:

```bash
cloudflared tunnel create teamsource-scholarship
cloudflared tunnel route dns teamsource-scholarship scholarship.example.com
cloudflared tunnel run teamsource-scholarship
```

Map the tunnel service to your local web server, for example `http://localhost:8080`.

## Security Implemented

- PDO prepared statements for all SQL queries.
- Password hashing and verification with `password_hash` / `password_verify`.
- Session regeneration on admin login.
- HTTP-only, SameSite session cookies.
- CSRF tokens on admin login and status updates.
- Honeypot field on the public form.
- Session and database-backed IP rate limiting on submissions.
- Cloudflare-aware IP capture using `CF-Connecting-IP`.
- User agent and IP logging.
- Indexed `email`, `phone`, `status`, `created_at`, and `ip_address` columns.

## PHPMailer

SMTP values live in `.env`:

```env
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=your-user
SMTP_PASSWORD=your-password
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="TeamSource Scholarship"
ADMIN_NOTIFY_EMAIL=admin@example.com
```

If PHPMailer is not installed, submissions are still stored in MySQL and an error is written to the PHP error log.
