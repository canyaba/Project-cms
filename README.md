# ProjectCMS - Equipment Management System

A robust, full-stack Content Management System built with PHP and MySQL for managing equipment catalogs, user reviews, and administrative operations.

## Overview

ProjectCMS is a production-grade web application designed to streamline equipment inventory management with a comprehensive admin interface and public-facing customer review system. The platform provides secure user authentication, advanced inventory management capabilities, and moderation tools for community-driven content.

## Key Features

### 🔐 Security & Authentication
- Secure user registration and login system with password hashing
- Session-based authentication with secure cookie parameters
- CAPTCHA protection against automated attacks
- SQL injection prevention through PDO prepared statements
- Input validation and HTML sanitization

### 📦 Equipment Management
- **Add/Create**: Insert new equipment with pricing, descriptions, and imagery
- **Read/View**: Browse and view equipment details with customer reviews
- **Update/Edit**: Modify existing equipment attributes and metadata
- **Delete**: Remove equipment safely from the system
- **Sort & Filter**: Organize equipment by name, price, creation date, and categories
- **Image Upload**: Support for equipment product photography

### 🏷️ Category Management
- Create and organize equipment into logical categories
- Category-based filtering and browsing
- Flexible categorization system for product organization

### 💬 Review & Comment System
- Customer rating system (1-5 star scale)
- Detailed product reviews from site visitors
- Comment moderation dashboard for quality control
- Guest and registered user reviews

### 👨‍💼 Admin Dashboard
- Central hub for all administrative functions
- Quick-access cards for common management tasks
- User session tracking (welcomes admin by username)
- Intuitive UI with Material Design Bootstrap styling

## Technology Stack

- **Backend**: PHP 8.2+
- **Database**: MySQL 10.4+ / MariaDB
- **Database Interface**: PDO (PHP Data Objects)
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Material Design Bootstrap (MDB)
- **Version Control**: Git

## Project Structure

```
ProjectCMS/
├── index.php                          # Public-facing homepage with equipment viewing
├── dashboard.php                      # Admin control center
├── login.php                          # User authentication
├── register.php                       # New user registration
├── logout.php                         # Session termination
│
├── Equipment Management
├── equipment.php                      # View/manage equipment list
├── insert_equipment.php               # Add new equipment
├── edit_equipment.php                 # Modify existing equipment
├── delete_equipment.php               # Remove equipment
├── view_equipment.php                 # Public equipment detail page
│
├── Category Management
├── categories.php                     # Manage categories
├── delete_category.php                # Remove categories
├── migrate_equipment_categories.php   # Database migration utilities
│
├── Content Moderation
├── comments_moderation.php            # Review and approve comments
│
├── Security & Utilities
├── captcha.php                        # CAPTCHA generation and validation
│
├── Configuration & Database
├── includes/
│   ├── auth.php                       # Authentication functions
│   ├── connect.php                    # MySQL database connection
│   ├── db_public.php                  # Public database connection
│   ├── header.php                     # HTML header template
│   ├── footer.php                     # HTML footer template
│   ├── function.php                   # Utility functions
│   └── image_upload.php               # Image handling
│
├── Database
├── sql/
│   └── serverside.sql                 # Database schema and seed data
│
├── Assets
├── css/                               # Stylesheets (MDB & custom themes)
├── js/                                # JavaScript libraries
├── scss/                              # SCSS source files
└── uploads/                           # User-uploaded images
```

## Installation & Setup

### Prerequisites
- Apache/PHP web server (XAMPP recommended)
- PHP 8.2 or higher
- MySQL 10.4+ or MariaDB
- Git (optional, for version control)

### Database Setup

1. **Create the Database**:
   ```sql
   CREATE DATABASE serverside;
   ```

2. **Import Data**:
   ```bash
   mysql -u serveruser -p serverside < sql/serverside.sql
   ```

3. **Database Credentials** (configured in `includes/connect.php`):
   - Host: `127.0.0.1:3306`
   - Database: `serverside`
   - User: `serveruser`
   - Password: `gorgonzola7!`

### Web Server Configuration

1. **Clone/Copy Project**:
   ```bash
   cp -r ProjectCMS c:/xampp/htdocs/WD2/
   ```

2. **Set File Permissions**:
   ```bash
   chmod -R 755 uploads/
   ```

3. **Start Services**:
   - Start Apache and MySQL via XAMPP Control Panel
   - Access the application: `http://localhost/WD2/ProjectCms/`

### Initial Login

1. Navigate to the login page
2. Register a new administrator account
3. Access the admin dashboard after successful authentication

## Usage Guide

### For Administrators

**Dashboard**: Central access point for all management functions

**Equipment Management**:
- Click "Manage Equipment" to view/edit/delete existing items
- Click "Add Equipment" to create new products
- Use sorting options to organize by name, price, or date

**Category Management**:
- Navigate to "Manage Categories" to organize products
- Create new categories for product classification

**Comment Moderation**:
- Access "Moderate Comments" to review user submissions
- Approve or reject customer reviews and ratings

### For End Users

1. **Browse Equipment**: View available products on public pages
2. **View Details**: Click equipment to see full descriptions, pricing, and reviews
3. **Submit Reviews**: Leave ratings and comments on equipment
4. **Register**: Create an account for personalized features (optional for reviews)

## Code Quality & Security Practices

✅ **Security Features**:
- PDO prepared statements prevent SQL injection
- Password hashing with PHP's `password_hash()`
- Input validation and sanitization
- Secure session configuration with `httponly` and `samesite` flags
- HTTPS support detection

✅ **Code Standards**:
- PHP 8.2+ compatibility
- Consistent error handling and reporting
- Well-documented file structure
- Separation of concerns (authentication, database, UI)

## API Endpoints & Form Handlers

- `POST /login.php` - User authentication
- `POST /register.php` - User registration
- `GET/POST /equipment.php` - Equipment listing and sorting
- `POST /insert_equipment.php` - Add new equipment
- `POST /edit_equipment.php` - Update equipment
- `POST /delete_equipment.php` - Delete equipment
- `POST /categories.php` - Manage categories
- `POST /comments_moderation.php` - Moderate reviews
- `GET /logout.php` - Terminate user session

## Database Schema

### Core Tables
- **users**: Admin and authenticated user accounts
- **equipment**: Product/equipment inventory
- **categories**: Product categorization
- **reviews**: Customer ratings and reviews

All tables leverage primary keys, foreign key constraints, and appropriate indexing for optimal query performance.

## Performance Considerations

- Database queries use parameterized statements for efficiency
- Sorting and filtering implemented at database level
- Image uploads stored in dedicated directory outside web root (best practice)
- Session management optimized with proper cookie configuration

## Future Enhancements

- [ ] RESTful API layer for third-party integrations
- [ ] Advanced search and filtering engine
- [ ] Email notifications for reviews
- [ ] User dashboard for review history
- [ ] Analytics and reporting dashboard
- [ ] Multi-language support
- [ ] Advanced image optimization and CDN integration
- [ ] Role-based access control (RBAC) for different admin levels

## Troubleshooting

**Database Connection Error**:
- Verify MySQL server is running
- Check credentials in `includes/connect.php`
- Ensure database `serverside` exists

**File Upload Issues**:
- Verify `uploads/` directory has write permissions
- Check PHP `upload_max_filesize` and `post_max_size` settings

**Session Issues**:
- Clear browser cookies
- Verify PHP session directory has write permissions
- Check `session.secure` and `session.samesite` settings in php.ini

## License & Ownership

Internal project - all rights reserved.

## Author & Contributors

Developed as part of the Web Development 2 curriculum. Built to production standards with enterprise-level security practices and scalability considerations.

---

**Last Updated**: April 2026
**Version**: 1.0.0
**Status**: Production Ready
