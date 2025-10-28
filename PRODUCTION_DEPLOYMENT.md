# Production Deployment Guide for CyberPanel

## Prerequisites
- CyberPanel with Apache installed
- PHP 8.2+ with required extensions (GD, PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON)
- MySQL/MariaDB database
- Composer installed
- Node.js and NPM installed
- Domain name configured

## Step 1: Clone Repository on Server

```bash
cd /home/yourdomain/public_html
git clone https://github.com/bassemslimani/eventmanager.git .
```

## Step 2: Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node dependencies
npm install

# Build frontend assets
npm run build
```

## Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file with your production settings:

```env
APP_NAME=QRMH
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Qatar

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Mail Configuration (Use your SMTP settings)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_email@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@qrch.online"
MAIL_FROM_NAME="Qatar Rail Creative Hub"

# Queue Configuration
QUEUE_CONNECTION=database

# Cache Configuration
CACHE_STORE=database
SESSION_DRIVER=database
```

## Step 4: Database Setup

```bash
# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

## Step 5: Set Permissions

```bash
# Set correct ownership
chown -R username:username /home/yourdomain/public_html

# Set directory permissions
chmod -R 755 /home/yourdomain/public_html
chmod -R 775 /home/yourdomain/public_html/storage
chmod -R 775 /home/yourdomain/public_html/bootstrap/cache

# Set file permissions
find /home/yourdomain/public_html -type f -exec chmod 644 {} \;
```

## Step 6: Configure Apache

Create/edit `.htaccess` in document root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

Or configure Apache VirtualHost to point to `/public` directory:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /home/yourdomain/public_html/public

    <Directory /home/yourdomain/public_html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /home/yourdomain/logs/error.log
    CustomLog /home/yourdomain/logs/access.log combined
</VirtualHost>
```

## Step 7: Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## Step 8: Setup Queue Worker

Create a supervisor configuration `/etc/supervisor/conf.d/qrmh-worker.conf`:

```ini
[program:qrmh-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/yourdomain/public_html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=username
numprocs=2
redirect_stderr=true
stdout_logfile=/home/yourdomain/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

Then start supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start qrmh-worker:*
```

**Alternative: Using Cron Job**

If supervisor is not available, add to crontab:

```bash
* * * * * cd /home/yourdomain/public_html && php artisan schedule:run >> /dev/null 2>&1
* * * * * cd /home/yourdomain/public_html && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

## Step 9: Setup Scheduled Tasks

Add to crontab:

```bash
crontab -e
```

Add this line:

```bash
* * * * * cd /home/yourdomain/public_html && php artisan schedule:run >> /dev/null 2>&1
```

## Step 10: SSL Certificate

Install SSL certificate using CyberPanel:
1. Go to SSL → Manage SSL
2. Select your domain
3. Issue SSL certificate (Let's Encrypt)

## Step 11: Security Hardening

### 1. Disable directory listing in `.htaccess`:
```apache
Options -Indexes
```

### 2. Protect sensitive files:
```apache
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 3. Update security headers in Apache config:
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"
```

## Step 12: Create Storage Link

```bash
php artisan storage:link
```

## Step 13: Test Application

1. Visit https://yourdomain.com
2. Test login functionality
3. Test event creation
4. Test badge generation and download
5. Test email sending
6. Test QR code scanning

## Monitoring & Maintenance

### Check Logs
```bash
# Application logs
tail -f storage/logs/laravel.log

# Queue worker logs
tail -f storage/logs/worker.log
```

### Clear Cache (when needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Update Application
```bash
cd /home/yourdomain/public_html
git pull origin main
composer install --optimize-autoloader --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart qrmh-worker:*
```

## Troubleshooting

### Issue: 500 Internal Server Error
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify file permissions
- Clear all caches

### Issue: Queue jobs not processing
- Check supervisor status: `sudo supervisorctl status`
- Restart queue workers: `sudo supervisorctl restart qrmh-worker:*`
- Check worker logs: `tail -f storage/logs/worker.log`

### Issue: Assets not loading
- Run `npm run build` again
- Clear browser cache
- Check file permissions on `public/build` directory

### Issue: Email not sending
- Verify SMTP credentials in `.env`
- Check firewall allows outbound SMTP connections
- Test email using: `php artisan tinker` then `Mail::raw('Test', function($msg) { $msg->to('test@example.com')->subject('Test'); });`

## Important Production Settings

Make sure these are set correctly in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Qatar

# Use 'database' for production queue
QUEUE_CONNECTION=database

# Use secure session cookie
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

## Backup Strategy

### Database Backup (Daily)
```bash
# Add to crontab
0 2 * * * mysqldump -u user -p'password' database_name | gzip > /path/to/backup/db_$(date +\%Y\%m\%d).sql.gz
```

### File Backup (Weekly)
```bash
# Add to crontab
0 3 * * 0 tar -czf /path/to/backup/files_$(date +\%Y\%m\%d).tar.gz /home/yourdomain/public_html
```

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- GitHub: https://github.com/bassemslimani/eventmanager
