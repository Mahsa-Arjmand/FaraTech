# FaraTech - Digital Technology Studio

A modern, bilingual (Persian/English) web presence for FaraTech, a digital technology studio specializing in web development, custom software, AI solutions, and business automation.

## Features

### Frontend
- **Bilingual Support**: Full Persian (RTL) and English (LTR) support
- **Modern Design**: Clean, professional design with 3D animations
- **Responsive**: Mobile-first responsive design
- **SEO Optimized**: Meta tags, structured data, sitemap, robots.txt
- **Performance**: Optimized loading with caching strategies

### Backend
- **PHP-based API**: RESTful API for dynamic content
- **MySQL Database**: Secure database with PDO
- **Admin Panel**: Content management and application review
- **Job Application System**: File upload support for resumes/portfolios
- **Security**: Rate limiting, input sanitization, session security

### Key Pages
- **Homepage**: Company overview, services, statistics, team, process
- **Portfolio**: Project showcase with filtering
- **Project Detail**: Detailed project information with image gallery
- **Team**: Team members with expertise
- **Contact**: Contact form and information
- **Career**: Job application system with file uploads

## Tech Stack

### Frontend
- HTML5, CSS3, JavaScript (Vanilla)
- Three.js for 3D animations
- Custom i18n system for bilingual support
- CSS Grid and Flexbox for layout

### Backend
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PDO for database operations
- RESTful API architecture

### Security
- .htaccess security headers
- Session security with IP validation
- Rate limiting for API and login
- Input sanitization
- File upload validation

## Project Structure

```
faratech/
├── backend/
│   ├── admin/              # Admin panel
│   ├── api/                # API endpoints
│   ├── config/             # Configuration files
│   ├── includes/           # Shared functions
│   ├── logs/               # Application logs
│   └── uploads/            # User uploads
├── frontend/
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   ├── images/             # Images
│   ├── index.html          # Homepage
│   ├── contact.html        # Contact page
│   ├── portfolio.html      # Portfolio page
│   ├── team.html           # Team page
│   └── project-detail.html # Project details
├── scripts/               # Utility scripts
├── .env.example          # Environment template
├── .gitignore            # Git ignore rules
├── DEPLOYMENT.md         # Deployment guide
├── SECURITY.md           # Security documentation
├── SEO.md                # SEO documentation
└── README.md             # This file
```

## Quick Start

### Local Development

1. **Clone the repository**
   ```bash
   git clone https://github.com/Mahsa-Arjmand/FaraTech.git
   cd FaraTech
   ```

2. **Setup database**
   - Create a MySQL database named `FaraTech`
   - Import the database schema
   - Run `php backend/setup_database.php`

3. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your local settings
   ```

4. **Start servers**
   ```bash
   # Backend (port 8000)
   cd backend
   php -S localhost:8000

   # Frontend (port 3000)
   cd frontend
   php -S localhost:3000
   ```

5. **Access the site**
   - Frontend: http://localhost:3000
   - Backend: http://localhost:8000
   - Admin Panel: http://localhost:8000/admin/
   - Default login: admin / admin123

## Deployment

For production deployment, see [DEPLOYMENT.md](DEPLOYMENT.md).

Key deployment steps:
1. Upload files to server
2. Configure environment variables
3. Setup database
4. Configure web server (Apache/Nginx)
5. Enable SSL certificate
6. Set file permissions
7. Test all functionality

## Security

Security features and guidelines are documented in [SECURITY.md](SECURITY.md).

Key security features:
- HTTP security headers
- Session security with IP validation
- Rate limiting
- Input sanitization
- File upload validation
- SQL injection prevention

## SEO

SEO optimizations are documented in [SEO.md](SEO.md).

Key SEO features:
- Optimized meta tags
- Open Graph tags
- Structured data (Schema.org)
- Sitemap.xml
- Robots.txt
- Canonical URLs

## Admin Panel

Access the admin panel at `/admin/` with credentials:
- Username: admin
- Password: admin123 (change in production)

**Admin Features:**
- Manage projects
- Review job applications
- Update content
- View analytics

## API Endpoints

### Public APIs
- `GET /api/public_projects.php` - Get all projects
- `GET /api/public_project.php?id=X` - Get single project
- `GET /api/public_team_positions.php` - Get career positions
- `POST /api/job_application.php` - Submit job application
- `POST /api/contact.php` - Submit contact form

### Admin APIs
- `GET /api/projects.php` - Manage projects
- `GET /api/team_positions.php` - Manage positions
- `GET /api/content.php` - Manage content

## Configuration

### Environment Variables (.env)
```env
DB_HOST=localhost
DB_NAME=FaraTech
DB_USER=root
DB_PASS=
SITE_URL=https://faratech.ir
ADMIN_PASSWORD=change_this
APP_ENV=production
```

### Database Configuration
Edit `backend/config/database.php` to match your database settings.

## Development

### Adding New Pages
1. Create HTML file in `frontend/`
2. Add corresponding CSS in `frontend/css/`
3. Add translations in `frontend/js/i18n.js`
4. Update navigation in all pages

### Adding New API Endpoints
1. Create PHP file in `backend/api/`
2. Include database configuration
3. Implement security measures
4. Document the endpoint

### Customizing Content
- Update company info in `frontend/js/company-config.js`
- Update translations in `frontend/js/i18n.js`
- Update admin credentials in database

## Troubleshooting

### Common Issues

**Database connection failed**
- Check database credentials in config
- Verify MySQL is running
- Check database exists

**File upload not working**
- Check upload directory permissions (775)
- Verify PHP upload settings
- Check disk space

**Admin panel not accessible**
- Check session settings
- Verify auth.php configuration
- Clear browser cookies

**Images not loading**
- Check file paths
- Verify upload directory exists
- Check file permissions

## Support

For issues or questions:
- Check [DEPLOYMENT.md](DEPLOYMENT.md) for deployment issues
- Check [SECURITY.md](SECURITY.md) for security concerns
- Check [SEO.md](SEO.md) for SEO questions
- Review error logs in `backend/logs/`

## License

This project is proprietary software of FaraTech.

## Credits

Developed by FaraTech Team
- Web Development
- UI/UX Design
- Backend Development
- AI Solutions

## Version History

### Version 1.0 (2026-10-25)
- Initial release
- Bilingual support (Persian/English)
- Job application system
- Admin panel
- Security features
- SEO optimization
- Documentation

---

**FaraTech** - Your Ideas. Our Technology. Infinite Possibilities.
