# FaraTech Admin Panel

A complete admin panel for managing the FaraTech website with PHP backend and MySQL database.

## Features

- **Authentication System**: Secure login with role-based access (Admin/Editor)
- **Project Management**: CRUD operations for portfolio projects
- **Content Management**: Dynamic content editing for all website pages
- **Comments Management**: Review and manage user comments with approval workflow
- **Team Positions Management**: Manage team positions and roles
- **Job Applications**: Receive and manage job applications with status tracking
- **Page Views Analytics**: Track and analyze page views statistics (Admin only)
- **Database Viewer**: View and browse database tables (Admin only)
- **User Management**: Manage admin users (Admin only)
- **Frontend Sync**: JavaScript library for real-time content synchronization
- **Public API**: RESTful API endpoints for frontend integration

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Setup

1. **Database Setup**

   The database is automatically created when you run the setup script:

   ```bash
   cd backend
   php setup_database.php
   ```

   This will:
   - Create database `FaraTech`
   - Create tables: `users`, `projects`, `content`
   - Create default admin user (username: `admin`, password: `admin123`)

2. **Configuration**

   Database configuration is in `config/database.php`:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'FaraTech');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

   Modify these values if your MySQL setup is different.

3. **Start the Server**

   Using PHP built-in server:
   ```bash
   php -S localhost:8000
   ```

   Or configure your web server to point to the `backend` directory.

## Access

- **Admin Panel**: http://localhost:8000/admin/
- **Default Login**: `admin` / `admin123`

## User Roles

### Admin
- Full access to all features
- Can manage users
- Can view database tables
- Can manage projects and content

### Editor
- Can manage projects
- Can manage content
- Cannot manage users
- Cannot view database tables

## Pages

### Dashboard
- Overview statistics
- Recent projects
- Quick actions

### Projects
- Create, edit, delete projects
- Search and filter projects
- Manage project status (draft/published/archived)
- Mark projects as featured

### Content
- Manage website content by page and section
- Edit content for all pages (Home, About, Services, etc.)
- Search and filter content

### Comments
- Review and manage user comments
- Approve or reject comments
- Filter by status, page, or search
- Delete inappropriate comments

### Team Positions
- Create and manage team positions
- Set display order for positions
- Edit position descriptions
- Delete unused positions

### Job Applications
- View and manage job applications
- Filter by status, position, or search
- Update application status (pending, reviewed, interview, hired, rejected)
- Add notes to applications
- View applicant details and links

### Page Views (Admin Only)
- View page view statistics
- Filter by date range and page
- See views by page breakdown
- Daily view analytics
- Total views over time

### Database (Admin Only)
- View all database tables
- Browse table structure
- View table data with pagination

### Users (Admin Only)
- Create, edit, delete users
- Manage user roles
- Search users

## API Endpoints

### Public API (No authentication required)

#### Get Published Projects
```
GET /api/public_projects.php
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Project Name",
      "description": "...",
      "client": "...",
      "category": "...",
      "technologies": ["PHP", "MySQL"],
      "image_url": "...",
      "project_url": "...",
      "featured": true,
      "status": "published",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### Get Page Content
```
GET /api/public_content.php?page=index
```

Response:
```json
{
  "success": true,
  "data": {
    "hero": {
      "title": "Welcome to FaraTech",
      "subtitle": "..."
    },
    "about": {
      "description": "..."
    }
  }
}
```

#### Track Page View
```
GET /api/page_views.php?action=track&page=index
```

Response:
```json
{
  "success": true,
  "message": "Page view tracked successfully"
}
```

#### Get Approved Comments
```
GET /api/comments.php?action=public_list&page=index
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "author_name": "John Doe",
      "author_email": "john@example.com",
      "comment_text": "Great website!",
      "status": "approved",
      "created_at": "2024-01-01 00:00:00"
    }
  ]
}
```

#### Submit Comment
```
POST /api/comments.php?action=public_create
```

Request body:
```json
{
  "page": "index",
  "author_name": "John Doe",
  "author_email": "john@example.com",
  "comment_text": "Great website!"
}
```

Response:
```json
{
  "success": true,
  "message": "Comment submitted successfully and awaiting approval"
}
```

#### Get Team Positions
```
GET /api/team_positions.php?action=public_list
```

Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Senior Frontend Developer",
      "description": "Lead frontend development",
      "display_order": 1
    }
  ]
}
```

#### Submit Job Application
```
POST /api/job_applications.php?action=public_create
```

Request body:
```json
{
  "position_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+98 912 345 6789",
  "cover_letter": "I am interested in this position...",
  "resume_url": "https://example.com/resume.pdf",
  "portfolio_url": "https://example.com/portfolio",
  "linkedin_url": "https://linkedin.com/in/johndoe"
}
```

Response:
```json
{
  "success": true,
  "message": "Job application submitted successfully"
}
```

### Admin API (Requires authentication)

#### Projects API
```
GET /api/projects.php?action=list&page=1&per_page=10&search=&status=
GET /api/projects.php?action=get&id=1
POST /api/projects.php?action=create
POST /api/projects.php?action=update
DELETE /api/projects.php?action=delete&id=1
```

#### Users API (Admin only)
```
GET /api/users.php?action=list&page=1&per_page=10&search=
GET /api/users.php?action=get&id=1
POST /api/users.php?action=create
POST /api/users.php?action=update
DELETE /api/users.php?action=delete&id=1
```

#### Content API
```
GET /api/content.php?action=list&page=1&per_page=20&search=&page_filter=
GET /api/content.php?action=get&id=1
POST /api/content.php?action=create
POST /api/content.php?action=update
DELETE /api/content.php?action=delete&id=1
GET /api/content.php?action=get_page_content&page=index
```

#### Comments API
```
GET /api/comments.php?action=list&page=1&per_page=20&search=&status=&page_filter=
GET /api/comments.php?action=get&id=1
GET /api/comments.php?action=approve&id=1
GET /api/comments.php?action=reject&id=1
DELETE /api/comments.php?action=delete&id=1
```

#### Team Positions API
```
GET /api/team_positions.php?action=list&page=1&per_page=20&search=
GET /api/team_positions.php?action=get&id=1
POST /api/team_positions.php?action=create
POST /api/team_positions.php?action=update
DELETE /api/team_positions.php?action=delete&id=1
```

#### Job Applications API
```
GET /api/job_applications.php?action=list&page=1&per_page=20&search=&status=&position_id=
GET /api/job_applications.php?action=get&id=1
GET /api/job_applications.php?action=update_status&id=1&status=hired&notes=
DELETE /api/job_applications.php?action=delete&id=1
```

#### Page Views API (Admin only)
```
GET /api/page_views.php?action=list&page=1&per_page=20&page_filter=&start_date=&end_date=
GET /api/page_views.php?action=stats&days=30
```

#### Database API (Admin only)
```
GET /api/database.php?action=tables
GET /api/database.php?action=table_data&table=projects&page=1&per_page=20
GET /api/database.php?action=table_info&table=projects
```

## Database Schema

### users
- `id` (INT, Primary Key)
- `username` (VARCHAR, Unique)
- `email` (VARCHAR, Unique)
- `password` (VARCHAR, Hashed)
- `role` (ENUM: admin, editor)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### projects
- `id` (INT, Primary Key)
- `title` (VARCHAR)
- `description` (TEXT)
- `client` (VARCHAR)
- `category` (VARCHAR)
- `technologies` (TEXT, comma-separated)
- `image_url` (VARCHAR)
- `project_url` (VARCHAR)
- `featured` (BOOLEAN)
- `status` (ENUM: draft, published, archived)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### content
- `id` (INT, Primary Key)
- `page` (VARCHAR)
- `section` (VARCHAR)
- `content_key` (VARCHAR)
- `content_value` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- Unique key: (page, section, content_key)

### page_views
- `id` (INT, Primary Key)
- `page` (VARCHAR)
- `view_date` (DATE)
- `views_count` (INT)
- `created_at` (TIMESTAMP)
- Unique key: (page, view_date)

### comments
- `id` (INT, Primary Key)
- `page` (VARCHAR)
- `author_name` (VARCHAR)
- `author_email` (VARCHAR)
- `comment_text` (TEXT)
- `status` (ENUM: pending, approved, rejected)
- `ip_address` (VARCHAR)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### team_positions
- `id` (INT, Primary Key)
- `name` (VARCHAR)
- `description` (TEXT)
- `display_order` (INT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### job_applications
- `id` (INT, Primary Key)
- `position_id` (INT, Foreign Key to team_positions)
- `full_name` (VARCHAR)
- `email` (VARCHAR)
- `phone` (VARCHAR)
- `cover_letter` (TEXT)
- `resume_url` (VARCHAR)
- `portfolio_url` (VARCHAR)
- `linkedin_url` (VARCHAR)
- `status` (ENUM: pending, reviewed, interview, hired, rejected)
- `notes` (TEXT)
- `ip_address` (VARCHAR)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## Security

- Passwords are hashed using PHP's `password_hash()` function
- Session-based authentication
- Role-based access control
- SQL injection prevention using prepared statements
- XSS prevention using input sanitization

## Integration with Frontend

To integrate the admin panel with your existing frontend:

1. **Load Projects from API**

   In your portfolio page, fetch projects from the API:

   ```javascript
   fetch('/backend/api/public_projects.php')
     .then(response => response.json())
     .then(data => {
       if (data.success) {
         renderProjects(data.data);
       }
     });
   ```

2. **Load Content from API**

   In your pages, fetch content dynamically:

   ```javascript
   fetch('/backend/api/public_content.php?page=about')
     .then(response => response.json())
     .then(data => {
       if (data.success) {
         updatePageContent(data.data);
       }
     });
   ```

3. **Track Page Views**

   Add this to every page to track views:

   ```javascript
   // Track page view when page loads
   const pageName = window.location.pathname.split('/').pop().replace('.html', '') || 'index';
   fetch(`/backend/api/page_views.php?action=track&page=${pageName}`);
   ```

4. **Load Comments**

   In your pages, fetch approved comments:

   ```javascript
   fetch('/backend/api/comments.php?action=public_list&page=index')
     .then(response => response.json())
     .then(data => {
       if (data.success) {
         renderComments(data.data);
       }
     });
   ```

5. **Submit Comment**

   Add a comment form with this handler:

   ```javascript
   function submitComment(page, name, email, comment) {
     fetch('/backend/api/comments.php?action=public_create', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({
         page: page,
         author_name: name,
         author_email: email,
         comment_text: comment
       })
     })
     .then(response => response.json())
     .then(data => {
       if (data.success) {
         alert('Comment submitted and awaiting approval');
       }
     });
   }
   ```

6. **Load Team Positions**

   Fetch team positions for team page:

   ```javascript
   fetch('/backend/api/team_positions.php?action=public_list')
     .then(response => response.json())
     .then(data => {
       if (data.success) {
         renderTeamPositions(data.data);
       }
     });
   ```

7. **Using the Content Sync Library**

   The `js/content-sync.js` library provides helper functions for easy integration:

   ```html
   <script src="js/content-sync.js"></script>
   ```

   Available functions:
   - `FaraTechSync.fetchPageContent(page)` - Fetch content for a page
   - `FaraTechSync.updatePageContent(page)` - Update DOM with content
   - `FaraTechSync.fetchProjects()` - Fetch published projects
   - `FaraTechSync.renderProjects(selector, renderFn)` - Render projects
   - `FaraTechSync.fetchTeamPositions()` - Fetch team positions
   - `FaraTechSync.renderTeamPositions(selector, renderFn)` - Render positions
   - `FaraTechSync.fetchComments(page)` - Fetch comments for a page
   - `FaraTechSync.renderComments(page, selector, renderFn)` - Render comments
   - `FaraTechSync.submitComment(data)` - Submit a comment
   - `FaraTechSync.submitJobApplication(data)` - Submit job application
   - `FaraTechSync.trackPageView(page)` - Track page view

   Example usage:
   ```javascript
   // Auto-sync content on page load
   window.FaraTechSync.initContentSync();

   // Manual content update
   const content = await window.FaraTechSync.fetchPageContent('about');
   console.log(content);

   // Render projects
   window.FaraTechSync.renderProjects('#projects-container', (project) => {
     const div = document.createElement('div');
     div.innerHTML = `<h3>${project.title}</h3>`;
     return div;
   });
   ```

8. **Admin Link**

   Add a link to the admin panel in your footer or navigation:

   ```html
   <a href="/backend/admin/login.php">Admin Login</a>
   ```

## Customization

### Using Content Sync Library

The `js/content-sync.js` library automatically syncs content from the database to your frontend. To use it:

1. Include the script in your HTML pages:
   ```html
   <script src="js/content-sync.js"></script>
   ```

2. Add `data-content` attributes to elements you want to sync:
   ```html
   <h1 data-content="title" data-section="hero">Default Title</h1>
   <p data-content="subtitle" data-section="hero">Default subtitle</p>
   ```

3. The library will automatically:
   - Track page views
   - Update content from the database
   - Cache content for performance

### Adding New Pages

1. Add the page to the content management page selector
2. Create content items for that page
3. Fetch content in your frontend using the public API

### Adding New Database Tables

1. Create migration in `setup_database.php`
2. Add table-specific API endpoints in `api/` directory
3. Add admin interface pages in `admin/` directory

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify database credentials in `config/database.php`
- Ensure database `FaraTech` exists

### Login Not Working
- Reset admin password by running `setup_database.php` again
- Check `users` table in database
- Clear browser cookies/sessions

### API Not Working
- Check PHP error logs
- Verify file permissions
- Ensure proper HTTP methods (GET/POST/DELETE)

## License

This admin panel is part of the FaraTech project.
