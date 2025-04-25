# MailZila

MailZila is a Laravel-based email marketing application with AdminLTE integration for a modern admin interface.

## Features

- User authentication with Laravel
- Admin dashboard powered by AdminLTE
- Profile and account management
- Clean, responsive design
- Modern UI

## Requirements

- PHP 8.2+
- Laravel 12.x
- Composer
- Node.js and NPM

## Installation

1. Clone the repository
```bash
git clone https://github.com/zagrox/mailzila.git
cd mailzila
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment variables
```bash
cp .env.example .env
php artisan key:generate
```

4. Set up the database
```bash
php artisan migrate --seed
```

5. Build frontend assets
```bash
npm run build
```

6. Start the server
```bash
php artisan serve
```

7. Access the application at http://localhost:8000

## Default Credentials

- Email: admin@mailzila.com
- Password: password

## License

This project is open-sourced software.
