# EdiBear.com

A kids’ learning and education website built with PHP. Edi the bear (“Little Buddies”) helps children access coloring pages, activity books, study packs, and school homework materials in a fun way.

## Features

- **Coloring pages** — Downloadable coloring pages
- **Books & papers** — Kids’ workbooks and model papers
- **Study packs** — Homework-related materials for school
- **Blogs / fun activities** — Articles and activity ideas
- **Testimonials** — User reviews and ratings
- **Admin area** — Manage content (books, blogs, homework, ads, testimonials, users)
- **Multi-language** — Sinhala, Tamil, and English
- **Grades** — LKG, UKG, Grade 1–5

## Tech stack

- **Backend:** PHP 5.6+ (PDO)
- **Database:** MySQL
- **Frontend:** HTML, CSS (Bootstrap), JavaScript
- **Admin UI:** Argon Dashboard (Bootstrap-based)
- **Email:** PHPMailer (in `src/`)

## Requirements

- PHP 5.6 or later (with PDO MySQL)
- MySQL 5.x or later
- Apache with `mod_rewrite` (for clean URLs and HTTPS redirect)

## Setup

1. **Clone or copy** the project into your web root or a virtual host document root.

2. **Database**
   - Create a MySQL database and user.
   - Import your schema/tables if you have a SQL dump.

3. **Configuration**
   - Edit `classes/dbconfig.php` and set your database credentials:
     - `$host` — database host (e.g. `localhost`)
     - `$db_name` — database name
     - `$username` — database user
     - `$password` — database password

4. **Web server**
   - Ensure the document root points to the project folder.
   - Enable `.htaccess` (AllowOverride) so rewrite rules and PHP settings in `.htaccess` apply.

5. **Optional**
   - For production, use HTTPS and keep `display_errors` off (as in the existing `.htaccess` PHP block).

## Project structure

```
edibear.com/
├── index.php          # Home page
├── about.php
├── blogs.php          # Fun activities / blog listing
├── blog.php           # Single blog post
├── books.php          # Books & papers
├── homework.php       # Study packs / homework
├── pdf.php            # PDF resources
├── testimonials.php
├── login.php / logout.php
├── account.php
├── search.php
├── classes/
│   ├── dbconfig.php   # Database connection (edit for your DB)
│   ├── class.user.php
│   ├── class.header.php
│   ├── class.widgets.php
│   └── session.php
├── admin-area/        # Admin panel (dashboard, CRUD for content)
├── img/               # Images (books, homework, blogs, etc.)
├── css/, scss/        # Styles
├── src/               # PHPMailer and related
└── .htaccess          # Rewrite rules, HTTPS redirect, PHP config
```

## License

Proprietary — all rights reserved.
