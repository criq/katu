# Validation System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Validation system in the KATU framework. The validation system provides robust input validation, parameter processing, and error handling capabilities.

---

## 1. System Overview

### 1.1. Purpose
- **Input Validation:** Comprehensive validation of user inputs and data
- **Parameter Processing:** Advanced parameter handling with transformation
- **Error Management:** Detailed error collection and reporting
- **REST Integration:** Built-in REST API response generation
- **Type Safety:** Strong typing and validation rules

### 1.2. Architecture
- **Core Classes:** `Param`, `Validation`, `Validator`, `Rule`
- **Parameter Types:** Specialized parameter classes for different input sources
- **Validation Rules:** Extensible rule system for various validation types
- **Error Handling:** Comprehensive error collection and reporting
- **Package Support:** Full serialization and deserialization capabilities

---

## 2. Core Classes

### 2.1. Param (`Katu\Tools\Validation\Param`)
**Location:** `Param.php`

The central parameter class for input validation and processing:

```php
// Key methods:
public function __construct(?string $key, $input = null)
public function setInput($value): Param
public function getOutput()
public function setOutput($value): Param
public function addAlias(string $alias): Param
public function each(callable $callback): Param
public function forwardInput(): Param
public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
```

**Key Features:**
- Input/output transformation
- Alias support for parameter mapping
- Chainable validation methods
- REST response integration
- Package serialization support
- Display name management

### 2.2. Validation (`Katu\Tools\Validation\Validation`)
**Location:** `Validation.php`

Validation result container with error management:

```php
// Key methods:
public function __construct(?ParamCollection $params = null)
public function addParam(Param $param): Validation
public function addError(\Katu\Errors\Error $error): Validation
public function hasErrors(): bool
public function getErrors(): ErrorCollection
public function getResponse()
public function setResponse($value): Validation
```

**Key Features:**
- Parameter collection management
- Error collection and reporting
- Response data handling
- Array access interface
- Package serialization

### 2.3. Validator (`Katu\Tools\Validation\Validator`)
**Location:** `Validator.php`

Rule-based validation engine:

```php
// Key methods:
public function addRule(Rule $rule): Validator
public function getRules(): array
public function validate(Param $param): Validation
```

**Key Features:**
- Rule-based validation
- Extensible rule system
- Chainable rule addition
- Comprehensive validation execution

### 2.4. Rule (`Katu\Tools\Validation\Rule`)
**Location:** `Rule.php`

Abstract base class for validation rules:

```php
// Key methods:
abstract public function validate(Param $param): Validation
public function getMessage(): string
```

**Key Features:**
- Abstract rule definition
- Customizable error messages
- Consistent validation interface

---

## 3. Built-in Validation Rules

### 3.1. Available Rules (`Rules/`)

#### IsNotEmpty (`IsNotEmpty.php`)
Validates that input is not empty after trimming:

```php
$rule = new IsNotEmpty();
$validation = $rule->validate($param);
```

#### IsInteger (`IsInteger.php`)
Validates that input is an integer:

```php
$rule = new IsInteger();
$validation = $rule->validate($param);
```

#### IsPositiveInt (`IsPositiveInt.php`)
Validates that input is a positive integer:

```php
$rule = new IsPositiveInt();
$validation = $rule->validate($param);
```

#### IsPositiveFloat (`IsPositiveFloat.php`)
Validates that input is a positive float:

```php
$rule = new IsPositiveFloat();
$validation = $rule->validate($param);
```

#### IsOneOf (`IsOneOf.php`)
Validates that input is one of allowed values:

```php
$rule = new IsOneOf(['option1', 'option2', 'option3']);
$validation = $rule->validate($param);
```

#### IsTruthy (`IsTruthy.php`)
Validates that input is truthy:

```php
$rule = new IsTruthy();
$validation = $rule->validate($param);
```

#### IsDateInPast (`IsDateInPast.php`)
Validates that input is a date in the past:

```php
$rule = new IsDateInPast();
$validation = $rule->validate($param);
```

---

## 4. Specialized Parameter Types

### 4.1. Parameter Classes (`Params/`)

#### UserInput (`UserInput.php`)
Specialized parameter for user input validation:

```php
$param = new UserInput("email", $request->getParsedBody()["email"]);
```

#### RequestParam (`RequestParam.php`)
Base class for HTTP request parameters:

```php
$param = new RequestParam("username", $request->getParsedBody()["username"]);
```

#### ObjectId (`ObjectId.php`)
Parameter for object ID validation:

```php
$param = new ObjectId("userId", $request->getParsedBody()["userId"]);
```

#### ObjectProperty (`ObjectProperty.php`)
Parameter for object property validation:

```php
$param = new ObjectProperty("user", "name", $user->name);
```

#### ObjectSelf (`ObjectSelf.php`)
Parameter for self-referencing validation:

```php
$param = new ObjectSelf("id", $object->id);
```

#### GeneratedParam (`GeneratedParam.php`)
Parameter for auto-generated values:

```php
$param = new GeneratedParam("token", generateToken());
```

---

## 5. Usage Patterns

### 5.1. Basic Validation
```php
// Create parameter
$param = new Param("email", $request->getParsedBody()["email"]);

// Add validation rules
$validator = new Validator();
$validator->addRule(new IsNotEmpty());
$validator->addRule(new IsEmail());

// Validate
$validation = $validator->validate($param);

// Check results
if ($validation->hasErrors()) {
    return $validation->getErrors()->getRestResponse($request);
}

$email = $validation->getResponse();
```

### 5.2. Parameter Transformation
```php
$param = new Param("name", $input["name"]);
$param->forwardInput(); // Copy input to output
$param->each(function($value) {
    return trim($value);
});
```

### 5.3. Alias Support
```php
$param = new Param("user_email", $input["email"]);
$param->addAlias("email");
$param->addAlias("userEmail");
```

### 5.4. REST API Integration
```php
$param = new Param("id", $request->getParsedBody()["id"]);
$validator = new Validator();
$validator->addRule(new IsInteger());

$validation = $validator->validate($param);

if ($validation->hasErrors()) {
    return $response->withBody(
        $validation->getErrors()->getRestResponse($request)->getStream()
    );
}
```

### 5.5. Complex Validation Chain
```php
$validation = new Validation();

// Validate email
$emailParam = new UserInput("email", $input["email"]);
$emailValidator = new Validator();
$emailValidator->addRule(new IsNotEmpty());
$emailValidator->addRule(new IsEmail());
$emailValidation = $emailValidator->validate($emailParam);

if ($emailValidation->hasErrors()) {
    $validation->addErrors($emailValidation->getErrors());
} else {
    $validation->addParam($emailValidation->getParams()[0]);
}

// Validate password
$passwordParam = new UserInput("password", $input["password"]);
$passwordValidator = new Validator();
$passwordValidator->addRule(new IsNotEmpty());
$passwordValidator->addRule(new IsMinLength(8));
$passwordValidation = $passwordValidator->validate($passwordParam);

if ($passwordValidation->hasErrors()) {
    $validation->addErrors($passwordValidation->getErrors());
} else {
    $validation->addParam($passwordValidation->getParams()[0]);
}

return $validation;
```

---

## 6. Error Handling

### 6.1. Error Collection
```php
$validation = new Validation();

if ($validation->hasErrors()) {
    $errors = $validation->getErrors();

    // Get all error messages
    foreach ($errors as $error) {
        echo $error->getMessage();
    }

    // Get REST response
    $restResponse = $errors->getRestResponse($request);
}
```

### 6.2. Custom Error Messages
```php
class CustomRule extends Rule
{
    public function validate(Param $param): Validation
    {
        $validation = new Validation();

        if (!$this->isValid($param->getInput())) {
            $error = new Error("Custom validation failed");
            $error->addParam($param);
            $validation->addError($error);
        }

        return $validation;
    }
}
```

---

## 7. Advanced Features

### 7.1. Package Serialization
```php
$param = new Param("test", "value");
$package = $param->getPackage();

// Deserialize
$restoredParam = Param::createFromPackage($package);
```

### 7.2. Collection Management
```php
$params = new ParamCollection();
$params[] = new Param("field1", "value1");
$params[] = new Param("field2", "value2");

$validation = new Validation($params);
```

### 7.3. REST Response Generation
```php
$param = new Param("data", $input);
$restResponse = $param->getRestResponse($request);
$stream = $restResponse->getStream();
```

---

## 8. Best Practices

### 8.1. Parameter Naming
- Use descriptive parameter names
- Follow consistent naming conventions
- Use aliases for multiple naming patterns

### 8.2. Validation Rules
- Create specific rules for business logic
- Reuse common rules across the application
- Provide meaningful error messages

### 8.3. Error Handling
- Always check for validation errors
- Provide user-friendly error messages
- Use appropriate HTTP status codes

### 8.4. Performance
- Validate early in the request lifecycle
- Cache validation results when appropriate
- Use appropriate validation rules for the context

---

## 9. Integration Points

### 9.1. Controller Integration
```php
class UserController extends Controller
{
    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        $validation = $this->validateUserInput($request);

        if ($validation->hasErrors()) {
            return $this->errorResponse($validation->getErrors());
        }

        $userData = $validation->getResponse();
        // Process user creation
    }
}
```

### 9.2. Model Integration
```php
class User extends Model
{
    public function validateInput(array $input): Validation
    {
        $validation = new Validation();

        // Validate name
        $nameParam = new UserInput("name", $input["name"]);
        $nameValidation = (new Validator())
            ->addRule(new IsNotEmpty())
            ->validate($nameParam);

        if ($nameValidation->hasErrors()) {
            $validation->addErrors($nameValidation->getErrors());
        } else {
            $validation->addParam($nameValidation->getParams()[0]);
        }

        return $validation;
    }
}
```

---

## 10. Troubleshooting

### 10.1. Common Issues
- **Empty Validation Results:** Check if rules are properly added to validator
- **Parameter Not Found:** Verify parameter key matches input data
- **Error Messages Missing:** Ensure rules have proper error message methods
- **REST Response Issues:** Check if request object is properly passed

### 10.2. Debugging
- Use `var_dump()` on validation objects to inspect state
- Check error collection for detailed error information
- Verify parameter input/output values
- Test individual rules in isolation

---

This documentation provides comprehensive coverage of the Validation system. For specific implementation details, refer to the source code in `src/Tools/Validation/`.
