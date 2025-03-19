# Core Learners - E-Learning Social Media Platform

A comprehensive E-Learning social media platform built with PHP where users can learn, share, and interact with educational content.

## Features

- User Authentication System
- Social Media Features
  - Friends System
  - Notifications
  - Profile Management
- Learning Resources
  - Courses
  - Notes
  - Videos
  - PDF Files
- Theme Customization
  - Dark Mode
  - Light Mode
  - System Default

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)
- Modern Web Browser

## Installation

1. Clone the repository
2. Import the database schema from `database/schema.sql`
3. Configure database connection in `config/database.php`
4. Set up your web server to point to the project directory
5. Access the website through your web browser

## Project Structure

```
Core-Learners/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── pages/
│   ├── home.php
│   ├── profile.php
│   ├── courses.php
│   └── settings.php
└── index.php
```

## License

MIT License