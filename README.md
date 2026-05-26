# DevHub

DevHub is a modern blogging and developer community platform built with Laravel. Users can create articles, interact through comments, bookmark content, like articles, manage profiles, and explore content created by other developers.

---

## Features

* User Authentication
* Email Verification
* Create, Edit and Delete Articles
* Article Categories
* Nested Comments & Replies
* Like System
* Bookmark System
* User Profiles
* Admin Dashboard
* Author Dashboard
* Article View Tracking
* Recently Viewed Articles
* Password Reset via Email
* Scheduled Tasks
* Queue Jobs

---

## Installation

Clone the repository:

```bash
git clone <repository-url>
cd devhub
```

Install dependencies:

```bash
composer install
npm install
npm run build
```

Create environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure your database inside `.env` and run migrations:

```bash
php artisan migrate --seed
```

---

## Email Configuration

Email functionality is disabled by default.

Open your `.env` file and uncomment/configure the following variables:

```env
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

After configuring your mail provider, clear cached configuration:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## Important

If you want email notifications during registration and login, make sure the mail-related code is uncommented inside:

```text
app/Http/Controllers/Auth/RegisterController.php
app/Http/Controllers/Auth/LoginController.php
```

---

## Queue Worker

Some features rely on queued jobs.

Start the queue worker:

```bash
php artisan queue:work
```

For production environments use a process manager such as Supervisor.

---

## Scheduler

Run the scheduler locally:

```bash
php artisan schedule:work
```

Production cron entry:

```bash
* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1
```

---

## Storage Link

Create the storage symlink:

```bash
php artisan storage:link
```

---

## Optimization

```bash
php artisan optimize
```

Clear caches:

```bash
php artisan optimize:clear
```

---

## Default Roles

### Admin

* Manage users
* Manage articles
* Access admin dashboard
* Moderate platform activity

### Author

* Create articles
* Edit own articles
* Comment on articles
* Bookmark articles
* Like articles

---

## Tech Stack

* Laravel
* MySQL
* Tailwind CSS
* Alpine.js
* Blade Components

---

## Development Notes

Before running the project ensure:

1. Database credentials are configured.
2. Mail configuration is configured and uncommented.
3. Queue worker is running.
4. Scheduler is running.
5. Storage link is generated.

Without queue workers and scheduler some automated features may not function correctly.

---

## License

This project is created for educational and portfolio purposes.
