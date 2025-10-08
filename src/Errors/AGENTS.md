# Error System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Error system in the KATU framework. The error system provides structured error handling, internationalization support, parameter binding, and REST API integration for comprehensive error management.

---

## 1. System Overview

### 1.1. Purpose
- **Structured Error Handling:** Comprehensive error representation with metadata
- **Internationalization:** Multi-language error message support
- **Parameter Binding:** Error context with parameter information
- **REST Integration:** Built-in REST API response generation
- **Error Classification:** Categorized errors with codes and help information
- **Collection Management:** Error collections for handling multiple errors
- **Package Serialization:** Full serialization support for error transmission

### 1.2. Architecture
- **Core Classes:** `Error`, `ErrorCollection` - Main error handling classes
- **Internationalization:** Multi-language error message support
- **Parameter System:** Integration with validation parameter system
- **REST Integration:** Automatic REST response generation
- **Package System:** Serialization and deserialization support
- **Code System:** Error code management and classification

---

## 2. Core Error Classes

### 2.1. Error (`Katu\Errors\Error`)
**Location:** `Error.php`

Main error class with comprehensive functionality:

```php
// Key methods:
public function __construct(?string $message = null, $code = null, ?array $versions = [])
public function setMessage(?string $value): Error
public function getMessage(): ?string
public function getMessageWithoutPeriod(): ?string
public function setCode($code): Error
public function getCode(): ?\Katu\Tools\Strings\Code
public function setVersions(?array $value): Error
public function addVersion(string $locale, string $message): Error
public function getVersions(): array
public function setHelp(?string $value): Error
public function getHelp(): ?string
public function setOptions(?array $value): Error
public function getOptions(): ?array
public function setParams(\Katu\Tools\Validation\ParamCollection $params): Error
public function getParams(): \Katu\Tools\Validation\ParamCollection
public function addParam(\Katu\Tools\Validation\Param $param): Error
public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
```

**Key Features:**
- Structured error messages
- Error code management
- Multi-language support
- Help text and options
- Parameter binding
- REST API integration
- Package serialization

### 2.2. ErrorCollection (`Katu\Errors\ErrorCollection`)
**Location:** `ErrorCollection.php`

Collection for managing multiple errors:

```php
// Key methods:
public function addError(Error $error): ErrorCollection
public function addErrors(ErrorCollection $errors): ErrorCollection
public function addValidationResults(array $validationResults): ErrorCollection
public function getTotal(): int
public function hasErrors(): bool
public function isEmpty(): bool
public function getParams(): \Katu\Tools\Validation\ParamCollection
public function filterWithParamKey(string $key): ErrorCollection
public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
```

**Key Features:**
- Error aggregation
- Collection management
- Parameter collection
- Error filtering
- REST response generation
- Package serialization

---

## 3. Error Creation and Management

### 3.1. Basic Error Creation
```php
// Simple error
$error = new Error("Field is required");

// Error with code
$error = new Error("Invalid email format", "INVALID_EMAIL");

// Error with internationalization
$error = new Error("Field is required", "REQUIRED_FIELD", [
    "en" => "Field is required",
    "es" => "El campo es obligatorio",
    "fr" => "Le champ est obligatoire"
]);
```

### 3.2. Error with Help and Options
```php
$error = new Error("Password must be at least 8 characters", "PASSWORD_TOO_SHORT");
$error->setHelp("Please choose a password with at least 8 characters including letters and numbers");
$error->setOptions([
    "minLength" => 8,
    "requireNumbers" => true,
    "requireSpecialChars" => false
]);
```

### 3.3. Error with Parameters
```php
$error = new Error("Invalid value for field", "INVALID_FIELD_VALUE");
$error->addParam(new \Katu\Tools\Validation\Param("fieldName", "email"));
$error->addParam(new \Katu\Tools\Validation\Param("currentValue", $input["email"]));
$error->addParam(new \Katu\Tools\Validation\Param("expectedFormat", "user@domain.com"));
```

---

## 4. Error Collections

### 4.1. Basic Collection Operations
```php
// Create error collection
$errors = new ErrorCollection();

// Add single error
$errors->addError(new Error("Name is required"));

// Add multiple errors
$errors->addErrors($otherErrorCollection);

// Add validation results
$errors->addValidationResults($validationResults);
```

### 4.2. Collection Management
```php
// Check for errors
if ($errors->hasErrors()) {
    // Handle errors
}

// Get error count
$count = $errors->getTotal();

// Check if empty
if ($errors->isEmpty()) {
    // No errors
}

// Filter errors by parameter key
$fieldErrors = $errors->filterWithParamKey("fieldName");
```

### 4.3. Parameter Collection
```php
// Get all parameters from errors
$allParams = $errors->getParams();

// Access specific parameter
$fieldParam = $allParams->get("fieldName");
```

---

## 5. Internationalization Support

### 5.1. Multi-language Error Messages
```php
$error = new Error("Field is required", "REQUIRED_FIELD", [
    "en" => "Field is required",
    "es" => "El campo es obligatorio",
    "fr" => "Le champ est obligatoire",
    "de" => "Das Feld ist erforderlich"
]);

// Add additional language
$error->addVersion("it", "Il campo è obbligatorio");
```

### 5.2. Language-specific Error Retrieval
```php
// Get all versions
$versions = $error->getVersions();

// Get specific language (if available)
$englishMessage = $versions["en"] ?? $error->getMessage();
$spanishMessage = $versions["es"] ?? $error->getMessage();
```

---

## 6. REST API Integration

### 6.1. Single Error REST Response
```php
$error = new Error("Invalid email format", "INVALID_EMAIL");
$error->setHelp("Please enter a valid email address");
$error->addParam(new \Katu\Tools\Validation\Param("field", "email"));

$restResponse = $error->getRestResponse($request);
```

### 6.2. Error Collection REST Response
```php
$errors = new ErrorCollection();
$errors->addError($error1);
$errors->addError($error2);

$restResponse = $errors->getRestResponse($request);
```

### 6.3. REST Response Structure
```json
{
    "message": "Invalid email format",
    "code": "INVALID_EMAIL",
    "versions": {
        "en": "Invalid email format",
        "es": "Formato de email inválido"
    },
    "help": "Please enter a valid email address",
    "options": {
        "minLength": 8
    },
    "params": [
        {
            "key": "field",
            "value": "email"
        }
    ]
}
```

---

## 7. Package Serialization

### 7.1. Error Serialization
```php
$error = new Error("Field is required", "REQUIRED_FIELD");
$package = $error->getPackage();

// Serialize to string
$serialized = $package->getPayload();
```

### 7.2. Error Deserialization
```php
// Create from package
$restoredError = Error::createFromPackage($package);
```

### 7.3. Collection Serialization
```php
$errors = new ErrorCollection();
$errors->addError($error1);
$errors->addError($error2);

$package = $errors->getPackage();

// Deserialize collection
$restoredErrors = ErrorCollection::createFromPackage($package);
```

---

## 8. Error Patterns

### 8.1. Validation Errors
```php
public function validateUserInput(array $input): ErrorCollection
{
    $errors = new ErrorCollection();

    // Validate name
    if (empty($input["name"])) {
        $error = new Error("Name is required", "REQUIRED_NAME");
        $error->addParam(new \Katu\Tools\Validation\Param("field", "name"));
        $errors->addError($error);
    }

    // Validate email
    if (empty($input["email"])) {
        $error = new Error("Email is required", "REQUIRED_EMAIL");
        $error->addParam(new \Katu\Tools\Validation\Param("field", "email"));
        $errors->addError($error);
    } elseif (!filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
        $error = new Error("Invalid email format", "INVALID_EMAIL");
        $error->setHelp("Please enter a valid email address");
        $error->addParam(new \Katu\Tools\Validation\Param("field", "email"));
        $error->addParam(new \Katu\Tools\Validation\Param("value", $input["email"]));
        $errors->addError($error);
    }

    return $errors;
}
```

### 8.2. Business Logic Errors
```php
public function processOrder(array $orderData): ErrorCollection
{
    $errors = new ErrorCollection();

    // Check inventory
    if (!$this->hasInventory($orderData["productId"], $orderData["quantity"])) {
        $error = new Error("Insufficient inventory", "INSUFFICIENT_INVENTORY");
        $error->setHelp("Please reduce the quantity or choose a different product");
        $error->addParam(new \Katu\Tools\Validation\Param("productId", $orderData["productId"]));
        $error->addParam(new \Katu\Tools\Validation\Param("requestedQuantity", $orderData["quantity"]));
        $error->addParam(new \Katu\Tools\Validation\Param("availableQuantity", $this->getAvailableQuantity($orderData["productId"])));
        $errors->addError($error);
    }

    // Check payment method
    if (!$this->isValidPaymentMethod($orderData["paymentMethod"])) {
        $error = new Error("Invalid payment method", "INVALID_PAYMENT_METHOD");
        $error->setHelp("Please choose a valid payment method");
        $error->addParam(new \Katu\Tools\Validation\Param("paymentMethod", $orderData["paymentMethod"]));
        $error->setOptions([
            "validMethods" => $this->getValidPaymentMethods()
        ]);
        $errors->addError($error);
    }

    return $errors;
}
```

### 8.3. System Errors
```php
public function handleSystemError(\Throwable $exception): Error
{
    $error = new Error("An unexpected error occurred", "SYSTEM_ERROR");
    $error->setHelp("Please try again later or contact support if the problem persists");
    $error->setOptions([
        "errorId" => uniqid(),
        "timestamp" => time(),
        "severity" => "high"
    ]);

    // Add context parameters
    $error->addParam(new \Katu\Tools\Validation\Param("exception", get_class($exception)));
    $error->addParam(new \Katu\Tools\Validation\Param("message", $exception->getMessage()));

    return $error;
}
```

---

## 9. Integration with Validation System

### 9.1. Validation Error Integration
```php
public function validateForm(array $formData): ErrorCollection
{
    $errors = new ErrorCollection();

    // Validate email
    $emailParam = new \Katu\Tools\Validation\Param("email", $formData["email"]);
    $emailValidation = (new \Katu\Tools\Validation\Validator())
        ->addRule(new \Katu\Tools\Validation\Rules\IsNotEmpty())
        ->addRule(new \Katu\Tools\Validation\Rules\IsEmail())
        ->validate($emailParam);

    if ($emailValidation->hasErrors()) {
        $errors->addErrors($emailValidation->getErrors());
    }

    return $errors;
}
```

### 9.2. Parameter Integration
```php
public function createErrorWithValidationParam(\Katu\Tools\Validation\Param $param, string $message, string $code): Error
{
    $error = new Error($message, $code);
    $error->addParam($param);

    // Add additional context
    $error->addParam(new \Katu\Tools\Validation\Param("inputValue", $param->getInput()));
    $error->addParam(new \Katu\Tools\Validation\Param("fieldName", $param->getKey()));

    return $error;
}
```

---

## 10. Best Practices

### 10.1. Error Message Design
- Use clear, actionable error messages
- Provide helpful context information
- Include relevant parameter data
- Use consistent error codes
- Provide multi-language support

### 10.2. Error Classification
- Use meaningful error codes
- Categorize errors by type (validation, business, system)
- Include severity levels when appropriate
- Provide help text for complex errors

### 10.3. Parameter Management
- Include relevant context parameters
- Use consistent parameter naming
- Provide both input and expected values
- Include field names and identifiers

### 10.4. Internationalization
- Provide translations for all user-facing errors
- Use consistent terminology across languages
- Include cultural context when appropriate
- Test error messages in target languages

---

## 11. Common Patterns

### 11.1. Form Validation Errors
```php
public function validateRegistrationForm(array $data): ErrorCollection
{
    $errors = new ErrorCollection();

    // Name validation
    if (empty($data["name"])) {
        $error = new Error("Name is required", "REQUIRED_NAME", [
            "en" => "Name is required",
            "es" => "El nombre es obligatorio"
        ]);
        $error->addParam(new \Katu\Tools\Validation\Param("field", "name"));
        $errors->addError($error);
    }

    // Email validation
    if (empty($data["email"])) {
        $error = new Error("Email is required", "REQUIRED_EMAIL");
        $error->addParam(new \Katu\Tools\Validation\Param("field", "email"));
        $errors->addError($error);
    } elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
        $error = new Error("Invalid email format", "INVALID_EMAIL");
        $error->setHelp("Please enter a valid email address");
        $error->addParam(new \Katu\Tools\Validation\Param("field", "email"));
        $error->addParam(new \Katu\Tools\Validation\Param("value", $data["email"]));
        $errors->addError($error);
    }

    return $errors;
}
```

### 11.2. API Error Responses
```php
public function handleApiError(ErrorCollection $errors, ServerRequestInterface $request): ResponseInterface
{
    if ($errors->hasErrors()) {
        $restResponse = $errors->getRestResponse($request);

        return $response
            ->withStatus(400)
            ->withHeader("Content-Type", "application/json")
            ->withBody($restResponse->getStream());
    }

    return $response->withStatus(200);
}
```

### 11.3. Error Logging
```php
public function logError(Error $error, array $context = []): void
{
    $logData = [
        "message" => $error->getMessage(),
        "code" => (string)$error->getCode(),
        "params" => $error->getParams()->getArrayCopy(),
        "context" => $context
    ];

    \App\App::getLogger()->error("Application error", $logData);
}
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Missing Error Messages:** Check if message is set in constructor
- **Parameter Not Found:** Verify parameter key matches error context
- **REST Response Issues:** Ensure request object is properly passed
- **Serialization Problems:** Check package structure and class names

### 12.2. Debugging
- Use `var_dump()` on error objects to inspect state
- Check error collection for detailed error information
- Verify parameter values and context
- Test REST response generation

### 12.3. Performance Considerations
- Avoid creating unnecessary error objects
- Use error collections efficiently
- Minimize parameter data size
- Cache error messages when appropriate

---

This documentation provides comprehensive coverage of the KATU Error system. For specific implementation details, refer to the error classes in `src/Errors/` and the integration with the validation system.
