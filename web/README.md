# Forest Trove Website

A PHP + MySQL website for managing and discovering hand-painted rock treasures hidden in the forest.

## Setup

### 1. Database
- Create a MySQL database named `forest_trove`
- Run `setup/schema.sql` to create all tables and seed data:
  ```bash
  mysql -u root -p forest_trove < setup/schema.sql
  ```

### 2. Configuration
Edit `config/config.php`:
- Set `DB_HOST`, `DB_USER`, `DB_PASS` for your MySQL server
- Set `GOOGLE_MAPS_API_KEY` with your Google Maps JavaScript API key
- Update `SITE_URL` to your domain

### 3. Default Admin Login
- **Username:** `admin`
- **Password:** `password`
- **⚠ Change this immediately!** Log in, then run:
  ```sql
  UPDATE User SET Password = '<bcrypt_hash>' WHERE Username = 'admin';
  ```
  Or add a "change password" page to the admin section.

### 4. Upload Directory Permissions
Ensure the web server can write to:
```
assets/images/uploads/
```
```bash
chmod 775 assets/images/uploads/
```

### 5. PHP Extensions
The following PHP extensions must be enabled in `php.ini`:
```
extension=pdo_mysql
extension=fileinfo
extension=mbstring
extension=curl
extension=gd
extension=intl
extension=mysqli
extension=openssl
extension=zip
extension=exif
extension=sodium
extension=bcmath
```

On Windows, also ensure `extension_dir` points to your PHP `ext` folder, e.g.:
```
extension_dir = "C:\Dev\Tools\php\ext"
```

If no `php.ini` exists, copy `php.ini-development` to `php.ini` in your PHP directory and apply the above changes.

### 6. Web Server
Point your virtual host document root to the `web/` folder.

To run the website locally, run

```
php -S localhost:8080
```
Open http://localhost:8080 in your browser.

---

## Pages

| URL | Description |
|-----|-------------|
| `/` | Home / landing page |
| `/browse.php` | Browse all treasures |
| `/treasure.php?id=...` | Treasure detail page |
| `/map.php` | Google Maps with treasure pins |
| `/claim.php?id=...` | QR code landing — claim a treasure |
| `/login.php` | Admin login |
| `/admin/` | Admin dashboard |
| `/admin/register-treasure.php` | Add a new treasure |
| `/admin/edit-treasure.php?id=...` | Edit / delete a treasure |
| `/admin/forests.php` | Manage forest locations |

---

## QR Codes
Each treasure's QR code should encode:
```
https://yourdomain.com/claim.php?id={TREASURE_ID}
```
The ID is generated when you register the treasure in the admin panel.

## Tech Stack
- **Backend:** PHP 8.x (no framework)
- **Database:** MySQL 5.7+ / MariaDB 10+
- **Frontend:** Vanilla CSS + Vanilla JS
- **Maps:** Google Maps JavaScript API
