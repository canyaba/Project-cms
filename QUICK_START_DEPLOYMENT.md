# Quick Deployment Start Guide

## For Busy Developers - 5 Minute Setup

### 1. Prepare Your Code

```bash
# Create environment file
cp .env.example .env

# Update .env with your settings
nano .env  # or edit in your IDE
```

**Required changes in .env:**
```
DB_HOST=your-database-host
DB_NAME=your-database-name
DB_USER=your-database-user
DB_PASSWORD=your-secure-password
APP_ENV=production
APP_DEBUG=false
```

### 2. Run Pre-Deployment Checks

```bash
# Check your environment
php deployment-check.php

# Run security audit
php security-audit.php
```

**Fix any issues reported before proceeding.**

### 3. Set Up Database

```bash
# Import schema and seed data
mysql -u your_user -p your_database < sql/serverside.sql

# Verify installation
mysql -u your_user -p your_database -e "SHOW TABLES;"
```

### 4. Fix File Permissions

```bash
# Directories
chmod 755 ./uploads
chmod 755 ./logs
chmod 755 ./sessions

# Files
chmod 644 .env
chmod 644 *.php
find includes -name "*.php" -exec chmod 644 {} \;
```

### 5. Configure Web Server

#### Apache (.htaccess already included)
- Ensure `mod_rewrite` is enabled
- Go to next step

#### Nginx Configuration
```nginx
# Place in your nginx config
location ~ /\. {
    deny all;
}

location ~ ^/sql/ {
    deny all;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_index index.php;
    include fastcgi_params;
}
```

### 6. Deploy Code

```bash
# Via Git (recommended)
git push origin main

# Via FTP/SFTP
sftp user@host
put -r * /path/to/app/

# Via Docker
docker build -t myapp .
docker run -e DB_HOST=db -p 80:80 myapp
```

### 7. Verify Installation

Visit your domain:
1. **http://yourdomain.com** - Should load homepage
2. **http://yourdomain.com/login.php** - Should load login
3. **Try creating an account** - Should work
4. **Try logging in** - Should authenticate

---

## Platform-Specific Quick Starts

### Railway.app (Recommended for PHP)

```bash
# 1. Install Railway CLI
npm i -g @railway/cli

# 2. Login and init project
railway login
railway init

# 3. Add MySQL
railway add -d mysql

# 4. Set environment variables
railway variables set DB_HOST=$MYSQL_HOSTNAME
# ... repeat for other variables

# 5. Deploy
railway up
```

### Render.com

```bash
1. Go to https://dashboard.render.com
2. Click "New +" → "Web Service"
3. Connect GitHub repository
4. Set Build Command: sh -c 'composer install && php deployment-check.php'
5. Set Start Command: php -S 0.0.0.0:8080
6. Add environment variables from .env.example
7. Deploy
```

### Docker (Any Cloud Platform)

```bash
# Build
docker build -t projectcms:latest .

# Run locally
docker run -e DB_HOST=localhost -p 8000:80 projectcms:latest

# Push to registry
docker tag projectcms:latest myregistry/projectcms:latest
docker push myregistry/projectcms:latest
```

### Traditional Hosting (cPanel/Plesk)

1. Upload files via FTP to `public_html`
2. Create MySQL database in hosting control panel
3. Create .env file with database credentials
4. Visit your domain
5. If error, check error log in hosting control panel

---

## Troubleshooting

**"Database Connection Failed"**
- Check .env has correct DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
- MySQL service running? Try: `mysql -u user -p`
- Firewall blocking connection? Check port 3306

**"404 Page Not Found"**
- Apache: Check mod_rewrite enabled: `a2enmod rewrite`
- Check .htaccess file exists
- Check web server document root points to project directory

**"403 Permission Denied on Upload"**
- Check `uploads/` directory exists
- Check chmod 755 on `uploads/`
- Check web server user can write to directory

**"Email Not Sending"**
- Configure MAIL_HOST in .env if using external service
- Check PHP mail settings in php.ini
- May need SMTP addon

---

## What's Next?

After successful deployment:

1. **Monitor logs** - Check error_log daily for first week
2. **Backup database** - Set up automated daily backups
3. **Update security** - Review SECURITY.md for hardening
4. **Enable monitoring** - Set up uptime monitoring
5. **Plan maintenance** - Schedule regular security updates

---

## Need Help?

- **General Issues**: See DEPLOYMENT.md
- **Security Questions**: See DEPLOYMENT.md Security section
- **Specific Platform**: Check platform documentation
- **Database Issues**: Run `php deployment-check.php`
- **Code Issues**: Run `php security-audit.php`

---

## Important Reminders

⚠️ **Critical Points:**
- Never commit `.env` file to Git
- Always use HTTPS in production
- Keep backups of database
- Monitor error logs regularly
- Update PHP and dependencies regularly
- Change default database password immediately

✓ **Best Practices:**
- Use environment variables for all configuration
- Enable error logging to file
- Set up automated backups
- Use SSH keys for authentication
- Implement rate limiting on login
- Use prepared statements for all queries
- Escape all output to HTML

Good luck with deployment! 🚀
