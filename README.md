# GLSS Global Logistics Security Platform

## Overview
GLSS is a logistics security portal that lets customers track high-value consignments while operations teams manage shipments, branding, and outgoing notifications from a secure admin dashboard.

### Key Features
- **Public tracking** with destination/recipient highlights and mission control contact ctas.
- **Operations dashboard** featuring sidebar navigation, shipment management, live stats, and quick mail desk.
- **Editable site settings** (branding, hero copy, support contacts, mail sender, SMTP).
- **Configurable mailer** using PHP `mail()` with optional SMTP settings stored in the database.

## Requirements
- PHP 8.1+ with `pdo_mysql` enabled
- MySQL 5.7 / MariaDB 10+ (or compatible)
- Apache / Nginx serving the `public/` directory (or the repo root if using the provided front-controller files)
- Composer is **not** required (project uses native PHP)

## Installation
```bash
# clone repository
git clone https://github.com/<your-account>/glss-logistics.git
cd glss-logistics

# copy files to your web root (example)
cp -R ./ /opt/lampp/htdocs/shipping-website
```

### Configuration (production-first)
In production, place secrets in a `.env` file located at the project root. `config/env.php` loads it automatically and the values override anything in `config/config.php`.

Required keys:
```
GLSS_APP_NAME="GLSS Global Logistics Security Company"
GLSS_DB_DRIVER=mysql
GLSS_DB_HOST=127.0.0.1
GLSS_DB_PORT=3306
GLSS_DB_NAME=glss
GLSS_DB_USER=glss_user
GLSS_DB_PASSWORD=super-secret
GLSS_TRACKING_PREFIX=GLS
GLSS_TRACKING_LENGTH=8
GLSS_ADMIN_USER=admin
GLSS_ADMIN_PASSWORD_HASH=$2y$12$...
```
> Generate the admin hash via `php -r "echo password_hash('StrongPass!23', PASSWORD_BCRYPT);"` and never commit `.env` to Git.

`config/config.php` remains as the default fallback (useful for local development or as a template), but production instances should rely on `.env` values.

### Database schema
Run migrations or import the raw SQL before first boot.
```bash
# migration script (also seeds default settings)
php database/migrate.php

# OR import SQL directly
mysql -u <user> -p <db_name> < database/schema.sql
```

After the schema is live, log into `/admin/login.php` with the admin credentials you set in `.env`.

## Default admin credentials
- **Login URL:** `/admin/login.php`
- **Username:** value of `GLSS_ADMIN_USER`
- **Password:** whatever you hashed for `GLSS_ADMIN_PASSWORD_HASH`

Immediate production tasks:
1. Change the admin password hash using the CLI command above.
2. Update site branding/contact details in the dashboard so the public site reflects your organisation.
3. Configure SMTP in the admin panel (or via direct DB update) so outbound mail uses a secured relay.

## Production deployment checklist
1. **Environment variables**: set all `GLSS_*` keys (database, tracking, admin, mail).
2. **Document root**: point Apache/Nginx to the project root (contains `public/` and admin assets). Ensure `index.php` and `track.php` wrappers are accessible.
3. **Permissions**: web server must read project files; restrict write access to uploaded/temporary directories if you add them later. `.env` should be `600` and owned by the deploy user.
4. **HTTPS**: terminate TLS at your reverse proxy/load balancer.
5. **Database migrations**: run `php database/migrate.php` on every deploy (safe to re-run; it’s idempotent).
6. **Backups**: schedule regular dumps of `shipments` and `settings` tables.
7. **Monitoring**: enable PHP error logging and web server access/error logs for auditability.

## Customisation
### Branding, hero copy & contacts
- Update through the admin dashboard (`Site settings` section). Changes immediately reflect on the public site.

### Mail configuration
- Configure sender identity and SMTP settings in the admin panel. The mail desk uses these values instantly.
- PHP `mail()` is the default; when SMTP is toggled on, the loader adjusts PHP’s mail configuration accordingly. Ensure your hosting provider allows outbound SMTP or uses an internal relay.

## Deployment to pxxl.app
1. Push this repository to GitHub (already done in this workflow).
2. On pxxl.app, create a new app and connect it to the GitHub repo.
3. Set the `GLSS_*` environment variables via the pxxl.app dashboard (do not rely on committed config).
4. Point the document root to the project root (contains `public/`).
5. Run `php database/migrate.php` on the container or import `database/schema.sql`.
6. Optionally, seed initial shipments via the CLI helper below.

## Useful Scripts
- **Seed demo shipment:**
  ```bash
  /opt/lampp/bin/php -r "require '/opt/lampp/htdocs/shipping-website/includes/bootstrap.php'; $config = swiftship_config(); $db = new SwiftShip\\Database($config['database']); $shipments = new SwiftShip\\Shipments($db->getConnection()); echo $shipments->create(['sender_name'=>'Atlas Defense','recipient_name'=>'Secure Labs','origin'=>'Newnan, GA','destination'=>'Berlin, Germany','status'=>'In Transit']);"
  ```
- **Run migrations:** `/opt/lampp/bin/php database/migrate.php`

## Roadmap ideas
- Add notification history with email logs
- Implement attachment support for shipment documents
- Extend admin with activity auditing

## License
This project is released under the MIT License. See `LICENSE` (create if required) for details.
