# Deployment Readiness Checklist

## ProjectCMS - Pre-Deployment Verification

Use this checklist before deploying to production. Check off each item and ensure all critical items (⚠️) are completed.

---

## Environment & Infrastructure

### PHP Configuration
- [ ] ⚠️ **PHP Version 8.2+** - Current version meets minimum requirement
  - Test: `php -v`
- [ ] ⚠️ **Required Extensions Installed**
  - PDO (PHP Data Objects)
  - PDO_MySQL or MySQLi
  - JSON
  - Hash
  - Filter
  - Session
  - GD Library (for image processing)
- [ ] **Optional Extensions**
  - [ ] OpenSSL (for encrypted connections)
  - [ ] Curl (for external API calls)
- [ ] ⚠️ **upload_max_filesize >= 5MB** - For file uploads
- [ ] ⚠️ **post_max_size >= 5MB** - For form submissions
- [ ] **memory_limit >= 128MB** - For large operations
- [ ] **max_execution_time >= 30 seconds** - For batch operations

### Database Configuration
- [ ] ⚠️ **MySQL/MariaDB 10.4+** installed and running
- [ ] ⚠️ **Database created** with correct name
- [ ] ⚠️ **Database user created** with appropriate permissions
- [ ] ⚠️ **SQL schema imported** - `sql/serverside.sql`
- [ ] **Database backups automated** - Daily backups scheduled
- [ ] **Database charset set to utf8mb4** - For Unicode support
- [ ] **Database indexes created** - For query performance

---

## Code Configuration

### Environment Variables
- [ ] ⚠️ **.env file created** from `.env.example`
- [ ] ⚠️ **DB_HOST set correctly** (not localhost, use IP)
- [ ] ⚠️ **DB_NAME, DB_USER, DB_PASSWORD set**
- [ ] ⚠️ **APP_ENV set to "production"**
- [ ] ⚠️ **APP_DEBUG set to "false"**
- [ ] **SECURE cookies enabled** - `SESSION_SECURE=true`
- [ ] **All sensitive credentials** in .env, NOT in code

### File Configuration
- [ ] ⚠️ **.gitignore contains .env** - Prevents credential leaks
- [ ] ✅ **.htaccess configured** - Blocks access to sensitive files
- [ ] ✅ **Production config loaded** - Error handling properly set
- [ ] ⚠️ **composer.json present** - Dependency tracking
- [ ] **Deployment guide present** - For team reference

---

## Security Checks

### Access Control
- [ ] ⚠️ **Sensitive files blocked from web** - .env, .git, sql/, includes/
  - Test via: http://yourdomain.com/.env (should get 403/404)
- [ ] ⚠️ **No directory listing** - Directories should NOT list files
- [ ] ⚠️ **Authentication required** - Admin features protected
- [ ] **Two-factor authentication** - Recommended for admin

### Input Validation
- [ ] ⚠️ **All form inputs validated** - Type, length, format
- [ ] ⚠️ **SQL injection prevented** - Prepared statements used
- [ ] **Output escaping applied** - HTML entities escaped
- [ ] **File upload validation** - Type whitelist enforced
- [ ] **CSRF tokens** - On all state-changing forms
- [ ] **Rate limiting** - On login/registration

### HTTPS & Security Headers
- [ ] ⚠️ **SSL/TLS certificate installed** - HTTPS enabled
- [ ] ⚠️ **HTTP redirects to HTTPS** - All traffic encrypted
- [ ] **Content-Security-Policy header** - XSS protection
- [ ] **X-Frame-Options header** - Clickjacking prevention
- [ ] **X-Content-Type-Options: nosniff** - MIME type sniffing prevention
- [ ] **Referrer-Policy set** - Control referrer information

### Database Security
- [ ] ⚠️ **Database user has minimal permissions** - Only needed tables/operations
- [ ] ⚠️ **SQL backups encrypted** - Backups stored securely
- [ ] **Database connection encrypted** - Use SSL for connections
- [ ] **No test/sample data** - Production data only

### File System Security
- [ ] ⚠️ **Directory permissions: 755** - Readable by web server
- [ ] ⚠️ **File permissions: 644** - Readable by web server, not executable
- [ ] ⚠️ **Logs directory writable** - Web server can write logs
- [ ] ⚠️ **Uploads directory writable** - Web server can write uploads
- [ ] **Uploads directory NOT executable** - .htaccess or nginx config blocks PHP execution
- [ ] **Log files outside web root** - Not accessible via web

---

## Code Quality

### Testing & Validation
- [ ] ✅ **PHP syntax validation passed** - `php -l` on all files
- [ ] **Unit tests written** - If applicable
- [ ] **Integration tests passed** - Database integration works
- [ ] ✅ **Security audit completed** - Run `php security-audit.php`
- [ ] ✅ **Deployment check passed** - Run `php deployment-check.php`

### Code Review
- [ ] **All hardcoded credentials removed** - Use environment variables
- [ ] **Sensitive debug output removed** - No var_dump() or print_r()
- [ ] **Error messages generic** - Don't expose system details
- [ ] **Comments updated** - Documentation current
- [ ] **No TODOs/FIXMEs** - In production code

### Dependencies
- [ ] ✅ **composer.json present** - Dependencies documented
- [ ] **All required packages listed** - With versions
- [ ] **No unnecessary dependencies** - Clean installation
- [ ] **Vendor directory excluded from git** - .gitignore configured

---

## Deployment Preparation

### Repository Management
- [ ] ⚠️ **Code committed to Git** - All changes tracked
- [ ] ⚠️ **.env NOT committed** - Only .env.example in repo
- [ ] ⚠️ **Sensitive files NOT committed** - Checked via git ls-files
- [ ] **Deploy keys configured** - For deployment automation
- [ ] **Backup before deploy** - Latest backup created

### Deployment Process
- [ ] **Deployment script tested** - Steps documented in DEPLOYMENT.md
- [ ] **Rollback procedure documented** - In case of issues
- [ ] **Migration plan prepared** - If migrating from old server
- [ ] **Team notified** - Stakeholders aware of deployment
- [ ] **Maintenance window scheduled** - If downtime required
- [ ] **Monitoring configured** - Error tracking set up

### Platform-Specific (if using cloud platforms)
- [ ] **Platform environment vars set** - Via dashboard or CLI
- [ ] **Build steps configured** - If using CI/CD
- [ ] **Health checks configured** - Automated monitoring
- [ ] **Auto-scaling configured** - If applicable
- [ ] **Database backups enabled** - Platform-level backups
- [ ] **SSH keys configured** - For remote access

---

## Testing Pre-Deployment

### Functional Testing
- [ ] **Homepage loads** - No errors
- [ ] **User registration works** - Can create new account
- [ ] **User login works** - Can authenticate
- [ ] **Dashboard loads** - For authenticated users
- [ ] **Equipment listing works** - Shows all equipment
- [ ] **Equipment detail page works** - Shows single item details
- [ ] **Equipment creation works** - Admin can add items
- [ ] **Equipment editing works** - Admin can modify items
- [ ] **Equipment deletion works** - Admin can remove items
- [ ] **Category management works** - Create/edit/delete categories
- [ ] **File uploads work** - Can upload equipment images
- [ ] **Comments/reviews work** - Can add and view reviews
- [ ] **Moderation works** - Admin can approve/reject comments
- [ ] **Logout works** - Session properly terminated

### Performance Testing
- [ ] **Page load times acceptable** - < 2 seconds typical
- [ ] **Database queries optimized** - Check slow query log
- [ ] **Images compressed** - Reasonable file sizes
- [ ] **CSS/JS minified** - Reduced file sizes
- [ ] **Caching configured** - Browser and server caching

### Security Testing
- [ ] ⚠️ **SQL injection test** - Try: `' OR '1'='1`
- [ ] ⚠️ **XSS test** - Try injecting: `<script>alert('XSS')</script>`
- [ ] ⚠️ **CSRF test** - Verify tokens present on forms
- [ ] **Password strength** - Requirements enforced
- [ ] **Session timeout** - Inactive sessions expire
- [ ] **Admin access protected** - Admin pages require authentication
- [ ] **URL traversal test** - Can't access parent directories
- [ ] **File upload restrictions** - Only allowed types accepted
- [ ] **Error messages generic** - No sensitive information disclosed

---

## Post-Deployment Verification

### Immediate Checks (First Hour)
- [ ] ⚠️ **Site is accessible** - Domain resolves correctly
- [ ] ⚠️ **HTTPS works** - Secure connection established
- [ ] ⚠️ **Error logs checked** - No critical errors
- [ ] **Database connection works** - Application can query database
- [ ] **File uploads working** - Can upload new equipment images
- [ ] **Email notifications working** - If applicable
- [ ] **Admin functionality works** - Dashboard accessible

### Ongoing Monitoring (First Week)
- [ ] **Error logs monitored daily** - Check for issues
- [ ] **Performance monitored** - Check response times
- [ ] **Security monitored** - Check for suspicious activity
- [ ] **Database backups verified** - Backups completing successfully
- [ ] **User feedback collected** - Any issues reported
- [ ] **Database growth monitored** - Storage usage normal

### Scheduled Maintenance
- [ ] **Weekly log reviews** - Check error patterns
- [ ] **Monthly security updates** - Apply patches
- [ ] **Quarterly backups tested** - Verify restoration works
- [ ] **Annual security audit** - Full security review

---

## Deployment Platforms - Special Considerations

### If deploying to Railway.app:
- [ ] Railway environment variables configured
- [ ] MySQL plugin added and configured
- [ ] Build command set to: `composer install`
- [ ] Start command set appropriately
- [ ] Database migrations run post-build
- [ ] Health checks configured

### If deploying to Render.com:
- [ ] Render environment variables configured
- [ ] Database service added (PostgreSQL or MySQL)
- [ ] Build command configured
- [ ] Start command configured
- [ ] Auto-deploy from GitHub enabled
- [ ] Regions selected for redundancy

### If deploying to Docker:
- [ ] Dockerfile created and tested
- [ ] docker-compose.yml configured
- [ ] All required extensions in base image
- [ ] Volume mounts configured for uploads/logs
- [ ] Environment variables passed to container
- [ ] Network defined for multi-container setup

### If deploying to Traditional Hosting:
- [ ] Hosting provider supports PHP 8.2+
- [ ] SSH/SFTP access configured
- [ ] Database provider configured
- [ ] FTP upload completed successfully
- [ ] Database restored on hosting server
- [ ] .htaccess rules configured
- [ ] File permissions set correctly
- [ ] SSL certificate installed

---

## Sign-Off

- [ ] **Developer**: Code reviewed and tested - Date: ___________
- [ ] **QA**: Testing completed - Date: ___________
- [ ] **DevOps/Admin**: Infrastructure ready - Date: ___________
- [ ] **Manager**: Approval to deploy - Date: ___________

**Deployment Date/Time**: ___________

**Deployer Name**: ___________

**Notes/Comments**:
```
_________________________________________________
_________________________________________________
_________________________________________________
```

---

## Emergency Contacts

- **Primary Contact**: ___________
- **Backup Contact**: ___________
- **Hosting Support**: ___________
- **Database Admin**: ___________

---

## Post-Deployment Rollback Procedure

If critical issues occur:

1. **Notify team** - Alert stakeholders
2. **Take backup** - Capture current state
3. **Restore from backup** - Revert database and files
4. **Verify restoration** - Test application functionality
5. **Document incident** - For post-mortem review
6. **Schedule fixes** - Plan corrective measures

**Backup Location**: ___________

**Restore Procedure**: See DEPLOYMENT.md
