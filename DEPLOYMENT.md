# Deployment Guide for FaraTech

## Prerequisites

### Server Requirements
- **Web Server**: Apache (with mod_rewrite) or Nginx
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3 or higher
- **SSL Certificate**: Recommended for production
- **Disk Space**: Minimum 1GB free space

### Required PHP Extensions
- PDO
- PDO_MySQL
- mbstring
- json
- curl
- gd
- zip
- fileinfo

## Deployment Steps

### 1. Prepare the Server

#### For Apache:
```bash
# Install required packages
sudo apt update
sudo apt install apache2 php8.0 php8.0-mysql php8.0-pdo php8.0-mbstring php8.0-json php8.0-curl php8.0-gd php8.0-zip php8.0-fileinfo

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### For Nginx:
```bash
# Install required packages
sudo apt update
sudo apt install nginx php8.0-fpm php8.0-mysql php8.0-pdo php8.0-mbstring php8.0-json php8.0-curl php8.0-gd php8.0-zip php8.0-fileinfo

# Configure Nginx (see nginx.conf example below)
sudo systemctl restart nginx
```

### 2. Upload Files

#### Option A: Using Git (Recommended)
```bash
# Clone the repository
git clone https://github.com/Mahsa-Arjmand/FaraTech.git /var/www/faratech

# Navigate to the directory
cd /var/www/faratech

# Switch to the deployment branch
git checkout mahsa

# Or merge to master if ready
git checkout master
git merge mahsa
```

#### Option B: Using FTP/SFTP
- Upload all files to the server
- Ensure .htaccess files are uploaded
- Upload to appropriate directories (frontend/ and backend/)

### 3. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit the .env file with your production values
nano .env
```

Update the following values:
- `DB_HOST`: Your database host
- `DB_NAME`: Your database name
- `DB_USER`: Your database username
- `DB_PASS`: Your database password
- `SITE_URL`: Your production URL (e.g., https://faratech.ir)
- `ADMIN_PASSWORD`: Strong password for admin
- `APP_ENV`: Set to `production`

### 4. Set File Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/faratech

# Set permissions
sudo chmod -R 755 /var/www/faratech
sudo chmod -R 775 /var/www/faratech/backend/uploads
sudo chmod -R 775 /var/www/faratech/backend/logs
sudo chmod 644 /var/www/faratech/.env
```

### 5. Setup Database

#### Create Database and User
```sql
CREATE DATABASE FaraTech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'faratech_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON FaraTech.* TO 'faratech_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Import Database Schema
```bash
# If you have a database export
mysql -u faratech_user -p FaraTech < database_export.sql

# Or run the setup script
php backend/setup_database.php
```

#### Update Database Configuration
Edit `backend/config/database.php`:
```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'FaraTech');
define('DB_USER', getenv('DB_USER') ?: 'faratech_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_password');
```

### 6. Configure Web Server

#### Apache Configuration
Create `/etc/apache2/sites-available/faratech.conf`:
```apache
<VirtualHost *:80>
    ServerName faratech.ir
    ServerAlias www.faratech.ir
    DocumentRoot /var/www/faratech/frontend

    <Directory /var/www/faratech/frontend>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/faratech/backend>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/faratech_error.log
    CustomLog ${APACHE_LOG_DIR}/faratech_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName faratech.ir
    ServerAlias www.faratech.ir
    DocumentRoot /var/www/faratech/frontend

    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/chain.crt

    <Directory /var/www/faratech/frontend>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/faratech/backend>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/faratech_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/faratech_ssl_access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite faratech.conf
sudo systemctl reload apache2
```

#### Nginx Configuration
Create `/etc/nginx/sites-available/faratech`:
```nginx
server {
    listen 80;
    server_name faratech.ir www.faratech.ir;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name faratech.ir www.faratech.ir;

    root /var/www/faratech/frontend;
    index index.html;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Frontend
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api/ {
        alias /var/www/faratech/backend/api/;
        try_files $uri $uri/ =404;
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }

    # Backend Admin
    location /admin/ {
        alias /var/www/faratech/backend/admin/;
        try_files $uri $uri/ /admin/login.php;
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }
    }

    # Block access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ /\.(env|log|sql|bak|old)$ {
        deny all;
    }
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/faratech /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Enable HTTPS (SSL)

#### Using Let's Encrypt (Free)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d faratech.ir -d www.faratech.ir

# Auto-renewal is configured automatically
```

#### For Nginx:
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d faratech.ir -d www.faratech.ir
```

### 8. Update Security Settings

#### Enable HTTPS in .htaccess
Uncomment these lines in `backend/.htaccess` and `frontend/.htaccess`:
```apache
# Enable HTTP Strict Transport Security (HTTPS only)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### Update Session Security
In `backend/includes/auth.php`, change:
```php
ini_set('session.cookie_secure', 1); // Enable when using HTTPS
```

### 9. Create Required Directories

```bash
# Create upload directories
mkdir -p /var/www/faratech/backend/uploads/job_applications
mkdir -p /var/www/faratech/backend/uploads/projects

# Create log directory
mkdir -p /var/www/faratech/backend/logs

# Set permissions
chmod 775 /var/www/faratech/backend/uploads
chmod 775 /var/www/faratech/backend/logs
```

### 10. Test the Deployment

#### Test Frontend
- Visit: https://faratech.ir
- Check all pages load correctly
- Test language switching
- Test contact form
- Test portfolio links

#### Test Backend
- Visit: https://faratech.ir/admin/
- Login with admin credentials
- Test dashboard
- Test job application management
- Test project management

#### Test API
```bash
# Test projects API
curl https://faratech.ir/api/public_projects.php?lang=fa

# Test job application API
curl -X POST https://faratech.ir/api/job_application.php \
  -F "position_id=1" \
  -F "full_name=Test" \
  -F "email=test@example.com" \
  -F "lang=fa"
```

### 11. Post-Deployment Checklist

- [ ] SSL certificate is working
- [ ] All pages load without errors
- [ ] Database connection is working
- [ ] File uploads are working
- [ ] Admin panel is accessible
- [ ] Contact form sends emails
- [ ] SEO meta tags are present
- [ ] robots.txt is accessible
- [ ] sitemap.xml is accessible
- [ ] Security headers are present
- [ ] No console errors in browser
- [ ] Mobile responsiveness works
- [ ] Images load correctly
- [ ] All links work

### 12. Monitoring and Maintenance

#### Set Up Monitoring
- Google Analytics
- Google Search Console
- Server monitoring (Uptime, CPU, Memory)
- Error log monitoring

#### Regular Maintenance Tasks
- Weekly: Check error logs
- Monthly: Update dependencies
- Quarterly: Security audit
- Annually: Full backup and restore test

#### Backup Strategy
```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u faratech_user -p FaraTech > /backups/faratech_$DATE.sql

# File backup
tar -czf /backups/faratech_files_$DATE.tar.gz /var/www/faratech

# Keep backups for 30 days
find /backups -name "faratech_*" -mtime +30 -delete
```

Add to crontab:
```bash
# Daily backup at 2 AM
0 2 * * * /path/to/backup.sh
```

## Troubleshooting

### Common Issues

#### 500 Internal Server Error
- Check .htaccess syntax
- Check PHP error logs
- Verify file permissions
- Check database connection

#### Database Connection Failed
- Verify database credentials
- Check MySQL is running
- Check firewall settings
- Verify database exists

#### File Upload Not Working
- Check upload directory permissions
- Verify PHP upload settings in php.ini
- Check disk space
- Verify file size limits

#### Admin Panel Not Accessible
- Check session settings
- Verify auth.php is configured
- Check logs directory permissions
- Clear browser cookies

#### SSL Certificate Issues
- Verify certificate path
- Check certificate expiration
- Verify domain matches certificate
- Check SSL configuration

## Security Best Practices

1. **Keep software updated**
   - Update PHP regularly
   - Update MySQL/MariaDB
   - Update Apache/Nginx
   - Update OS packages

2. **Regular security audits**
   - Review access logs
   - Check for failed login attempts
   - Scan for vulnerabilities
   - Review file permissions

3. **Backup regularly**
   - Daily database backups
   - Weekly file backups
   - Test restore procedures
   - Store backups off-site

4. **Monitor performance**
   - Page load times
   - Server resources
   - Error rates
   - User experience

## Support

For deployment issues or questions:
- Check SECURITY.md for security guidelines
- Check SEO.md for SEO recommendations
- Review error logs in `backend/logs/`
- Check PHP error logs
- Contact development team

## Additional Resources

- [Apache Documentation](https://httpd.apache.org/docs/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Let's Encrypt](https://letsencrypt.org/)
