# KATU Framework

A comprehensive PHP framework built on Slim 4, providing MVC architecture, database abstraction, routing, and extensive utility classes for modern web applications.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Architecture](#architecture)
- [Core Components](#core-components)
- [Configuration](#configuration)
- [Models & Database](#models--database)
- [Controllers](#controllers)
- [Views & Templates](#views--templates)
- [Routing](#routing)
- [Utilities](#utilities)
- [Security](#security)
- [Caching](#caching)
- [File Management](#file-management)
- [Email System](#email-system)
- [API Documentation](#api-documentation)
- [Best Practices](#best-practices)
- [Examples](#examples)
- [Contributing](#contributing)

## Overview

KATU is a modern PHP framework that extends Slim 4 with additional functionality for building robust web applications. It provides:

- **MVC Architecture**: Clean separation of concerns with Models, Controllers, and Views
- **Database ORM**: Active Record pattern with custom query builder integration
- **Dependency Injection**: PHP-DI container for service management
- **PSR Compliance**: PSR-4 autoloading, PSR-7 HTTP messages, PSR-3 logging
- **Extensive Utilities**: Calendar, validation, HTML generation, file management, and more
- **Security Features**: JWT handling, password encoding, encryption
- **Modern PHP**: Optimized for PHP 7.4+ with enhanced PSR compliance

## Installation

### Requirements

- PHP 7.4 or higher
- Composer
- MySQL/PostgreSQL database (optional)

### Composer Installation

```bash
composer require criq/katu
```

### Dependencies

KATU includes 30+ carefully selected packages:

- **Framework**: Slim 4, Twig, PHP-DI
- **Database**: Custom ORM with Sexy query builder
- **Security**: JWT, encryption, OAuth2
- **Utilities**: Guzzle, Monolog, Symfony components
- **Cloud Services**: AWS SDK, Google Cloud, SendGrid
- **Image Processing**: Intervention Image, QR codes
- **Development**: PHPUnit, CodeSniffer

## Quick Start

### 1. Basic Application Setup

```php
<?php
// bootstrap.php
require_once 'vendor/autoload.php';

use Katu\App;
use Katu\Config\AppConfig;

// Initialize the application
$app = App::getInstance();

// Your application logic here
```

### 2. Simple Route

```php
// In your RouterConfig
public function getRoutes(): array
{
    return [
        'home' => (new Route)
            ->setPattern('/')
            ->setMethods(['GET'])
            ->setCallback(function($request, $response) {
                return $response->withJson(['message' => 'Hello KATU!']);
            })
    ];
}
```

### 3. Basic Model

```php
<?php
namespace App\Models;

use Katu\Models\Model;

class User extends Model
{
    const DATABASE = "app";
    const TABLE = "users";

    public $id;
    public $name;
    public $email;
    public $timeCreated;

    public static function getIdColumn(): Column
    {
        return static::getColumn("id");
    }
}
```

## Architecture

### Core Components

```
KATU Framework
├── App (Bootstrap)
├── Config (Configuration System)
├── Models (ORM & Database)
├── Controllers (Request Handling)
├── Views (Template System)
├── Tools (Utilities)
├── Types (Type System)
├── Storage (File Storage)
└── Cache (Caching System)
```

### MVC Pattern

- **Models**: Database entities with Active Record pattern
- **Controllers**: Request handling and business logic
- **Views**: Twig templates with custom extensions
- **Routing**: Pattern-based routing with parameter extraction

## Core Components

### Application Bootstrap (`Katu\App`)

The main application class that bootstraps the entire framework:

```php
// Get application instance
$app = App::getInstance();

// Get dependency injection container
$container = App::getContainer();

// Get configuration
$config = App::getAppConfig();

// Get logger
$logger = App::getLogger(new TIdentifier("my-component"));
```

### Configuration System

All configurations extend `Katu\Config\Config`:

```php
class MyConfig extends \Katu\Config\Config
{
    public function getDatabaseHost(): string
    {
        return "localhost";
    }

    public function getDatabaseName(): string
    {
        return "myapp";
    }
}
```

## Configuration

### Environment Configuration

```php
// .env file
APP_ENV=production
DB_HOST=localhost
DB_NAME=myapp
DB_USER=user
DB_PASS=password
```

### Database Configuration

```php
class DatabaseConfig extends \Katu\Config\DatabaseConfig
{
    public function getHost(): string
    {
        return $_ENV['DB_HOST'] ?? 'localhost';
    }

    public function getName(): string
    {
        return $_ENV['DB_NAME'] ?? 'myapp';
    }
}
```

## Models & Database

### Active Record Pattern

```php
class User extends Model
{
    const DATABASE = "app";
    const TABLE = "users";

    public $id;
    public $name;
    public $email;
    public $timeCreated;

    // Create a new user
    public static function createUser(string $name, string $email): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->timeCreated = new Time();
        return $user->persist();
    }

    // Find users by criteria
    public static function findActiveUsers(): array
    {
        return static::getBy(['active' => true]);
    }
}
```

### Database Queries

```php
use Sexy\Sexy as SX;

// Simple query
$users = User::getBy(['active' => true]);

// Complex query with Sexy ORM
$sql = SX::select()
    ->from(User::getTable())
    ->where(SX::eq(User::getColumn("active"), true))
    ->orderBy(SX::orderBy(User::getColumn("name")));
$users = User::getBySQL($sql);
```

### Model Relationships

```php
class Post extends Model
{
    const DATABASE = "app";
    const TABLE = "posts";

    public $id;
    public $title;
    public $content;
    public $userId;

    public function getUser(): ?User
    {
        return User::get($this->userId);
    }

    public function getComments(): array
    {
        return Comment::getBy(['postId' => $this->id]);
    }
}
```

## Controllers

### Base Controller

```php
class UserController extends \Katu\Controllers\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        $users = User::getBy(['active' => true]);

        return $this->getViewResponse('users/index', [
            'users' => $users
        ]);
    }

    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isSubmitted($request)) {
            $name = $request->getParsedBody()['name'] ?? '';
            $email = $request->getParsedBody()['email'] ?? '';

            if ($name && $email) {
                $user = User::createUser($name, $email);
                return $this->getRedirectResponse('/users');
            }
        }

        return $this->getViewResponse('users/create');
    }
}
```

### Form Handling

```php
public function handleForm(ServerRequestInterface $request): ResponseInterface
{
    if ($this->isSubmittedWithToken($request)) {
        $data = $request->getParsedBody();

        // Validate data
        $param = new Param('name', $data['name'] ?? '');
        $param->forwardInput();

        if ($param->getOutput()) {
            // Process form
            $user = new User();
            $user->name = $param->getOutput();
            $user->persist();

            return $this->getRedirectResponse('/success');
        }
    }

    return $this->getViewResponse('form');
}
```

## Views & Templates

### Twig Integration

```twig
{# users/index.twig #}
{% extends "layout.twig" %}

{% block content %}
    <h1>Users</h1>
    <ul>
        {% for user in users %}
            <li>{{ user.name }} - {{ user.email }}</li>
        {% endfor %}
    </ul>
{% endblock %}
```

### Custom Twig Extensions

```php
// Custom Twig functions available in templates
{{ url('user.show', {id: user.id}) }}
{{ asset('css/style.css') }}
{{ csrf_token() }}
```

## Routing

### Route Definition

```php
class RouterConfig extends \Katu\Config\RouterConfig
{
    public function getRoutes(): array
    {
        return [
            'home' => (new Route)
                ->setPattern('/')
                ->setMethods(['GET'])
                ->setCallback([HomeController::class, 'index']),

            'user.show' => (new Route)
                ->setPattern('/users/{id}')
                ->setMethods(['GET'])
                ->setCallback([UserController::class, 'show']),

            'api.users' => (new Route)
                ->setPattern('/api/users')
                ->setMethods(['GET', 'POST'])
                ->setCallback([UserApiController::class, 'handle'])
        ];
    }
}
```

### URL Generation

```php
// Generate URLs
$url = URL::getRoute('user.show', ['id' => 123]);
// Result: /users/123

$url = URL::getRoute('home');
// Result: /
```

## Utilities

### Calendar System

```php
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Day;
use Katu\Tools\Calendar\Interval;

// Time manipulation
$time = new Time();
$tomorrow = $time->add(new Interval('P1D'));
$lastWeek = $time->subtract(new Interval('P1W'));

// Date periods
$day = new Day($time);
$week = new Week($time);
$month = new Month($time);
```

### Validation System

```php
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Rules\IsNotEmpty;
use Katu\Tools\Validation\Rules\IsEmail;

$param = new Param('email', $request->getParsedBody()['email'] ?? '');
$param->addRule(new IsNotEmpty())
      ->addRule(new IsEmail())
      ->forwardInput();

if ($param->getOutput()) {
    // Valid email
    $email = $param->getOutput();
}
```

### HTML Generation

```php
use Katu\Tools\HTML\HTML;
use Katu\Tools\HTML\A;
use Katu\Tools\HTML\Div;

// Create HTML elements
$link = new A('Click here', '/some-url');
$div = new Div('Content', ['class' => 'container']);

// Combine elements
$html = new HTML($div->getHTML() . $link->getHTML());
```

### Type System

```php
use Katu\Types\TString;
use Katu\Types\TURL;
use Katu\Types\TEmailAddress;

// Enhanced string handling
$string = new TString('Hello World');
$urlString = $string->getForURL(); // "hello-world"

// URL handling
$url = new TURL('https://example.com');
$domain = $url->getDomain(); // "example.com"

// Email handling
$email = new TEmailAddress('user@example.com');
$isValid = $email->isValid(); // true
```

## Security

### Password Handling

```php
use Katu\Tools\Security\PlainPassword;

$password = new PlainPassword('user-password');
$hashed = $password->getHashed(); // Secure hash
$isValid = $password->verify($hashed, 'user-password'); // true
```

### JWT Tokens

```php
use Katu\Tools\Security\JWT;

$jwt = new JWT();
$token = $jwt->create(['user_id' => 123]);
$payload = $jwt->verify($token);
```

### Encryption

```php
use Katu\Types\Encryption\TEncryptedString;

$encrypted = new TEncryptedString('sensitive-data');
$encryptedString = $encrypted->getEncrypted();
$decrypted = $encrypted->getDecrypted();
```

## Caching

### Runtime Caching

```php
use Katu\Cache\Runtime;

$cache = new Runtime();
$cache->set('key', 'value', 3600); // 1 hour
$value = $cache->get('key');
```

### General Caching

```php
use Katu\Cache\General;

$cache = new General();
$cache->set('user:123', $userData);
$userData = $cache->get('user:123');
```

## File Management

### File Operations

```php
use Katu\Files\File;

$file = new File('/path/to/file.txt');
$content = $file->get();
$file->set('new content');

if ($file->exists()) {
    $size = $file->getSize();
    $mimeType = $file->getMimeType();
}
```

### File Uploads

```php
use Katu\Files\Upload;

$upload = new Upload($_FILES['file']);
if ($upload->isValid()) {
    $file = $upload->moveTo('/uploads/');
    $url = $file->getURL();
}
```

### File Collections

```php
use Katu\Files\FileCollection;

$files = new FileCollection();
$files->add($file1);
$files->add($file2);

foreach ($files as $file) {
    echo $file->getPath();
}
```

## Email System

### Email Composition

```php
use Katu\Tools\Emails\Email;

$email = new Email();
$email->setTo('user@example.com')
      ->setSubject('Welcome!')
      ->setBody('Welcome to our application!')
      ->send();
```

### Email Providers

```php
use Katu\Tools\Emails\Provider\SendGrid;

$provider = new SendGrid();
$email->setProvider($provider);
$email->send();
```

## API Documentation

### REST API Responses

```php
use Katu\Tools\Rest\RestResponse;

$data = ['users' => $users];
$response = new RestResponse($data);
return $response->getStream();
```

### JSON API

```php
public function apiUsers(ServerRequestInterface $request): ResponseInterface
{
    $users = User::getBy(['active' => true]);

    return (new RestResponse([
        'data' => $users,
        'meta' => [
            'count' => count($users),
            'timestamp' => (new Time())->getDbDateTimeFormat()
        ]
    ]))->getStream();
}
```

## Best Practices

### Model Development

```php
class User extends Model
{
    const DATABASE = "app";
    const TABLE = "users";

    public $id;
    public $name;
    public $email;
    public $timeCreated;

    // Always implement getIdColumn for custom primary keys
    public static function getIdColumn(): Column
    {
        return static::getColumn("id");
    }

    // Use callbacks for business logic
    public function beforePersist(): void
    {
        if (!$this->timeCreated) {
            $this->timeCreated = new Time();
        }
    }
}
```

### Controller Development

```php
class UserController extends \Katu\Controllers\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $users = User::getBy(['active' => true]);
            return $this->getViewResponse('users/index', [
                'users' => $users
            ]);
        } catch (\Exception $e) {
            $this->addError(new Error('Failed to load users'));
            return $this->getViewResponse('error');
        }
    }
}
```

### Error Handling

```php
use Katu\Errors\Error;
use Katu\Errors\ErrorCollection;

$errors = new ErrorCollection();
$errors->add(new Error('Invalid email format'));

if ($errors->count() > 0) {
    // Handle errors
    foreach ($errors as $error) {
        echo $error->getMessage();
    }
}
```

## Examples

### Complete User Management

```php
// Model
class User extends Model
{
    const DATABASE = "app";
    const TABLE = "users";

    public $id;
    public $name;
    public $email;
    public $active;
    public $timeCreated;

    public static function createUser(string $name, string $email): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->active = true;
        $user->timeCreated = new Time();
        return $user->persist();
    }
}

// Controller
class UserController extends \Katu\Controllers\Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = User::getBy(['active' => true]);
        return $this->getViewResponse('users/index', ['users' => $users]);
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isSubmitted($request)) {
            $data = $request->getParsedBody();

            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';

            if ($name && $email) {
                User::createUser($name, $email);
                return $this->getRedirectResponse('/users');
            }
        }

        return $this->getViewResponse('users/create');
    }
}

// Route
'users.index' => (new Route)
    ->setPattern('/users')
    ->setMethods(['GET'])
    ->setCallback([UserController::class, 'index']),

'users.create' => (new Route)
    ->setPattern('/users/create')
    ->setMethods(['GET', 'POST'])
    ->setCallback([UserController::class, 'create'])
```

### API Endpoint

```php
class UserApiController extends \Katu\Controllers\Controller
{
    public function getUsers(ServerRequestInterface $request): ResponseInterface
    {
        $users = User::getBy(['active' => true]);

        return (new RestResponse([
            'data' => $users,
            'meta' => [
                'count' => count($users),
                'timestamp' => (new Time())->getDbDateTimeFormat()
            ]
        ]))->getStream();
    }
}
```

## Contributing

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code style: `composer cs-check`

### Code Standards

- Follow PSR-12 coding standards
- Write comprehensive tests
- Document all public methods
- Use type hints where possible

### Testing

```bash
# Run all tests
composer test

# Run specific test suite
vendor/bin/phpunit tests/Models/

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

---

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For support and questions:

- Create an issue on GitHub
- Check the documentation
- Review the examples

---

**KATU Framework** - Building modern PHP applications with ease.