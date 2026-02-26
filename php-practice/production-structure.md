# Production-Ready PHP Application Structure

A professional PHP project structure following industry best practices.

```
my-php-app/
│
├── app/                        # Core application code
│   ├── Controllers/            # Handle HTTP requests
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   └── Api/
│   │       └── UserController.php
│   │
│   ├── Models/                 # Business entities
│   │   ├── User.php
│   │   └── Product.php
│   │
│   ├── Services/               # Business logic
│   │   ├── AuthService.php
│   │   └── PaymentService.php
│   │
│   ├── Repositories/           # Data access layer
│   │   └── UserRepository.php
│   │
│   ├── Middleware/             # HTTP middleware
│   │   ├── AuthMiddleware.php
│   │   └── CsrfMiddleware.php
│   │
│   ├── Exceptions/             # Custom exceptions
│   │   ├── ValidationException.php
│   │   └── NotFoundException.php
│   │
│   └── Helpers/                # Utility functions
│       ├── helpers.php
│       └── security.php
│
├── bootstrap/                  # App initialization
│   ├── app.php                 # Application bootstrap
│   ├── container.php           # DI container setup
│   └── routes/
│       ├── web.php             # Web routes
│       └── api.php             # API routes
│
├── config/                     # Configuration files
│   ├── app.php                 # App settings
│   ├── database.php            # DB connections
│   ├── cache.php               # Cache settings
│   ├── mail.php                # Email settings
│   └── auth.php                # Auth settings
│
├── database/                   # Database files
│   ├── migrations/             # Schema migrations
│   │   ├── 2024_01_01_create_users_table.php
│   │   └── 2024_01_02_create_products_table.php
│   ├── seeders/                # Test data
│   │   └── UserSeeder.php
│   └── factories/              # Model factories
│       └── UserFactory.php
│
├── public/                     # Web server document root ←── point here!
│   ├── index.php               # Single entry point
│   ├── .htaccess               # Apache rewrite rules
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
│
├── resources/                  # Frontend/template resources
│   ├── views/                  # Template files (.php / .twig)
│   │   ├── layouts/
│   │   │   └── main.php
│   │   └── auth/
│   │       ├── login.php
│   │       └── register.php
│   └── lang/                   # Translations
│       ├── en.php
│       └── bn.php
│
├── routes/                     # Route definitions
│   ├── web.php
│   └── api.php
│
├── storage/                    # Runtime storage (writable)
│   ├── logs/
│   │   └── app.log
│   ├── cache/
│   ├── sessions/
│   └── uploads/
│
├── tests/                      # Automated tests
│   ├── Unit/
│   │   └── UserTest.php
│   ├── Feature/
│   │   └── AuthTest.php
│   └── TestCase.php
│
├── vendor/                     # Composer packages (gitignored)
│
├── .env                        # Environment variables (gitignored)
├── .env.example                # Environment template (committed)
├── .gitignore
├── composer.json               # PHP dependency manifest
├── phpunit.xml                 # Test configuration
└── README.md
```

---

## Apache `.htaccess` (for public/)

```apache
Options -Indexes
RewriteEngine On

# Force HTTPS in production
# RewriteCond %{HTTPS} off
# RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Route all requests through index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name myapp.com;
    root /var/www/myapp/public;
    index index.php;

    # Route all traffic through index.php
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Block dotfiles
    location ~ /\. { deny all; }

    # Block access to storage
    location ~ ^/(storage|bootstrap)/.*\.php$ { deny all; }
}
```

---

## `.env` Structure

```env
APP_NAME="My PHP App"
APP_ENV=production        # local | staging | production
APP_DEBUG=false
APP_URL=https://myapp.com
APP_KEY=base64:GENERATE_RANDOM_32_CHAR_KEY_HERE

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=dbuser
DB_PASSWORD=strongpassword

CACHE_DRIVER=redis
SESSION_DRIVER=redis

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM=no-reply@myapp.com

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## `composer.json` Starter Template

```json
{
    "name": "yourname/myapp",
    "description": "A production-ready PHP application",
    "type": "project",
    "require": {
        "php": "^8.2"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "friendsofphp/php-cs-fixer": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        },
        "files": [
            "app/Helpers/helpers.php"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "fix": "php-cs-fixer fix app --rules=@PSR12"
    }
}
```

---

## Production Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Generate unique `APP_KEY`
- [ ] Set `display_errors = Off` in `php.ini`
- [ ] Enable HTTPS with Let's Encrypt
- [ ] Configure rate limiting (nginx/firewall)
- [ ] Set file permissions (`chmod 755 storage/ bootstrap/cache/`)
- [ ] Enable OPcache in `php.ini`
- [ ] Configure proper logging (rotation, level)
- [ ] Add CSRF protection on all state-changing forms
- [ ] Use prepared statements everywhere
- [ ] Sanitize all user output with `htmlspecialchars()`
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Set up a CI/CD pipeline (GitHub Actions, etc.)
