# Controller System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Controller system in the KATU framework. The controller system provides the foundation for handling HTTP requests, form processing, error management, and view data preparation in the MVC architecture.

---

## 1. System Overview

### 1.1. Purpose
- **HTTP Request Handling:** Process and validate HTTP requests
- **Form Processing:** Handle form submissions with CSRF protection
- **Error Management:** Centralized error collection and handling
- **View Data Preparation:** Prepare data for template rendering
- **Exception Handling:** Manage and propagate exceptions
- **Security:** CSRF token validation and form security
- **Response Generation:** Generate appropriate HTTP responses

### 1.2. Architecture
- **Base Controller:** `Controller` - Core controller functionality
- **Preset Controllers:** Specialized controllers for common tasks
- **Form Handling:** CSRF token validation and form processing
- **Error System:** Error collection and management
- **Exception System:** Exception handling and propagation
- **View Integration:** Template data preparation

---

## 2. Core Controller Classes

### 2.1. Controller (`Katu\Controllers\Controller`)
**Location:** `Controller.php`

Base controller class with common functionality:

```php
// Key methods:
public function isSubmitted(ServerRequestInterface $request, ?string $name = null)
public function isSubmittedWithToken(ServerRequestInterface $request, ?string $name = null)
public function isSubmittedByHuman(ServerRequestInterface $request, ?string $name = null)
public function getViewData(array $data = []): array
public function getErrors(): \Katu\Errors\ErrorCollection
public function addError(\Katu\Errors\Error $error): Controller
public function addErrors(\Katu\Errors\ErrorCollection $errors): Controller
public function hasErrors(): bool
public function getExceptions(): \Katu\Exceptions\ExceptionCollection
public function addExceptions(\Katu\Exceptions\Exception $e): Controller
public function hasExceptions(): bool
```

**Key Features:**
- Form submission detection
- CSRF token validation
- Error handling and collection
- View data preparation
- Exception management
- Human vs. bot detection

---

## 3. Form Processing

### 3.1. Form Submission Detection
```php
// Basic form submission check
if ($this->isSubmitted($request, "myForm")) {
    // Handle form submission
}

// Form submission with CSRF token validation
if ($this->isSubmittedWithToken($request, "myForm")) {
    // Handle secure form submission
}

// Human submission detection
if ($this->isSubmittedByHuman($request, "myForm")) {
    // Handle human form submission
}
```

### 3.2. CSRF Protection
The controller system includes built-in CSRF protection:

```php
// Validate CSRF token
if (\Katu\Tools\Forms\Token::validate($request->getParsedBody()["formToken"] ?? null)) {
    // Token is valid
}
```

**Key Features:**
- Automatic CSRF token validation
- Form name verification
- Human vs. bot detection
- Secure form processing

---

## 4. Error Management

### 4.1. Error Collection
```php
// Get error collection
$errors = $this->getErrors();

// Add single error
$this->addError(new \Katu\Errors\Error("Field is required"));

// Add multiple errors
$this->addErrors($errorCollection);

// Check for errors
if ($this->hasErrors()) {
    // Handle errors
}
```

### 4.2. Error Handling Patterns
```php
public function processForm(ServerRequestInterface $request)
{
    if (!$this->isSubmittedWithToken($request, "myForm")) {
        return $this->getViewResponse("form", $this->getViewData());
    }

    // Validate input
    if (empty($request->getParsedBody()["name"])) {
        $this->addError(new \Katu\Errors\Error("Name is required"));
    }

    if ($this->hasErrors()) {
        return $this->getViewResponse("form", $this->getViewData());
    }

    // Process form data
    // ...
}
```

---

## 5. Exception Management

### 5.1. Exception Collection
```php
// Get exception collection
$exceptions = $this->getExceptions();

// Add exception
$this->addExceptions($exception);

// Check for exceptions
if ($this->hasExceptions()) {
    // Handle exceptions
}
```

### 5.2. Exception Handling Patterns
```php
public function processRequest(ServerRequestInterface $request)
{
    try {
        // Process request
        $result = $this->doSomething();
    } catch (\Katu\Exceptions\Exception $e) {
        $this->addExceptions($e);
        return $this->getErrorResponse();
    }

    return $this->getSuccessResponse($result);
}
```

---

## 6. View Data Management

### 6.1. View Data Preparation
```php
// Get view data with errors
$viewData = $this->getViewData([
    "title" => "My Page",
    "data" => $someData
]);

// View data includes:
// - Controller data ($this->data)
// - Errors ($this->getErrors())
// - Additional data passed to method
```

### 6.2. Data Management
```php
// Set controller data
$this->data["user"] = $user;
$this->data["settings"] = $settings;

// Get view data
$viewData = $this->getViewData([
    "pageTitle" => "Dashboard"
]);
```

---

## 7. Preset Controllers

### 7.1. Images (`Katu\Controllers\Presets\Images`)
**Location:** `Presets/Images.php`

Specialized controller for image handling:

```php
// Key methods:
public function getVersion(ServerRequestInterface $request, ResponseInterface $response, string $imagePackage, string $versionCode, string $extension)
```

**Key Features:**
- Image version processing
- Memory limit management
- Cache control headers
- Image package handling
- MIME type handling

### 7.2. Image Processing Example
```php
public function getVersion(ServerRequestInterface $request, ResponseInterface $response, string $imagePackage, string $versionCode, string $extension)
{
    // Set memory limit for image processing
    \Katu\Tools\System\Memory::setLimit(\Katu\Types\TFileSize::createFromShorthand("2G"));

    // Create image from package
    $image = \Katu\Tools\Images\Image::createFromPackage(Package::createFromPortableString($imagePackage));

    // Get image version
    $version = (new ImageConfig)->getVersions()->filterByTitle($versionCode)->getFirst();
    $imageVersion = $image->getImageVersion($version);

    // Set cache headers
    $maxAge = (new ImageConfig)->getCacheTimeout();
    $response = $response->withAddedHeader("Cache-Control", "max-age={$maxAge}");

    // Return image response
    return $response
        ->withHeader("Content-Type", $imageVersion->getFile()->getMime())
        ->withBody(\GuzzleHttp\Psr7\Utils::streamFor($imageVersion->getFile()->get()));
}
```

---

## 8. Controller Patterns

### 8.1. Basic Controller Structure
```php
class ExampleController extends \Katu\Controllers\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        // Process request
        $data = $this->processData($request);

        // Return view response
        return $this->getViewResponse("template", $this->getViewData([
            "data" => $data
        ]));
    }

    private function processData(ServerRequestInterface $request)
    {
        // Process request data
        return $processedData;
    }
}
```

### 8.2. Form Processing Controller
```php
class FormController extends \Katu\Controllers\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isSubmittedWithToken($request, "myForm")) {
            return $this->processForm($request);
        }

        return $this->getViewResponse("form", $this->getViewData());
    }

    private function processForm(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        // Validate data
        if (empty($data["name"])) {
            $this->addError(new \Katu\Errors\Error("Name is required"));
        }

        if ($this->hasErrors()) {
            return $this->getViewResponse("form", $this->getViewData([
                "formData" => $data
            ]));
        }

        // Process form
        $this->saveData($data);

        return $this->getRedirectResponse("/success");
    }
}
```

### 8.3. API Controller
```php
class ApiController extends \Katu\Controllers\Controller
{
    public function getResponse(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $this->processApiRequest($request);
            return $this->getJsonResponse($data);
        } catch (\Katu\Exceptions\Exception $e) {
            $this->addExceptions($e);
            return $this->getJsonErrorResponse();
        }
    }

    private function processApiRequest(ServerRequestInterface $request)
    {
        // Process API request
        return $result;
    }
}
```

---

## 9. Security Features

### 9.1. CSRF Protection
- **Token Validation:** Automatic CSRF token validation
- **Form Security:** Secure form processing
- **Human Detection:** Bot vs. human submission detection

### 9.2. Input Validation
- **Error Collection:** Centralized error management
- **Exception Handling:** Proper exception propagation
- **Data Sanitization:** Input data validation

### 9.3. Security Best Practices
```php
// Always validate CSRF tokens
if (!$this->isSubmittedWithToken($request, $formName)) {
    throw new \Katu\Exceptions\ForbiddenException("Invalid form submission");
}

// Validate all inputs
if (empty($data["requiredField"])) {
    $this->addError(new \Katu\Errors\Error("Required field is missing"));
}

// Check for errors before processing
if ($this->hasErrors()) {
    return $this->getViewResponse("form", $this->getViewData());
}
```

---

## 10. Integration with Other Systems

### 10.1. Model Integration
```php
public function processUser(ServerRequestInterface $request): ResponseInterface
{
    if ($this->isSubmittedWithToken($request, "userForm")) {
        $user = new User();
        $user->name = $request->getParsedBody()["name"];
        $user->email = $request->getParsedBody()["email"];

        try {
            $user->persist();
        } catch (\Katu\Exceptions\Exception $e) {
            $this->addExceptions($e);
        }
    }

    return $this->getViewResponse("user", $this->getViewData());
}
```

### 10.2. View Integration
```php
public function getResponse(ServerRequestInterface $request): ResponseInterface
{
    $data = $this->getViewData([
        "title" => "My Page",
        "users" => User::getAll(),
        "settings" => Setting::getAll()
    ]);

    return $this->getViewResponse("template", $data);
}
```

### 10.3. Error System Integration
```php
public function processData(ServerRequestInterface $request): ResponseInterface
{
    try {
        $result = $this->doSomething();
    } catch (\Katu\Exceptions\ValidationException $e) {
        $this->addErrors($e->getErrors());
    } catch (\Katu\Exceptions\Exception $e) {
        $this->addExceptions($e);
    }

    if ($this->hasErrors() || $this->hasExceptions()) {
        return $this->getErrorResponse();
    }

    return $this->getSuccessResponse($result);
}
```

---

## 11. Best Practices

### 11.1. Controller Development
- Always extend `\Katu\Controllers\Controller`
- Use proper error handling
- Implement CSRF protection
- Validate all inputs
- Use appropriate response types

### 11.2. Form Processing
- Always validate CSRF tokens
- Check for human submissions
- Validate all form data
- Provide meaningful error messages
- Handle errors gracefully

### 11.3. Error Management
- Use error collections for validation errors
- Use exception collections for system errors
- Provide user-friendly error messages
- Log errors appropriately
- Handle errors consistently

### 11.4. Security Considerations
- Always validate CSRF tokens
- Sanitize all inputs
- Use proper authentication
- Implement authorization checks
- Handle sensitive data carefully

---

## 12. Common Patterns

### 12.1. CRUD Operations
```php
class UserController extends \Katu\Controllers\Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = User::getAll();
        return $this->getViewResponse("users/index", $this->getViewData([
            "users" => $users
        ]));
    }

    public function create(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isSubmittedWithToken($request, "userForm")) {
            return $this->processCreate($request);
        }

        return $this->getViewResponse("users/create", $this->getViewData());
    }

    private function processCreate(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        // Validation
        if (empty($data["name"])) {
            $this->addError(new \Katu\Errors\Error("Name is required"));
        }

        if ($this->hasErrors()) {
            return $this->getViewResponse("users/create", $this->getViewData([
                "formData" => $data
            ]));
        }

        // Create user
        $user = new User();
        $user->name = $data["name"];
        $user->email = $data["email"];
        $user->persist();

        return $this->getRedirectResponse("/users");
    }
}
```

### 12.2. API Endpoints
```php
class ApiController extends \Katu\Controllers\Controller
{
    public function getUsers(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $users = User::getAll();
            return $this->getJsonResponse([
                "success" => true,
                "data" => $users
            ]);
        } catch (\Katu\Exceptions\Exception $e) {
            $this->addExceptions($e);
            return $this->getJsonResponse([
                "success" => false,
                "errors" => $this->getExceptions()
            ], 500);
        }
    }
}
```

---

## 13. Troubleshooting

### 13.1. Common Issues
- **CSRF Token Errors:** Check token validation and form setup
- **Form Not Submitting:** Verify form name and method
- **Errors Not Displaying:** Check error collection and view data
- **Exceptions Not Caught:** Verify exception handling

### 13.2. Debugging
- Use `getViewData()` to inspect controller data
- Check error collections with `getErrors()`
- Monitor exception collections with `getExceptions()`
- Use logging for debugging controller flow

### 13.3. Performance Issues
- Minimize data processing in controllers
- Use appropriate caching strategies
- Optimize database queries
- Monitor memory usage for image processing

---

This documentation provides comprehensive coverage of the KATU Controller system. For specific implementation details, refer to the controller classes in `src/Controllers/` and the application-specific controller implementations.
