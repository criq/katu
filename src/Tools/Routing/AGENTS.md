# Routing System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Routing system in the KATU framework. The routing system provides URL routing, route management, URL generation, and integration with the Slim framework for HTTP request handling.

---

## 1. System Overview

### 1.1. Purpose
- **URL Routing:** HTTP request routing and URL pattern matching
- **Route Management:** Route definition, collection, and management
- **URL Generation:** Programmatic URL generation for routes
- **Parameter Extraction:** Route parameter parsing and extraction
- **HTTP Method Support:** Multiple HTTP method support (GET, POST, etc.)
- **Slim Integration:** Integration with Slim framework routing

### 1.2. Architecture
- **Core Classes:** `Route`, `RouteCollection`, `URL`
- **Slim Integration:** Built on Slim framework routing
- **Parameter System:** Route parameter extraction and management
- **URL Generation:** Dynamic URL generation with parameters
- **Route Naming:** Named routes for easy reference
- **HTTP Methods:** Support for various HTTP methods

---

## 2. Core Routing Classes

### 2.1. Route (`Katu\Tools\Routing\Route`)
**Location:** `Route.php`

Main route class for defining application routes:

```php
// Key methods:
public function __construct(string $pattern, $callback, array $methods = null)
public static function getNameFromRequest(ServerRequestInterface $request): ?string
public function setName(string $name): Route
public function getName(): string
public function setPattern(string $pattern): Route
public function getPattern(): string
public function setCallback($callback): Route
public function getCallback()
public function setMethods(array $methods): Route
public function getMethods(): array
public function getArgs(): array
```

**Key Features:**
- Route pattern definition
- HTTP method support
- Callback function binding
- Parameter extraction
- Route naming
- Request integration

### 2.2. RouteCollection (`Katu\Tools\Routing\RouteCollection`)
**Location:** `RouteCollection.php`

Collection for managing multiple routes:

```php
// Key methods:
// Extends ArrayObject for array-like access
```

**Key Features:**
- Route collection management
- Array-like access
- Route organization
- Collection operations

### 2.3. URL (`Katu\Tools\Routing\URL`)
**Location:** `URL.php`

URL generation and manipulation utilities:

```php
// Key methods:
public static function isHttps(): bool
public static function getCurrent(): TURL
public static function getBase(): TURL
public static function getPathFor($route, ?array $args = []): string
public static function getFor($route, ?array $args = [], ?array $params = []): TURL
public static function getDecodedFor($route, $args = [], $params = []): TURL
public static function joinPaths(): string
```

**Key Features:**
- URL generation
- Route-based URL creation
- Parameter handling
- Base URL management
- HTTPS detection
- Path joining

---

## 3. Route Definition

### 3.1. Basic Route Creation
```php
// Simple route
$route = new Route("/users", "UserController@index", ["GET"]);

// Route with parameters
$route = new Route("/users/{id}", "UserController@show", ["GET"]);

// Multiple methods
$route = new Route("/users", "UserController@store", ["POST", "PUT"]);

// Named route
$route = new Route("/users/{id}", "UserController@show", ["GET"])
    ->setName("user.show");
```

### 3.2. Route with Callback
```php
// Closure callback
$route = new Route("/api/users", function($request, $response, $args) {
    return $response->withJson(["users" => User::getAll()]);
}, ["GET"]);

// Controller method callback
$route = new Route("/users", "UserController@index", ["GET"]);

// Static method callback
$route = new Route("/health", "HealthController::check", ["GET"]);
```

### 3.3. Route Parameter Extraction
```php
// Route with multiple parameters
$route = new Route("/users/{userId}/posts/{postId}", "PostController@show", ["GET"]);

// Get route arguments
$args = $route->getArgs(); // Returns ["userId", "postId"]

// Access parameters in callback
$route = new Route("/users/{id}", function($request, $response, $args) {
    $userId = $args["id"];
    $user = User::get($userId);
    return $response->withJson($user);
}, ["GET"]);
```

---

## 4. Route Management

### 4.1. Route Collection
```php
// Create route collection
$routes = new RouteCollection();

// Add routes
$routes[] = new Route("/", "HomeController@index", ["GET"]);
$routes[] = new Route("/about", "HomeController@about", ["GET"]);
$routes[] = new Route("/contact", "ContactController@show", ["GET"]);

// Named routes
$routes[] = (new Route("/users", "UserController@index", ["GET"]))->setName("users.index");
$routes[] = (new Route("/users/{id}", "UserController@show", ["GET"]))->setName("users.show");
```

### 4.2. Route Filtering and Search
```php
// Filter routes by method
$getRoutes = array_filter($routes->getArrayCopy(), function($route) {
    return in_array("GET", $route->getMethods());
});

// Filter routes by pattern
$userRoutes = array_filter($routes->getArrayCopy(), function($route) {
    return strpos($route->getPattern(), "/users") === 0;
});

// Find route by name
$userShowRoute = null;
foreach ($routes as $route) {
    if ($route->getName() === "users.show") {
        $userShowRoute = $route;
        break;
    }
}
```

---

## 5. URL Generation

### 5.1. Basic URL Generation
```php
// Generate URL for named route
$url = URL::getFor("users.show", ["id" => 123]);
// Returns: https://example.com/users/123

// Generate URL with query parameters
$url = URL::getFor("users.index", [], ["page" => 2, "limit" => 10]);
// Returns: https://example.com/users?page=2&limit=10

// Generate path only
$path = URL::getPathFor("users.show", ["id" => 123]);
// Returns: /users/123
```

### 5.2. URL with Parameters
```php
// Multiple parameters
$url = URL::getFor("posts.show", [
    "userId" => 123,
    "postId" => 456
]);
// Returns: https://example.com/users/123/posts/456

// Encoded parameters
$url = URL::getDecodedFor("users.show", ["id" => "user@example.com"]);
// Returns: https://example.com/users/user%40example.com
```

### 5.3. Base URL Management
```php
// Get current URL
$currentUrl = URL::getCurrent();
// Returns: https://example.com/current/path

// Get base URL
$baseUrl = URL::getBase();
// Returns: https://example.com

// Check HTTPS
$isHttps = URL::isHttps();
// Returns: true/false
```

---

## 6. Route Configuration

### 6.1. RouterConfig Integration
```php
class AppRouterConfig extends \Katu\Config\RouterConfig
{
    public function getRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        // Home routes
        $routes[] = new Route("/", "HomeController@index", ["GET"]);
        $routes[] = new Route("/about", "HomeController@about", ["GET"]);

        // User routes
        $routes[] = (new Route("/users", "UserController@index", ["GET"]))->setName("users.index");
        $routes[] = (new Route("/users/{id}", "UserController@show", ["GET"]))->setName("users.show");
        $routes[] = (new Route("/users", "UserController@store", ["POST"]))->setName("users.store");
        $routes[] = (new Route("/users/{id}", "UserController@update", ["PUT"]))->setName("users.update");
        $routes[] = (new Route("/users/{id}", "UserController@delete", ["DELETE"]))->setName("users.delete");

        return $routes;
    }
}
```

### 6.2. Route Grouping
```php
// API routes
$apiRoutes = new RouteCollection();
$apiRoutes[] = new Route("/api/users", "Api\UserController@index", ["GET"]);
$apiRoutes[] = new Route("/api/users/{id}", "Api\UserController@show", ["GET"]);

// Admin routes
$adminRoutes = new RouteCollection();
$adminRoutes[] = new Route("/admin/users", "Admin\UserController@index", ["GET"]);
$adminRoutes[] = new Route("/admin/users/{id}", "Admin\UserController@show", ["GET"]);

// Combine routes
$allRoutes = new RouteCollection();
$allRoutes->addRoutes($apiRoutes);
$allRoutes->addRoutes($adminRoutes);
```

---

## 7. HTTP Method Support

### 7.1. Standard HTTP Methods
```php
// GET routes
$routes[] = new Route("/users", "UserController@index", ["GET"]);
$routes[] = new Route("/users/{id}", "UserController@show", ["GET"]);

// POST routes
$routes[] = new Route("/users", "UserController@store", ["POST"]);
$routes[] = new Route("/users/{id}/posts", "PostController@store", ["POST"]);

// PUT routes
$routes[] = new Route("/users/{id}", "UserController@update", ["PUT"]);

// DELETE routes
$routes[] = new Route("/users/{id}", "UserController@delete", ["DELETE"]);

// PATCH routes
$routes[] = new Route("/users/{id}", "UserController@patch", ["PATCH"]);
```

### 7.2. Multiple Method Support
```php
// Route supporting multiple methods
$route = new Route("/users/{id}", "UserController@handle", ["GET", "POST", "PUT", "DELETE"]);

// RESTful resource routes
$routes[] = new Route("/users", "UserController@index", ["GET"]);
$routes[] = new Route("/users", "UserController@store", ["POST"]);
$routes[] = new Route("/users/{id}", "UserController@show", ["GET"]);
$routes[] = new Route("/users/{id}", "UserController@update", ["PUT"]);
$routes[] = new Route("/users/{id}", "UserController@delete", ["DELETE"]);
```

---

## 8. Route Parameters

### 8.1. Parameter Types
```php
// String parameters
$route = new Route("/users/{id}", "UserController@show", ["GET"]);

// Numeric parameters
$route = new Route("/posts/{id}", "PostController@show", ["GET"]);

// Multiple parameters
$route = new Route("/users/{userId}/posts/{postId}", "PostController@show", ["GET"]);

// Optional parameters
$route = new Route("/users/{id?}", "UserController@show", ["GET"]);
```

### 8.2. Parameter Validation
```php
// Route with parameter constraints
$route = new Route("/users/{id}", "UserController@show", ["GET"]);

// In controller
public function show(ServerRequestInterface $request, ResponseInterface $response, array $args)
{
    $userId = $args["id"];

    // Validate parameter
    if (!is_numeric($userId)) {
        return $response->withStatus(400)->withJson(["error" => "Invalid user ID"]);
    }

    $user = User::get($userId);
    if (!$user) {
        return $response->withStatus(404)->withJson(["error" => "User not found"]);
    }

    return $response->withJson($user);
}
```

---

## 9. Integration with Controllers

### 9.1. Controller Route Binding
```php
class UserController extends \Katu\Controllers\Controller
{
    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $users = User::getAll();
        return $this->getViewResponse("users.index", ["users" => $users]);
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $userId = $request->getAttribute("route")->getArgs()["id"];
        $user = User::get($userId);
        return $this->getViewResponse("users.show", ["user" => $user]);
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $user = new User($data);
        $user->persist();
        return $this->getViewResponse("users.show", ["user" => $user]);
    }
}
```

### 9.2. Route Name Access
```php
// Get route name from request
$routeName = Route::getNameFromRequest($request);

// Use route name for conditional logic
if ($routeName === "users.show") {
    // User show page specific logic
}

// Generate URLs based on route name
$editUrl = URL::getFor("users.edit", ["id" => $userId]);
$deleteUrl = URL::getFor("users.delete", ["id" => $userId]);
```

---

## 10. Best Practices

### 10.1. Route Design
- Use RESTful route patterns
- Implement consistent naming conventions
- Use appropriate HTTP methods
- Include parameter validation

### 10.2. URL Generation
- Use named routes for URL generation
- Implement proper parameter encoding
- Use base URL configuration
- Handle query parameters appropriately

### 10.3. Route Organization
- Group related routes
- Use route collections for organization
- Implement route middleware when needed
- Document route purposes

### 10.4. Performance
- Use specific route patterns
- Implement route caching when appropriate
- Monitor route performance
- Optimize route matching

---

## 11. Common Patterns

### 11.1. RESTful Resource Routes
```php
// Resource routes
$routes[] = (new Route("/users", "UserController@index", ["GET"]))->setName("users.index");
$routes[] = (new Route("/users", "UserController@store", ["POST"]))->setName("users.store");
$routes[] = (new Route("/users/{id}", "UserController@show", ["GET"]))->setName("users.show");
$routes[] = (new Route("/users/{id}", "UserController@update", ["PUT"]))->setName("users.update");
$routes[] = (new Route("/users/{id}", "UserController@delete", ["DELETE"]))->setName("users.delete");
```

### 11.2. API Routes
```php
// API routes with versioning
$routes[] = new Route("/api/v1/users", "Api\V1\UserController@index", ["GET"]);
$routes[] = new Route("/api/v1/users/{id}", "Api\V1\UserController@show", ["GET"]);
$routes[] = new Route("/api/v2/users", "Api\V2\UserController@index", ["GET"]);
$routes[] = new Route("/api/v2/users/{id}", "Api\V2\UserController@show", ["GET"]);
```

### 11.3. Admin Routes
```php
// Admin routes with authentication
$routes[] = new Route("/admin/users", "Admin\UserController@index", ["GET"]);
$routes[] = new Route("/admin/users/{id}", "Admin\UserController@show", ["GET"]);
$routes[] = new Route("/admin/users/{id}/edit", "Admin\UserController@edit", ["GET"]);
$routes[] = new Route("/admin/users/{id}", "Admin\UserController@update", ["PUT"]);
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Route Not Found:** Check route pattern and HTTP method
- **Parameter Issues:** Verify parameter extraction and validation
- **URL Generation Errors:** Check route names and parameters
- **Method Not Allowed:** Verify HTTP method configuration

### 12.2. Debugging
- Use route name extraction for debugging
- Check route pattern matching
- Verify parameter passing
- Monitor route performance

### 12.3. Performance Issues
- Optimize route patterns
- Implement route caching
- Monitor route matching performance
- Use specific patterns when possible

---

This documentation provides comprehensive coverage of the KATU Routing system. For specific implementation details, refer to the routing classes in `src/Tools/Routing/` and the integration with the Slim framework.
