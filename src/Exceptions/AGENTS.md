# Exception System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Exception system in the KATU framework. The exception system provides a comprehensive hierarchy of exceptions for error handling, HTTP status codes, REST API integration, and context management.

---

## 1. System Overview

### 1.1. Purpose
- **Error Handling:** Comprehensive exception hierarchy for different error types
- **HTTP Integration:** HTTP status code mapping for REST API responses
- **REST API Support:** Built-in REST response generation for exceptions
- **Context Management:** Rich context information for debugging
- **Error Classification:** Categorized exceptions for different error scenarios
- **Collection Management:** Exception collections for handling multiple errors
- **Abbreviation System:** Short error codes for client-side handling

### 1.2. Architecture
- **Base Exception:** `Exception` - Core exception functionality
- **Exception Hierarchy:** 20+ specialized exception classes
- **Collection System:** `ExceptionCollection` for multiple exceptions
- **HTTP Integration:** HTTP status code mapping
- **REST Integration:** Automatic REST response generation
- **Context System:** Rich debugging information

---

## 2. Core Exception Classes

### 2.1. Exception (`Katu\Exceptions\Exception`)
**Location:** `Exception.php`

Base exception class with advanced features:

```php
// Key methods:
public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
public function getHttpCode(): int
public function setAbbr(string $abbr): Exception
public function getAbbr(): ?string
public function addErrorName(string $errorName): Exception
public function getErrorNames(): array
public function setContext(?array $context): Exception
public function getContext(): ?array
public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
```

**Key Features:**
- HTTP status code mapping (default: 400)
- Error abbreviation system
- Error name management
- Context information storage
- REST API response generation
- PSR-7 request integration

### 2.2. ExceptionCollection (`Katu\Exceptions\ExceptionCollection`)
**Location:** `ExceptionCollection.php`

Collection for managing multiple exceptions:

```php
// Key methods:
public function addException(\Exception $exception): ExceptionCollection
public function add(): ExceptionCollection
public function hasExceptions(): bool
public function countExceptions(): int
public function getErrorNames(): array
public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
```

**Key Features:**
- ArrayAccess, Iterator, Countable interfaces
- Exception aggregation
- Error name collection
- REST response generation
- Collection management

---

## 3. Exception Hierarchy

### 3.1. HTTP Status Exceptions

#### NotFoundException (`Katu\Exceptions\NotFoundException`)
**Location:** `NotFoundException.php`
- **HTTP Code:** 404
- **Purpose:** Resource not found errors

#### UnauthorizedException (`Katu\Exceptions\UnauthorizedException`)
**Location:** `UnauthorizedException.php`
- **HTTP Code:** 401
- **Purpose:** Authentication required errors

#### ForbiddenException (`Katu\Exceptions\ForbiddenException`)
**Location:** `ForbiddenException.php`
- **HTTP Code:** 403
- **Purpose:** Access denied errors

### 3.2. Database Exceptions

#### DatabaseConnectionException (`Katu\Exceptions\DatabaseConnectionException`)
**Location:** `DatabaseConnectionException.php`
- **Purpose:** Database connection failures
- **Inherits:** `ErrorException`

#### PDOConfigException (`Katu\Exceptions\PDOConfigException`)
**Location:** `PDOConfigException.php`
- **Purpose:** PDO configuration errors

#### NoPrimaryKeyReturnedException (`Katu\Exceptions\NoPrimaryKeyReturnedException`)
**Location:** `NoPrimaryKeyReturnedException.php`
- **Purpose:** Database insert failures

### 3.3. Model Exceptions

#### ModelNotFoundException (`Katu\Exceptions\ModelNotFoundException`)
**Location:** `ModelNotFoundException.php`
- **Purpose:** Model not found errors

#### MissingUserException (`Katu\Exceptions\MissingUserException`)
**Location:** `MissingUserException.php`
- **Purpose:** User not found errors

#### MissingUserServiceException (`Katu\Exceptions\MissingUserServiceException`)
**Location:** `MissingUserServiceException.php`
- **Purpose:** User service not found errors

### 3.4. Input and Validation Exceptions

#### InputErrorException (`Katu\Exceptions\InputErrorException`)
**Location:** `InputErrorException.php`
- **Purpose:** Input validation errors

#### MissingArrayKeyException (`Katu\Exceptions\MissingArrayKeyException`)
**Location:** `MissingArrayKeyException.php`
- **Purpose:** Missing array key errors

#### UserErrorException (`Katu\Exceptions\UserErrorException`)
**Location:** `UserErrorException.php`
- **Purpose:** User-related errors

### 3.5. System Exceptions

#### LoadAverageExceededException (`Katu\Exceptions\LoadAverageExceededException`)
**Location:** `LoadAverageExceededException.php`
- **Purpose:** System load too high

#### MaintenanceModeException (`Katu\Exceptions\MaintenanceModeException`)
**Location:** `MaintenanceModeException.php`
- **Purpose:** System maintenance mode

#### LockException (`Katu\Exceptions\LockException`)
**Location:** `LockException.php`
- **Purpose:** Lock acquisition failures

### 3.6. File and Resource Exceptions

#### FileNotFoundException (`Katu\Exceptions\FileNotFoundException`)
**Location:** `FileNotFoundException.php`
- **Purpose:** File not found errors

#### ImageErrorException (`Katu\Exceptions\ImageErrorException`)
**Location:** `ImageErrorException.php`
- **Purpose:** Image processing errors

### 3.7. Configuration Exceptions

#### MissingConfigException (`Katu\Exceptions\MissingConfigException`)
**Location:** `MissingConfigException.php`
- **Purpose:** Configuration not found errors

#### MissingSettingException (`Katu\Exceptions\MissingSettingException`)
**Location:** `MissingSettingException.php`
- **Purpose:** Setting not found errors

### 3.8. Authentication and Authorization Exceptions

#### InvalidAccessTokenException (`Katu\Exceptions\InvalidAccessTokenException`)
**Location:** `InvalidAccessToken.php`
- **Purpose:** Invalid access token errors

### 3.9. Routing and Controller Exceptions

#### RouteException (`Katu\Exceptions\RouteException`)
**Location:** `RouteException.php`
- **Purpose:** Routing errors

#### ControllerMethodNotFoundException (`Katu\Exceptions\ControllerMethodNotFoundException`)
**Location:** `ControllerMethodNotFoundException.php`
- **Purpose:** Controller method not found errors

### 3.10. Template and View Exceptions

#### TemplateException (`Katu\Exceptions\TemplateException`)
**Location:** `TemplateException.php`
- **Purpose:** Template rendering errors

### 3.11. Specialized Exceptions

#### ErrorException (`Katu\Exceptions\ErrorException`)
**Location:** `ErrorException.php`
- **Purpose:** General error exceptions

#### DoNotCacheException (`Katu\Exceptions\DoNotCacheException`)
**Location:** `DoNotCacheException.php`
- **Purpose:** Cache prevention exceptions

#### CacheCallbackException (`Katu\Exceptions\CacheCallbackException`)
**Location:** `CacheCallbackException.php`
- **Purpose:** Cache callback errors

#### RedirectException (`Katu\Exceptions\RedirectException`)
**Location:** `RedirectException.php`
- **Purpose:** Redirect handling

#### PdoExpressionErorException (`Katu\Exceptions\PdoExpressionErorException`)
**Location:** `PdoExpressionErorException.php`
- **Purpose:** PDO expression errors

---

## 4. Exception Usage Patterns

### 4.1. Basic Exception Throwing
```php
// Simple exception
throw new \Katu\Exceptions\NotFoundException("User not found");

// Exception with abbreviation
$exception = new \Katu\Exceptions\InputErrorException("Invalid input");
$exception->setAbbr("invalidInput");

// Exception with context
$exception = new \Katu\Exceptions\DatabaseConnectionException("Connection failed");
$exception->setContext([
    "host" => "localhost",
    "database" => "app",
    "error" => $pdoError
]);
```

### 4.2. Exception Collections
```php
// Create exception collection
$exceptions = new \Katu\Exceptions\ExceptionCollection();

// Add exceptions
$exceptions->add(
    new \Katu\Exceptions\InputErrorException("Name is required"),
    new \Katu\Exceptions\InputErrorException("Email is required")
);

// Check for exceptions
if ($exceptions->hasExceptions()) {
    // Handle exceptions
}
```

### 4.3. REST API Integration
```php
// Exception with REST response
$exception = new \Katu\Exceptions\ValidationException("Validation failed");
$exception->setAbbr("validationFailed");
$exception->addErrorName("field.name");

// Get REST response
$response = $exception->getRestResponse($request);
```

---

## 5. HTTP Status Code Mapping

### 5.1. Standard HTTP Codes
- **400 Bad Request:** Default for `Exception`
- **401 Unauthorized:** `UnauthorizedException`
- **403 Forbidden:** `ForbiddenException`
- **404 Not Found:** `NotFoundException`

### 5.2. Custom HTTP Codes
```php
class CustomException extends \Katu\Exceptions\Exception
{
    const HTTP_CODE = 422; // Unprocessable Entity
}
```

---

## 6. Error Abbreviation System

### 6.1. Setting Abbreviations
```php
$exception = new \Katu\Exceptions\InputErrorException("Invalid email format");
$exception->setAbbr("invalidEmail");
```

### 6.2. Error Name Management
```php
// Add error names
$exception->addErrorName("user.email");
$exception->addErrorName("user.password");

// Replace error names
$exception->replaceErrorName("user.email", "email");

// Get error names
$errorNames = $exception->getErrorNames();
```

---

## 7. Context Management

### 7.1. Setting Context
```php
$exception = new \Katu\Exceptions\DatabaseConnectionException("Connection failed");
$exception->setContext([
    "host" => "localhost",
    "port" => 3306,
    "database" => "app",
    "user" => "root",
    "error" => $pdoError,
    "timestamp" => time()
]);
```

### 7.2. Retrieving Context
```php
$context = $exception->getContext();
if ($context) {
    $host = $context["host"];
    $error = $context["error"];
}
```

---

## 8. REST API Integration

### 8.1. REST Response Generation
```php
// Single exception
$exception = new \Katu\Exceptions\ValidationException("Validation failed");
$response = $exception->getRestResponse($request);

// Exception collection
$exceptions = new \Katu\Exceptions\ExceptionCollection();
$exceptions->add($exception1, $exception2);
$response = $exceptions->getRestResponse($request);
```

### 8.2. REST Response Structure
```json
{
    "message": "Validation failed",
    "abbr": "validationFailed",
    "names": ["user.email", "user.password"]
}
```

---

## 9. Exception Handling Patterns

### 9.1. Try-Catch Blocks
```php
try {
    $user = User::get($id);
    if (!$user) {
        throw new \Katu\Exceptions\ModelNotFoundException("User not found");
    }
} catch (\Katu\Exceptions\ModelNotFoundException $e) {
    $e->setAbbr("userNotFound");
    throw $e;
} catch (\Katu\Exceptions\Exception $e) {
    $e->setContext(["userId" => $id]);
    throw $e;
}
```

### 9.2. Exception Collection Handling
```php
$exceptions = new \Katu\Exceptions\ExceptionCollection();

try {
    $this->validateInput($data);
} catch (\Katu\Exceptions\InputErrorException $e) {
    $exceptions->add($e);
}

try {
    $this->processData($data);
} catch (\Katu\Exceptions\ProcessingException $e) {
    $exceptions->add($e);
}

if ($exceptions->hasExceptions()) {
    throw $exceptions;
}
```

---

## 10. Best Practices

### 10.1. Exception Selection
- Use specific exceptions for different error types
- Choose appropriate HTTP status codes
- Provide meaningful error messages
- Include relevant context information

### 10.2. Error Handling
- Always catch and handle exceptions appropriately
- Use exception collections for multiple errors
- Provide user-friendly error messages
- Log exceptions for debugging

### 10.3. REST API Integration
- Use abbreviations for client-side error handling
- Include error names for field-specific errors
- Provide consistent error response structure
- Use appropriate HTTP status codes

### 10.4. Context Management
- Include relevant debugging information
- Avoid sensitive data in context
- Use structured context data
- Provide enough information for debugging

---

## 11. Common Patterns

### 11.1. Validation Exceptions
```php
public function validateUser($data)
{
    $exceptions = new \Katu\Exceptions\ExceptionCollection();

    if (empty($data["name"])) {
        $exceptions->add(new \Katu\Exceptions\InputErrorException("Name is required"));
    }

    if (empty($data["email"])) {
        $exceptions->add(new \Katu\Exceptions\InputErrorException("Email is required"));
    }

    if ($exceptions->hasExceptions()) {
        throw $exceptions;
    }
}
```

### 11.2. Model Exceptions
```php
public function getUser($id)
{
    $user = User::get($id);
    if (!$user) {
        throw (new \Katu\Exceptions\ModelNotFoundException("User not found"))
            ->setAbbr("userNotFound")
            ->setContext(["userId" => $id]);
    }

    return $user;
}
```

### 11.3. Authentication Exceptions
```php
public function authenticate($token)
{
    if (!$token) {
        throw new \Katu\Exceptions\UnauthorizedException("Authentication required");
    }

    $user = $this->validateToken($token);
    if (!$user) {
        throw new \Katu\Exceptions\InvalidAccessTokenException("Invalid access token");
    }

    return $user;
}
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Wrong Exception Type:** Use appropriate exception classes
- **Missing Context:** Include relevant debugging information
- **HTTP Code Issues:** Verify HTTP status code mapping
- **REST Response Issues:** Check abbreviation and error name setup

### 12.2. Debugging
- Use `getContext()` to inspect exception context
- Check `getAbbr()` for error abbreviations
- Monitor `getErrorNames()` for error classification
- Use logging for exception tracking

### 12.3. Performance Considerations
- Avoid creating unnecessary exception objects
- Use exception collections efficiently
- Minimize context data size
- Handle exceptions at appropriate levels

---

This documentation provides comprehensive coverage of the KATU Exception system. For specific implementation details, refer to the individual exception classes in `src/Exceptions/` and the application-specific exception handling.
