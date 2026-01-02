---
description: How to deploy the Payment Hub application to Hostinger
---

# Deploying to Hostinger

### 1. Upload Files
- **Option A (Recommended):** Use Git. SSH into your Hostinger account and clone your repository.
- **Option B:** Use FTP (FileZilla) or Hostinger's File Manager to upload all files to the `public_html/payment-hub` directory (or your choice).

### 2. Configure PHP Version
- In Hostinger hPanel, go to **Advanced > PHP Configuration**.
- Ensure **PHP 8.1 or higher** is selected.
- Enable necessary extensions: `mbstring`, `xml`, `curl`, `mysql`, `fileinfo`, `bcmath`.

### 3. Database Setup
- Go to **Databases > MySQL Databases**.
- Create a new database and a user. Write down the name, username, and password.

### 4. Environment Configuration
- In the file manager, rename `.env.example` to `.env`.
- Update the following fields:
  ```env
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://your-domain.com
  
  DB_DATABASE=your_hostinger_db_name
  DB_USERNAME=your_hostinger_user
  DB_PASSWORD=your_hostinger_password
  ```

### 5. Install Dependencies (SSH Required)
- Open the Hostinger SSH terminal.
- Navigate to your project folder: `cd public_html/payment-hub`.
- Run:
  ```bash
  composer install --no-dev --optimize-autoloader
  php artisan key:generate
  php artisan migrate --force
  php artisan storage:link
  ```

### 6. Document Root (CRITICAL)
- Hostinger's default root is `public_html`. Laravel needs `public_html/public`.
- **Method A:** In hPanel, go to **Domains > Subdomains** (if using a subdomain) and set the path to `public_html/payment-hub/public`.
- **Method B:** If using the main domain, move all files from `public` to `public_html` and update `index.php` paths, OR use a `.htaccess` redirect.

### 7. Permissions
- Ensure `storage` and `bootstrap/cache` are writable:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```