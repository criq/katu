# REST API System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the REST API system in the KATU framework. The REST system provides comprehensive API response handling, external API integration, and HTTP client functionality.

---

## 1. System Overview

### 1.1. Purpose
- **API Response Handling:** Structured REST API response generation and management
- **External API Integration:** HTTP client functionality for external API calls
- **Response Serialization:** Automatic object serialization for JSON responses
- **Stream Integration:** PSR-7 stream interface support
- **Type Conversion:** Automatic type conversion for various data types
- **Error Handling:** REST-compatible error response generation
- **Interface Compliance:** PSR-7 and REST interface compliance

### 1.2. Architecture
- **Core Classes:** `RestResponse`, `RestResponseInterface` - Response handling
- **API Classes:** `API`, `Request`, `Response` - External API integration
- **Type Conversion:** Automatic serialization of various data types
- **Stream Support:** PSR-7 stream interface integration
- **JSON Integration:** JSON encoding and formatting
- **HTTP Client:** cURL-based HTTP client functionality

---

## 2. Core REST Classes

### 2.1. RestResponse (`Katu\Tools\Rest\RestResponse`)
**Location:** `RestResponse.php`

Main REST response handling class:

```php
// Key methods:
public function __construct($payload)
public function setPayload($payload): RestResponse
public function getPayload()
public function getResponse()
public function getJSON(): \Katu\Types\TJSON
public function getInlineJSON(): \Katu\Types\TJSON
public function getStream(): \Psr\Http\Message\StreamInterface
```

**Key Features:**
- Payload management and processing
- Automatic type conversion
- JSON encoding and formatting
- PSR-7 stream interface support
- Recursive object serialization
- Built-in type handling for common objects

### 2.2. RestResponseInterface (`Katu\Tools\Rest\RestResponseInterface`)
**Location:** `RestResponseInterface.php`

Interface for REST response generation:

```php
// Key methods:
public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
```

**Key Features:**
- Standardized REST response interface
- Request and options parameter support
- Consistent response generation

---

## 3. External API Integration

### 3.1. API (`Katu\Tools\Rest\API\API`)
**Location:** `API/API.php`

Abstract base class for external API integration:

```php
// Key methods:
abstract public function getBaseURL(): \Katu\Types\TURL
public function setCurl(?\Curl\Curl $curl): API
public function getCurl(): \Curl\Curl
public function generateCurl(): \Curl\Curl
public function createRequest(string $method, string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Request
public function call(string $method, string $endpoint, ?array $params, ?\Curl\Curl $curl = null): Response
public function get(string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Response
public function post(string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null): Response
```

**Key Features:**
- Abstract base for API implementations
- cURL integration
- HTTP method support (GET, POST, etc.)
- Request/response management
- Endpoint and parameter handling

### 3.2. Request (`Katu\Tools\Rest\API\Request`)
**Location:** `API/Request.php`

HTTP request handling for external APIs:

```php
// Key methods:
public function __construct(API $api, string $method, string $endpoint, ?array $params = [], ?\Curl\Curl $curl = null)
public function setAPI(API $api): Request
public function getAPI(): API
public function setMethod(string $method): Request
public function getMethod(): string
public function setEndpoint(string $endpoint): Request
public function getEndpoint(): string
public function setParams(?array $params): Request
public function getParams(): ?array
public function setCurl(?\Curl\Curl $curl): Request
public function getCurl(): \Curl\Curl
public function getURL(): string
public function getResponse(): Response
```

**Key Features:**
- HTTP request configuration
- Method and endpoint management
- Parameter handling
- cURL integration
- URL generation
- Response execution

### 3.3. Response (`Katu\Tools\Rest\API\Response`)
**Location:** `API/Response.php`

HTTP response handling for external APIs:

```php
// Key methods:
public function __construct(Request $request)
public function setData($data): Response
public function getData()
public function setInfo(array $info): Response
public function getInfo(): ?array
public function getStatus(): ?int
```

**Key Features:**
- Response data management
- HTTP status code handling
- cURL info integration
- Request context preservation

---

## 4. REST Response Generation

### 4.1. Basic Response Creation
```php
// Create simple response
$response = new RestResponse([
    "message" => "Success",
    "data" => $userData
]);

// Get JSON
$json = $response->getJSON();

// Get stream
$stream = $response->getStream();
```

### 4.2. Complex Response with Objects
```php
// Response with various object types
$response = new RestResponse([
    "user" => $user, // Model object
    "url" => new \Katu\Types\TURL("https://example.com"),
    "timestamp" => new \DateTime(),
    "code" => new \Katu\Tools\Strings\Code("USER_001"),
    "class" => new \Katu\Types\TClass(User::class)
]);

// Automatic type conversion
$json = $response->getJSON();
```

### 4.3. Nested Response Handling
```php
// Nested responses
$parentResponse = new RestResponse([
    "users" => [
        new RestResponse(["id" => 1, "name" => "John"]),
        new RestResponse(["id" => 2, "name" => "Jane"])
    ]
]);

// Recursive processing
$json = $parentResponse->getJSON();
```

---

## 5. External API Integration

### 5.1. Custom API Implementation
```php
class ExampleAPI extends \Katu\Tools\Rest\API\API
{
    public function getBaseURL(): \Katu\Types\TURL
    {
        return new \Katu\Types\TURL("https://api.example.com/v1");
    }

    public function getUsers(array $params = []): \Katu\Tools\Rest\API\Response
    {
        return $this->get("/users", $params);
    }

    public function createUser(array $userData): \Katu\Tools\Rest\API\Response
    {
        return $this->post("/users", $userData);
    }
}
```

### 5.2. API Usage Patterns
```php
// Create API instance
$api = new ExampleAPI();

// GET request
$response = $api->getUsers(["page" => 1, "limit" => 10]);
$users = $response->getData();

// POST request
$response = $api->createUser([
    "name" => "John Doe",
    "email" => "john@example.com"
]);

// Check response status
if ($response->getStatus() === 200) {
    $user = $response->getData();
} else {
    // Handle error
}
```

### 5.3. Custom cURL Configuration
```php
class CustomAPI extends \Katu\Tools\Rest\API\API
{
    public function generateCurl(): \Curl\Curl
    {
        $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_TIMEOUT, 30);
        $curl->setOpt(CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getApiKey(),
            "Content-Type: application/json"
        ]);

        return $curl;
    }

    private function getApiKey(): string
    {
        return \App\App::getEnvConfig()->getVariable("API_KEY");
    }
}
```

---

## 6. Type Conversion System

### 6.1. Automatic Type Conversion
The REST system automatically converts various object types:

```php
// DateTime objects
$response = new RestResponse([
    "created_at" => new \DateTime("2023-01-01 12:00:00")
]);
// Converts to: "2023-01-01T12:00:00+00:00"

// TURL objects
$response = new RestResponse([
    "url" => new \Katu\Types\TURL("https://example.com")
]);
// Converts to: "https://example.com"

// Code objects
$response = new RestResponse([
    "code" => new \Katu\Tools\Strings\Code("USER_001")
]);
// Converts to: "USER_001"

// TClass objects
$response = new RestResponse([
    "class" => new \Katu\Types\TClass(User::class)
]);
// Converts to portable class name
```

### 6.2. Stream Handling
```php
// Stream objects
$response = new RestResponse([
    "data" => $streamObject
]);
// Converts stream to string content
```

---

## 7. Error Response Integration

### 7.1. Exception Integration
```php
// Exception with REST response
$exception = new \Katu\Exceptions\ValidationException("Validation failed");
$restResponse = $exception->getRestResponse($request);

// Use in controller
return $response
    ->withStatus(400)
    ->withHeader("Content-Type", "application/json")
    ->withBody($restResponse->getStream());
```

### 7.2. Error Collection Integration
```php
// Error collection with REST response
$errors = new \Katu\Errors\ErrorCollection();
$errors->addError(new \Katu\Errors\Error("Field is required"));
$restResponse = $errors->getRestResponse($request);

// Use in validation
if ($validation->hasErrors()) {
    return $validation->getErrors()->getRestResponse($request);
}
```

---

## 8. Controller Integration

### 8.1. REST Controller Pattern
```php
class ApiController extends \Katu\Controllers\Controller
{
    public function getUsers(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $users = User::getAll();
            $restResponse = new RestResponse([
                "success" => true,
                "data" => $users,
                "count" => count($users)
            ]);

            return $response
                ->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->withBody($restResponse->getStream());

        } catch (\Katu\Exceptions\Exception $e) {
            $restResponse = $e->getRestResponse($request);

            return $response
                ->withStatus($e->getHttpCode())
                ->withHeader("Content-Type", "application/json")
                ->withBody($restResponse->getStream());
        }
    }
}
```

### 8.2. Validation Integration
```php
public function createUser(ServerRequestInterface $request): ResponseInterface
{
    $validation = $this->validateUserInput($request);

    if ($validation->hasErrors()) {
        $restResponse = $validation->getErrors()->getRestResponse($request);

        return $response
            ->withStatus(400)
            ->withHeader("Content-Type", "application/json")
            ->withBody($restResponse->getStream());
    }

    $user = $this->createUserFromValidation($validation);
    $restResponse = new RestResponse([
        "success" => true,
        "data" => $user
    ]);

    return $response
        ->withStatus(201)
        ->withHeader("Content-Type", "application/json")
        ->withBody($restResponse->getStream());
}
```

---

## 9. Best Practices

### 9.1. Response Design
- Use consistent response structure
- Include appropriate HTTP status codes
- Provide meaningful error messages
- Include relevant metadata

### 9.2. API Integration
- Implement proper error handling
- Use appropriate timeouts
- Include authentication headers
- Handle rate limiting

### 9.3. Performance Considerations
- Use appropriate caching strategies
- Minimize response payload size
- Optimize external API calls
- Monitor API response times

### 9.4. Security Considerations
- Validate all external API responses
- Use secure authentication methods
- Implement proper error handling
- Avoid exposing sensitive data

---

## 10. Common Patterns

### 10.1. Standard API Response
```php
public function getStandardResponse($data, bool $success = true, ?string $message = null): RestResponse
{
    $response = [
        "success" => $success,
        "data" => $data
    ];

    if ($message) {
        $response["message"] = $message;
    }

    return new RestResponse($response);
}
```

### 10.2. Paginated Response
```php
public function getPaginatedResponse($data, int $page, int $perPage, int $total): RestResponse
{
    return new RestResponse([
        "success" => true,
        "data" => $data,
        "pagination" => [
            "page" => $page,
            "per_page" => $perPage,
            "total" => $total,
            "total_pages" => ceil($total / $perPage)
        ]
    ]);
}
```

### 10.3. Error Response
```php
public function getErrorResponse(string $message, string $code = null, int $httpCode = 400): RestResponse
{
    $response = [
        "success" => false,
        "message" => $message
    ];

    if ($code) {
        $response["code"] = $code;
    }

    return new RestResponse($response);
}
```

---

## 11. Troubleshooting

### 11.1. Common Issues
- **Type Conversion Errors:** Check object types and serialization
- **Stream Issues:** Verify stream handling and content
- **API Connection Errors:** Check network connectivity and authentication
- **Response Format Issues:** Verify JSON encoding and structure

### 11.2. Debugging
- Use `getResponse()` to inspect processed data
- Check `getInfo()` for HTTP response details
- Monitor API response times and status codes
- Use logging for API call tracking

### 11.3. Performance Issues
- Monitor external API response times
- Implement appropriate caching
- Optimize response payload size
- Use connection pooling for external APIs

---

This documentation provides comprehensive coverage of the KATU REST API system. For specific implementation details, refer to the REST classes in `src/Tools/Rest/` and the integration with other framework components.
