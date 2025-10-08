# Configuration System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Configuration system in the KATU framework. The configuration system provides centralized management of application settings, database connections, routing, environment variables, and third-party integrations.

---

## 1. System Overview

### 1.1. Purpose
- **Application Configuration:** Centralized application settings and environment management
- **Database Configuration:** Database connection management with encryption support
- **Routing Configuration:** Route definition and URL management
- **Environment Management:** Environment variable loading and processing
- **Third-Party Integration:** External service configuration (Google, AWS, etc.)
- **Internationalization:** Locale and language configuration
- **Security Configuration:** Encryption, authentication, and permission settings

### 1.2. Architecture
- **Base Class:** `Config` - Abstract base for all configuration classes
- **Core Configs:** `AppConfig`, `DatabaseConfig`, `RouterConfig`, `EnvConfig`
- **Specialized Configs:** Database connections, encryption, images, time, etc.
- **Collection Support:** Configuration collections for managing multiple configs
- **Environment Integration:** Dotenv integration for environment variables
- **Dependency Injection:** PHP-DI container integration for configuration

---

## 2. Core Configuration Classes

### 2.1. Config (`Katu\Config\Config`)
**Location:** `Config.php`

Abstract base class for all configuration classes:

```php
// Base class structure:
abstract class Config
{
    // All config classes extend this base
}
```

**Key Features:**
- Abstract base for all configuration classes
- Consistent interface for configuration management
- Type safety and validation support

### 2.2. AppConfig (`Katu\Config\AppConfig`)
**Location:** `AppConfig.php`

Main application configuration:

```php
// Key methods:
public function getIsEnvironment(string $environment): bool
public function getEnvironment(): string
public function getBaseURL(): TURL
public function getAPIURL(): ?TURL
public function getDIDefinitions(): array
```

**Key Features:**
- Environment detection and validation
- Base URL and API URL management
- Dependency injection definitions
- Application environment configuration

### 2.3. DatabaseConfig (`Katu\Config\DatabaseConfig`)
**Location:** `DatabaseConfig.php`

Abstract database configuration:

```php
// Key methods:
abstract public function getDatabaseConnectionConfigs(): DatabaseConnectionConfigCollection
```

**Key Features:**
- Abstract base for database configuration
- Multiple database connection support
- Connection collection management

### 2.4. DatabaseConnectionConfig (`Katu\Config\DatabaseConnectionConfig`)
**Location:** `DatabaseConnectionConfig.php`

Individual database connection configuration:

```php
// Key methods:
public function __construct(string $title, string $host, string $user, string $plainPassword, string $database)
public function getTitle(): string
public function getHost(): string
public function getUser(): string
public function getPlainPassword(): string
public function getDatabase(): string
public function getCharset(): ?string
public function getIsProfiled(): bool
public function getPDODSN(): string
```

**Key Features:**
- Secure password encryption
- Connection profiling support
- PDO DSN generation
- Charset configuration
- Connection title management

### 2.5. RouterConfig (`Katu\Config\RouterConfig`)
**Location:** `RouterConfig.php`

Route configuration management:

```php
// Key methods:
abstract public function getRoutes(): RouteCollection
```

**Key Features:**
- Route collection management
- Abstract route definition
- URL pattern configuration

### 2.6. EnvConfig (`Katu\Config\EnvConfig`)
**Location:** `EnvConfig.php`

Environment variable configuration:

```php
// Key methods:
public function getEnvFiles(): FileCollection
public function getVariables(): array
public function getVariable(string $variable): ?string
```

**Key Features:**
- Dotenv integration
- Environment file management
- Variable caching
- Fallback handling

---

## 3. Specialized Configuration Classes

### 3.1. Database Connection Configs

#### MySQLDatabaseConnectionConfig (`Katu\Config\MySQLDatabaseConnectionConfig`)
**Location:** `MySQLDatabaseConnectionConfig.php`

MySQL-specific database configuration with MySQL driver and schema.

#### MSSQLDatabaseConnectionConfig (`Katu\Config\MSSQLDatabaseConnectionConfig`)
**Location:** `MSSQLDatabaseConnectionConfig.php`

MSSQL-specific database configuration with MSSQL driver and schema.

### 3.2. Collection Configuration Classes

#### DatabaseConnectionConfigCollection (`Katu\Config\DatabaseConnectionConfigCollection`)
**Location:** `DatabaseConnectionConfigCollection.php`

Collection for managing multiple database connections.

#### SolrConnectionConfigCollection (`Katu\Config\SolrConnectionConfigCollection`)
**Location:** `SolrConnectionConfigCollection.php`

Collection for managing Solr search connections.

### 3.3. Specialized Configs

#### IntlConfig (`Katu\Config\IntlConfig`)
**Location:** `IntlConfig.php`

Internationalization configuration:

```php
// Key methods:
abstract public function getSupportedLocales(): LocaleCollection
abstract public function getDefaultLocale(): Locale
```

**Key Features:**
- Locale management
- Internationalization support
- Multi-language configuration

#### TimeConfig (`Katu\Config\TimeConfig`)
**Location:** `TimeConfig.php`

Time and timezone configuration:

```php
// Key methods:
public function getTimezone(): \DateTimeZone
```

**Key Features:**
- Timezone configuration
- Default UTC timezone
- Time management

#### ImageConfig (`Katu\Config\ImageConfig`)
**Location:** `ImageConfig.php`

Image processing configuration:

```php
// Key methods:
public function getVersions(): VersionCollection
public function getCacheTimeout(): int
```

**Key Features:**
- Image version management
- Cache timeout configuration
- Image processing settings

#### EncryptionConfig (`Katu\Config\EncryptionConfig`)
**Location:** `EncryptionConfig.php`

Encryption and security configuration.

#### CookieConfig (`Katu\Config\CookieConfig`)
**Location:** `CookieConfig.php`

Cookie configuration and management.

#### PaginationConfig (`Katu\Config\PaginationConfig`)
**Location:** `PaginationConfig.php`

Pagination settings and configuration.

#### RedisConfig (`Katu\Config\RedisConfig`)
**Location:** `RedisConfig.php`

Redis cache configuration.

#### SolrConfig (`Katu\Config\SolrConfig`)
**Location:** `SolrConfig.php`

Solr search configuration.

#### UserPermissionConfig (`Katu\Config\UserPermissionConfig`)
**Location:** `UserPermissionConfig.php`

User permission configuration:

```php
// Key methods:
abstract public function getPermissions(): array
```

**Key Features:**
- Permission definition
- Access control configuration
- User role management

---

## 4. Third-Party Integration Configs

### 4.1. Google Integration (`Katu\Config\ThirdParty\Google\*`)

#### SecretManagerConfig (`Katu\Config\ThirdParty\Google\SecretManagerConfig`)
**Location:** `ThirdParty/Google/SecretManagerConfig.php`

Google Secret Manager configuration for secure credential management.

---

## 5. Configuration Patterns

### 5.1. Configuration Class Structure
```php
class ExampleConfig extends \Katu\Config\Config
{
    public function getSomeValue(): string
    {
        return "value";
    }

    public function getSomeArray(): array
    {
        return ["key" => "value"];
    }
}
```

### 5.2. Database Connection Pattern
```php
class ExampleDatabaseConnectionConfig extends \Katu\Config\DatabaseConnectionConfig
{
    public function getSchema(): string
    {
        return "mysql";
    }

    public function getDriver(): string
    {
        return "pdo_mysql";
    }
}
```

### 5.3. Collection Configuration Pattern
```php
class ExampleConfig extends \Katu\Config\Config
{
    public function getItems(): ItemCollection
    {
        return new ItemCollection([
            // Configuration items
        ]);
    }
}
```

---

## 6. Environment Variable Management

### 6.1. Environment File Loading
- **Primary File:** `.env` in application root
- **Loading Strategy:** Immutable dotenv loading
- **Fallback Handling:** Graceful handling of missing files
- **Variable Caching:** Static variable caching for performance

### 6.2. Common Environment Variables
- `APP_ENV` - Application environment (development, production, etc.)
- `APP_SCHEMA` - Application URL schema (http, https)
- `APP_HOST` - Application hostname
- Database connection variables
- Third-party service credentials

---

## 7. Dependency Injection Integration

### 7.1. DI Definitions
The `AppConfig::getDIDefinitions()` method provides model class mappings:

```php
public function getDIDefinitions(): array
{
    return [
        \Katu\Models\Presets\AccessToken::class => \App\Models\Users\AccessToken::class,
        \Katu\Models\Presets\EmailAddress::class => \App\Models\EmailAddress::class,
        // ... more mappings
    ];
}
```

### 7.2. Container Integration
- PHP-DI container integration
- Service definition management
- Auto-wiring support
- Configuration-based service registration

---

## 8. Security Features

### 8.1. Password Encryption
- **Encrypted Storage:** Passwords stored as `TEncryptedString`
- **Secure Encryption:** Uses framework encryption utilities
- **Plain Text Access:** Secure plain text password retrieval

### 8.2. Connection Security
- **Encrypted Credentials:** Database passwords encrypted at rest
- **Secure DSN Generation:** Safe PDO DSN construction
- **Connection Profiling:** Optional query profiling

---

## 9. Best Practices

### 9.1. Configuration Development
- Extend `\Katu\Config\Config` for all configuration classes
- Use descriptive method names
- Implement proper type hints
- Use collections for multiple items

### 9.2. Environment Management
- Use environment variables for sensitive data
- Provide sensible defaults
- Implement proper fallback handling
- Cache environment variables for performance

### 9.3. Database Configuration
- Use encrypted passwords
- Configure appropriate charsets
- Enable profiling in development
- Use connection titles for identification

### 9.4. Security Considerations
- Never store plain text passwords
- Use encrypted configuration values
- Implement proper access controls
- Validate configuration values

---

## 10. Common Patterns

### 10.1. Environment Detection
```php
if ($appConfig->getIsEnvironment("production")) {
    // Production-specific configuration
}
```

### 10.2. URL Generation
```php
$baseURL = $appConfig->getBaseURL();
$apiURL = $appConfig->getAPIURL();
```

### 10.3. Database Connection
```php
$connection = \Katu\PDO\Connection::getInstance("app");
```

### 10.4. Environment Variables
```php
$envConfig = \App\App::getEnvConfig();
$value = $envConfig->getVariable("SOME_VARIABLE");
```

---

## 11. Troubleshooting

### 11.1. Common Issues
- **Missing Environment Files:** Check `.env` file existence and permissions
- **Database Connection Errors:** Verify connection configuration and credentials
- **Configuration Not Found:** Ensure proper class extension and method implementation
- **Environment Variable Issues:** Check variable names and loading

### 11.2. Debugging
- Use `getVariables()` to inspect loaded environment variables
- Check database connection configuration with `getPDODSN()`
- Verify configuration class inheritance
- Use logging for configuration debugging

---

This documentation provides comprehensive coverage of the KATU Configuration system. For specific implementation details, refer to the individual configuration classes in `src/Config/` and the application-specific implementations.
