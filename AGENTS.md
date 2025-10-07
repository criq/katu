# KATU Library - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the `criq/katu` library used in the v2 application. KATU is a custom PHP framework that provides MVC architecture, database abstraction, routing, and extensive utility classes.

---

## 1. Library Overview

### 1.1. Basic Information
- **Name:** `criq/katu`
- **Type:** Custom PHP framework library
- **Version:** 4.x (latest: 4.20251002.1)
- **Location:** `vendor/criq/katu/`
- **Namespace:** `Katu\`
- **Dependencies:** 30+ packages including Slim, Twig, Guzzle, Monolog, etc.

### 1.2. Core Architecture
- **MVC Pattern:** Models, Controllers, Views with custom routing
- **Dependency Injection:** PHP-DI container integration
- **Database ORM:** Custom ORM with Sexy query builder
- **PSR Compliance:** PSR-4 autoloading, PSR-7 HTTP messages, PSR-3 logging
- **Framework Integration:** Built on Slim 4 with Twig templating

---

## 2. Core Classes

### 2.1. Application Bootstrap (`Katu\App`)
**Location:** `src/App.php`

The main application class that bootstraps the entire framework:

```php
// Key methods:
public static function getInstance(): \Slim\App
public static function getContainer(): ContainerInterface
public static function getAppConfig(): \Katu\Config\AppConfig
public static function getLogger(TIdentifier $identifier): LoggerInterface
```

**Key Features:**
- Singleton pattern for application instance
- PHP-DI container configuration
- Automatic route registration from `RouterConfig`
- Error handling middleware setup
- Timezone configuration
- Autoloader registration

### 2.2. Model System (`Katu\Models\Model`)
**Location:** `src/Models/Model.php`

Base model class providing ORM functionality:

```php
// Key methods:
public function persist(): Model
public function persistInsert(): Model
public function persistUpdate(): Model
public function delete(): bool
public static function get(?string $id)
public static function getBy(?array $where = [], $orderBy = null, $limitOrPage = null)
public static function getOneBy(?array $where = [], $orderBy = null)
```

**Key Features:**
- Active Record pattern
- Automatic CRUD operations
- Callback system (before/after persist/delete)
- Unique column value generation
- Slug generation with collision handling
- File attachment management
- Transaction support

### 2.3. Controller Base (`Katu\Controllers\Controller`)
**Location:** `src/Controllers/Controller.php`

Base controller class with common functionality:

```php
// Key methods:
public function isSubmitted(ServerRequestInterface $request, ?string $name = null)
public function isSubmittedWithToken(ServerRequestInterface $request, ?string $name = null)
public function getViewData(array $data = []): array
public function addError(\Katu\Errors\Error $error): Controller
public function hasErrors(): bool
```

**Key Features:**
- Form submission detection
- CSRF token validation
- Error handling and collection
- View data preparation
- Exception management

---

## 3. Configuration System

### 3.1. Configuration Base (`Katu\Config\Config`)
**Location:** `src/Config/Config.php`

Abstract base class for all configuration classes. All configs extend this class and provide specific configuration data.

### 3.2. Key Configuration Classes

#### AppConfig (`Katu\Config\AppConfig`)
- Environment detection
- Base URL management
- Dependency injection definitions
- API URL configuration

#### DatabaseConfig (`Katu\Config\DatabaseConfig`)
- Abstract class for database configuration
- Connection management
- Multiple database support

#### RouterConfig (`Katu\Config\RouterConfig`)
- Abstract class for route definitions
- Route collection management

### 3.3. Configuration Pattern
All configuration classes follow this pattern:
```php
class ExampleConfig extends \Katu\Config\Config
{
    public function getSomeValue(): string
    {
        return "value";
    }
}
```

---

## 4. Database Layer

### 4.1. Connection Management (`Katu\PDO\Connection`)
**Location:** `src/PDO/Connection.php`

Database connection handler with advanced features:

```php
// Key methods:
public static function getInstance(string $title): Connection
public function select(\Sexy\Select $select, array $params = []): Query
public function createQuery($sql, array $params = []): Query
public function transaction($callback)
public function getTables(): TableCollection
```

**Key Features:**
- Connection pooling and singleton pattern
- Transaction support with automatic rollback
- Table and view introspection
- SQL mode management
- Process monitoring
- Query result caching with pickle system

### 4.2. Model Base (`Katu\Models\Base`)
**Location:** `src/Models/Base.php`

Abstract base class for all models:

```php
// Key methods:
public static function getConnection(): \Katu\PDO\Connection
public static function getTable(): \Katu\PDO\Table
public static function getColumn(string $name): \Katu\PDO\Column
public static function getBy(?array $where = [], $orderBy = null, $limitOrPage = null)
public static function getBySQL(\Sexy\Select $sql)
```

**Key Features:**
- Database connection management
- Table and column introspection
- Query building with Sexy ORM
- Result factory integration
- Transaction support

### 4.3. Query Builder Integration
Uses the `Sexy\Sexy` query builder for type-safe SQL generation:
```php
use Sexy\Sexy as SX;

$sql = SX::select()
    ->from(static::getTable())
    ->where(SX::eq(static::getColumn("name"), "value"))
    ->orderBy(SX::orderBy(static::getColumn("id")));
```

---

## 5. Routing System

### 5.1. Route Definition (`Katu\Tools\Routing\Route`)
**Location:** `src/Tools/Routing/Route.php`

Route class for defining application routes:

```php
// Key methods:
public function setPattern(string $pattern): Route
public function setCallback($callback): Route
public function setMethods(array $methods): Route
public function getArgs(): array
```

**Key Features:**
- Pattern-based routing with parameter extraction
- Multiple HTTP method support
- Callback function binding
- Route name management

### 5.2. Route Collection (`Katu\Tools\Routing\RouteCollection`)
**Location:** `src/Tools/Routing/RouteCollection.php`

Collection class for managing multiple routes with filtering and searching capabilities.

### 5.3. URL Generation (`Katu\Tools\Routing\URL`)
**Location:** `src/Tools/Routing/URL.php`

URL generation and manipulation utilities.

---

## 6. Utility Classes

### 6.1. Calendar System (`Katu\Tools\Calendar\*`)

#### Time (`Katu\Tools\Calendar\Time`)
**Location:** `src/Tools/Calendar/Time.php`

Extended DateTime class with additional functionality:

```php
// Key methods:
public static function createFromString(?string $string, bool $timeRequired): ?Time
public function getDbDateTimeFormat(): string
public function isToday(): bool
public function isInFuture(): bool
public function getAge(): Seconds
public function change($value): Time
```

**Key Features:**
- Multiple date format parsing
- Database format conversion
- Timezone handling
- Relative time calculations
- Time manipulation methods

#### Other Calendar Classes
- `Day`, `Week`, `Month`, `Year` - Date period classes
- `Interval`, `Timeout` - Time duration classes
- `Seconds` - Precise time measurement

### 6.2. Validation System (`Katu\Tools\Validation\*`)

#### Param (`Katu\Tools\Validation\Param`)
**Location:** `src/Tools/Validation/Param.php`

Parameter validation and processing:

```php
// Key methods:
public function setInput($value): Param
public function getOutput()
public function each(callable $callback): Param
public function forwardInput(): Param
```

**Key Features:**
- Input/output transformation
- Alias support
- Chainable validation
- REST response integration

### 6.3. Type System (`Katu\Types\*`)

#### TString (`Katu\Types\TString`)
**Location:** `src/Types/TString.php`

Enhanced string handling:

```php
// Key methods:
public function getForURL(?OptionCollection $options = null): TString
public function getSearchable(): TString
public function getWithAccentsRemoved(): TString
public function getAsFloat(): float
public function getNumberOfWords(): int
```

**Key Features:**
- URL-friendly string generation
- Search optimization
- Accent removal
- Numeric conversion
- Word counting

#### Other Type Classes
- `TURL`, `TEmailAddress` - Specialized string types
- `TArray`, `TClass` - Object type wrappers
- `TJSON`, `TPayload` - Data serialization types

### 6.4. REST API Support (`Katu\Tools\Rest\*`)

#### RestResponse (`Katu\Tools\Rest\RestResponse`)
**Location:** `src/Tools/Rest/RestResponse.php`

REST API response handling:

```php
// Key methods:
public function getResponse()
public function getJSON(): TJSON
public function getStream(): StreamInterface
```

**Key Features:**
- Automatic object serialization
- JSON response generation
- Stream interface support
- Nested response handling

### 6.5. File Management (`Katu\Files\*`)

#### File (`Katu\Files\File`)
**Location:** `src/Files/File.php`

File handling and manipulation utilities.

#### FileCollection (`Katu\Files\FileCollection`)
**Location:** `src/Files/FileCollection.php`

Collection of files with filtering and searching.

### 6.6. Image Processing (`Katu\Tools\Images\*`)

#### Image (`Katu\Tools\Images\Image`)
**Location:** `src/Tools/Images/Image.php`

Image manipulation and processing.

#### ImageVersion (`Katu\Tools\Images\ImageVersion`)
**Location:** `src/Tools/Images/ImageVersion.php`

Image versioning and transformation.

### 6.7. Email System (`Katu\Tools\Emails\*`)

#### Email (`Katu\Tools\Emails\Email`)
**Location:** `src/Tools/Emails/Email.php`

Email composition and sending.

#### Provider (`Katu\Tools\Emails\Provider`)
**Location:** `src/Tools/Emails/Provider.php`

Email provider abstraction.

### 6.8. Security (`Katu\Tools\Security\*`)

#### PlainPassword (`Katu\Tools\Security\PlainPassword`)
**Location:** `src/Tools/Security/PlainPassword.php`

Password handling and hashing.

#### JWT (`Katu\Tools\Security\JWT`)
**Location:** `src/Tools/Security/JWT.php`

JSON Web Token handling.

### 6.9. Session Management (`Katu\Tools\Session\*`)

#### Session (`Katu\Tools\Session\Session`)
**Location:** `src/Tools/Session/Session.php`

Session handling and management.

#### Flash (`Katu\Tools\Session\Flash`)
**Location:** `src/Tools/Session/Flash.php`

Flash message system.

### 6.10. Caching (`Katu\Cache\*`)

#### General (`Katu\Cache\General`)
**Location:** `src/Cache/General.php`

General-purpose caching system.

#### Runtime (`Katu\Cache\Runtime`)
**Location:** `src/Cache/Runtime.php`

Runtime memory caching.

---

## 7. Exception System

### 7.1. Exception Hierarchy
All exceptions extend `Katu\Exceptions\Exception`:

- `DatabaseConnectionException` - Database connection issues
- `ModelNotFoundException` - Model not found errors
- `RouteException` - Routing errors
- `ValidationException` - Input validation errors
- `UnauthorizedException` - Authentication errors
- `ForbiddenException` - Authorization errors
- `NotFoundException` - Resource not found errors

### 7.2. Exception Collection (`Katu\Exceptions\ExceptionCollection`)
**Location:** `src/Exceptions/ExceptionCollection.php`

Collection class for managing multiple exceptions.

---

## 8. Error Handling

### 8.1. Error System (`Katu\Errors\*`)

#### Error (`Katu\Errors\Error`)
**Location:** `src/Errors/Error.php`

Individual error representation.

#### ErrorCollection (`Katu\Errors\ErrorCollection`)
**Location:** `src/Errors/ErrorCollection.php`

Collection of errors with management methods.

---

## 9. Integration Points

### 9.1. Slim Framework Integration
- Uses Slim 4 for HTTP handling
- PSR-7 request/response interfaces
- Middleware support
- Route management

### 9.2. Twig Integration
- Custom Twig extensions
- Template inheritance
- Custom filters and functions

### 9.3. PHP-DI Integration
- Dependency injection container
- Service definitions
- Auto-wiring support

### 9.4. Third-Party Services
- Google Cloud integration
- AWS SDK integration
- Email service providers
- Payment processing

---

## 10. Development Patterns

### 10.1. Model Development
```php
class ExampleModel extends \App\Models\Model
{
    const DATABASE = "app";
    const TABLE = "examples";
    
    public $id;
    public $name;
    public $timeCreated;
    
    public static function getIdColumn(): Column
    {
        return static::getColumn("id");
    }
}
```

### 10.2. Controller Development
```php
class ExampleController extends \App\Classes\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        // Controller logic here
        return $this->getViewResponse("template", $this->getViewData());
    }
}
```

### 10.3. Configuration Development
```php
class ExampleConfig extends \Katu\Config\Config
{
    public function getSomeValue(): string
    {
        return "value";
    }
}
```

---

## 11. Key Dependencies

### 11.1. Core Dependencies
- `slim/slim` - HTTP framework
- `twig/twig` - Template engine
- `php-di/php-di` - Dependency injection
- `guzzlehttp/guzzle` - HTTP client
- `monolog/monolog` - Logging

### 11.2. Database Dependencies
- `sexytool/sexytool` - Query builder (referenced as Sexy\Sexy)

### 11.3. Utility Dependencies
- `symfony/string` - String manipulation
- `symfony/intl` - Internationalization
- `intervention/image` - Image processing
- `endroid/qr-code` - QR code generation

---

## 12. Best Practices

### 12.1. Model Usage
- Always extend `App\Models\Model` for application models
- Use `const DATABASE` and `const TABLE` constants
- Implement `getIdColumn()` for custom primary keys
- Use callbacks for business logic hooks

### 12.2. Controller Usage
- Extend `App\Classes\Controller` for application controllers
- Use `getResponse()` method for PSR-7 compliance
- Implement proper error handling
- Use view data methods for template variables

### 12.3. Configuration Usage
- Extend `Katu\Config\Config` for all configurations
- Use descriptive method names
- Implement proper type hints
- Use dependency injection for complex configurations

### 12.4. Error Handling
- Use appropriate exception types
- Implement proper error collections
- Provide meaningful error messages
- Use logging for debugging

---

## 13. Common Patterns

### 13.1. Database Queries
```php
// Simple query
$users = User::getBy(["active" => true]);

// Complex query with Sexy ORM
$sql = SX::select()
    ->from(User::getTable())
    ->where(SX::eq(User::getColumn("active"), true))
    ->orderBy(SX::orderBy(User::getColumn("name")));
$users = User::getBySQL($sql);
```

### 13.2. Model Persistence
```php
$user = new User();
$user->name = "John Doe";
$user->email = "john@example.com";
$user->persist();
```

### 13.3. Validation
```php
$param = new Param("email", $request->getParsedBody()["email"]);
$param->forwardInput();
// Add validation rules...
```

### 13.4. REST Responses
```php
$response = new RestResponse($data);
return $response->getStream();
```

---

## 14. Troubleshooting

### 14.1. Common Issues
- **Database Connection Errors:** Check `DatabaseConfig` implementation
- **Route Not Found:** Verify route registration in `RouterConfig`
- **Model Not Found:** Check `DATABASE` and `TABLE` constants
- **Template Errors:** Verify Twig configuration and template paths

### 14.2. Debugging
- Use `\App\App::getLogger()` for logging
- Check exception collections for detailed error information
- Use database query dumps for SQL debugging
- Enable Twig debug mode for template issues

---

## 15. Migration Notes

### 15.1. Version Compatibility
- KATU 4.x requires PHP 7.4+
- Slim 4 compatibility
- PSR-7 compliance
- Modern PHP features usage

### 15.2. Breaking Changes
- Method signature changes in major versions
- Configuration class structure updates
- Database connection handling improvements
- Exception hierarchy changes

---

This documentation provides a comprehensive overview of the KATU library. For specific implementation details, refer to the source code in `vendor/criq/katu/src/` and the application-specific implementations in `app/`.
