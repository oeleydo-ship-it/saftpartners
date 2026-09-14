# SAF Partners Corporate Website

Laravel 12 + Inertia + React/TypeScript corporate site and CMS, recreated from the supplied SAF Partners visual reference.

## Architecture

- Laravel owns routing, authentication, authorization, validation, persistence, uploads, rate limiting, mail and security headers.
- Inertia renders the public website and protected React CMS without a separate API or Node backend.
- SQLite is configured locally; production is ready for MySQL 8 through environment variables.
- Public copy is stored in `site_settings`, with ordered collections for `markets` and `team_members`.

## Database schema

- `users`: CMS users with `super_admin`, `admin`, or `editor` roles.
- `site_settings`: typed key/value CMS content.
- `markets`, `team_members`: ordered, active/inactive, soft-deletable public collections.
- `contact_submissions`: enquiries and workflow status.
- `media`: safe paths, MIME metadata, size and alt text.
- `audit_logs`: actor, action, entity, request details and timestamp.
- Laravel framework tables: sessions, password resets, jobs, failed jobs and cache.

## Routes and React structure

Public routes are `/`, `/about`, `/markets`, `/team`, `/contact`, `/privacy-policy`, `/terms`, `/sitemap.xml`, and `/robots.txt`. Contact submissions use `POST /contact`; authentication and the CMS live under `/login` and `/admin`. Run `php artisan route:list --except-vendor` for the full route list.

- `resources/js/Pages/Public/Home.tsx`: responsive screenshot-matched home and contact form.
- `resources/js/Pages/Public/Page.tsx`: policy/terms and canonical page handling.
- `resources/js/Pages/Admin/Dashboard.tsx`: content, market/team CRUD, contacts, media and audit UI.
- `resources/css/app.css`: corporate public design, breakpoints and CMS UI.

## CMS

The admin can edit all public copy and links, create/update/archive/reorder and activate markets/team members, upload/delete media, copy media URLs, review enquiries, update status and inspect audit history. Mutations persist in the database and are authorized server-side.

Local seeded administrator (change immediately):

```text
admin@safpartners.ae
ChangeMe123!
```

Override with `ADMIN_EMAIL` and `ADMIN_PASSWORD` before seeding.

## Security

- CSRF-protected session authentication; public registration is disabled.
- Login/password-reset throttling plus contact and upload rate limits.
- Form Requests and authorization middleware/Gates; editors cannot delete content.
- MIME/content-based uploads limited to JPEG, PNG, WEBP and PDF; SVG/executables are rejected.
- Random storage names; SMTP configuration is never shared with React.
- CSP with a per-request nonce, HSTS on HTTPS, deny framing, no sniffing, referrer and permissions policies.
- React text escaping, no public rich HTML, honeypot and bounded input lengths.
- Audit records for content, collection, media and contact-status changes.

## SMTP and queues

Set only in `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=website@example.com
MAIL_FROM_NAME="SAF Partners"
MAIL_CONTACT_TO=contact@safpartners.ae
QUEUE_CONNECTION=database
```

Run `php artisan queue:work --tries=3 --backoff=10`. Enquiries are committed before mail is queued.

## Local installation

Requirements: PHP 8.2+ (8.3 recommended), Composer 2, Node 20+ and npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

For MySQL, set `DB_CONNECTION=mysql` and the host, port, database, username and password before migration.

## Testing

```bash
php artisan test
npm run build
```

Tests cover authentication, disabled registration, public rendering, contact validation/persistence, admin access, role enforcement and rejection of executable/SVG uploads. Tests use isolated in-memory SQLite and do not alter the local CMS.

## Linux production deployment

1. Install PHP-FPM with Laravel extensions, Composer, Node/npm, MySQL 8 and Nginx/Apache.
2. Point the web root to `public/`; deny access to dotfiles and `.env`.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, the HTTPS `APP_URL`, MySQL, SMTP and queue variables, plus `ADMIN_EMAIL` and `ADMIN_PASSWORD` for the initial admin account (it is not created in production without a password).
4. Run `bash deploy.sh` on every deployment. It runs:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The seeder is safe to repeat: it only adds missing default content (settings and markets) and the initial admin, and never overwrites anything edited in the admin CMS.

**Outgoing email.** Admins and super admins configure email in Admin → Email: Microsoft 365 (OAuth 2.0 client credentials over SMTP XOAUTH2, via an Entra app with the `SMTP.SendAsApp` permission), any SMTP server, or log only. Secrets are stored encrypted, and a test email button reports the result. Enquiry notifications are queued, so keep a queue worker running (`php artisan queue:work`).

**First-run setup wizard.** Until a super admin exists, every page redirects to `/setup`, which asks for the super admin's name, email and password, logs them in and then opens the site. Set `SETUP_TOKEN` to a long random value before the first deployment so only someone who knows the key can complete setup (`SETUP_WIZARD=false` disables the wizard).

5. Give the web user write access only to `storage` and `bootstrap/cache`.
6. Run the queue under systemd/Supervisor and schedule `php artisan schedule:run` each minute.
7. Terminate TLS at the web server/proxy and retain application security headers.

## Backup and recovery

- Nightly encrypted MySQL logical backup with daily/weekly/monthly retention.
- Versioned backup of `storage/app/public`; database and media must share a recovery point.
- Keep an off-account/off-region copy, monitor failures and test restores quarterly.
- Snapshot before deployment and use forward migrations in production.

## Assumptions and future work

The supplied copy and imagery are launch defaults. The owner must provide SMTP credentials, approved privacy/terms wording, production admin accounts and infrastructure. Recommended future work: TOTP 2FA, AVIF derivatives, content drafts/preview, full user/role management screens and external monitoring.
