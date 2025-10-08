# Session Management System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Session Management system in the KATU framework. The session system provides advanced session handling, flash messages, and variable management capabilities.

---

## 1. System Overview

### 1.1. Purpose
- **Session Management:** Secure session handling with configurable options
- **Flash Messages:** Temporary messages for user feedback
- **Variable Storage:** Session-based variable management
- **Library System:** Organized data storage in sessions
- **Security:** Secure session configuration and management
- **Integration:** Seamless integration with application configuration

### 1.2. Architecture
- **Core Classes:** `Session`, `Flash`, `Library`, `VariableLibrary`
- **Flash System:** Multiple flash message types (Success, Error, Message, Payload)
- **Library System:** Organized session data storage
- **Configuration:** Cookie and session configuration management
- **Storage:** File-based session storage

---

## 2. Core Session Classes

### 2.1. Session (`Katu\Tools\Session\Session`)
**Location:** `Session.php`

Main session management class:

```php
// Key methods:
public function __construct()
public function getOptions(): array
public function getStorage(): \Katu\Files\File
public function getContents(): array
public function set(string $key, $value): Session
public function get(string $key, $default = null)
public function has(string $key): bool
public function remove(string $key): Session
public function clear(): Session
public function destroy(): Session
public function regenerateId(): Session
```

**Key Features:**
- Automatic session initialization
- Configurable session options
- Secure cookie settings
- Session data manipulation
- ID regeneration for security

### 2.2. Flash (`Katu\Tools\Session\Flash`)
**Location:** `Flash.php`

Abstract base class for flash messages:

```php
// Key methods:
public function getClassCode(): Code
```

**Key Features:**
- Abstract flash message base
- Class code generation
- Type identification

### 2.3. Library (`Katu\Tools\Session\Library`)
**Location:** `Library.php`

Abstract base class for session libraries:

```php
// Key methods:
public function __construct(string $key)
public function setKey(string $key): Library
public function getKey(): string
```

**Key Features:**
- Organized session data storage
- Key-based data management
- ArrayObject inheritance

---

## 3. Flash Message System

### 3.1. MessageFlash (`MessageFlash.php`)
**Location:** `MessageFlash.php`

Base class for message-based flash messages:

```php
// Key methods:
public function __construct(?string $message)
public function setMessage(?string $message): MessageFlash
public function getMessage(): ?string
```

### 3.2. SuccessFlash (`SuccessFlash.php`)
**Location:** `SuccessFlash.php`

Success message flash:

```php
$success = new SuccessFlash("Operation completed successfully!");
```

### 3.3. ErrorFlash (`ErrorFlash.php`)
**Location:** `ErrorFlash.php`

Error message flash:

```php
$error = new ErrorFlash("An error occurred during processing.");
```

### 3.4. PayloadFlash (`PayloadFlash.php`)
**Location:** `PayloadFlash.php`

Payload-based flash message:

```php
$payload = new PayloadFlash(['data' => $result, 'status' => 'success']);
```

---

## 4. Library System

### 4.1. FlashLibrary (`FlashLibrary.php`)
**Location:** `FlashLibrary.php`

Flash message library:

```php
// Key methods:
public function addFlash(Flash $flash): FlashLibrary
public function getFlashes(): array
public function clearFlashes(): FlashLibrary
```

### 4.2. VariableLibrary (`VariableLibrary.php`)
**Location:** `VariableLibrary.php`

Variable storage library:

```php
// Key methods:
public function setVariable(string $key, $value): VariableLibrary
public function getVariable(string $key, $default = null)
public function hasVariable(string $key): bool
public function removeVariable(string $key): VariableLibrary
```

### 4.3. LibraryCollection (`LibraryCollection.php`)
**Location:** `LibraryCollection.php`

Collection for managing multiple libraries:

```php
// Key methods:
public function addLibrary(Library $library): LibraryCollection
public function getLibrary(string $key): ?Library
public function hasLibrary(string $key): bool
```

---

## 5. Usage Patterns

### 5.1. Basic Session Management
```php
use Katu\Tools\Session\Session;

// Initialize session
$session = new Session();

// Set session data
$session->set('user_id', 123);
$session->set('username', 'john_doe');
$session->set('is_logged_in', true);

// Get session data
$userId = $session->get('user_id');
$username = $session->get('username');
$isLoggedIn = $session->get('is_logged_in', false);

// Check if key exists
if ($session->has('user_id')) {
    echo "User is logged in";
}

// Remove session data
$session->remove('username');

// Clear all session data
$session->clear();
```

### 5.2. Flash Messages
```php
use Katu\Tools\Session\SuccessFlash;
use Katu\Tools\Session\ErrorFlash;
use Katu\Tools\Session\FlashLibrary;

// Create flash library
$flashLibrary = new FlashLibrary('flash_messages');

// Add success message
$success = new SuccessFlash("User created successfully!");
$flashLibrary->addFlash($success);

// Add error message
$error = new ErrorFlash("Invalid email address provided.");
$flashLibrary->addFlash($error);

// Get all flash messages
$flashes = $flashLibrary->getFlashes();

// Display flash messages
foreach ($flashes as $flash) {
    if ($flash instanceof SuccessFlash) {
        echo "<div class='alert alert-success'>" . $flash->getMessage() . "</div>";
    } elseif ($flash instanceof ErrorFlash) {
        echo "<div class='alert alert-danger'>" . $flash->getMessage() . "</div>";
    }
}

// Clear flash messages after display
$flashLibrary->clearFlashes();
```

### 5.3. Variable Management
```php
use Katu\Tools\Session\VariableLibrary;

// Create variable library
$vars = new VariableLibrary('app_variables');

// Set variables
$vars->setVariable('theme', 'dark');
$vars->setVariable('language', 'en');
$vars->setVariable('timezone', 'UTC');

// Get variables
$theme = $vars->getVariable('theme', 'light');
$language = $vars->getVariable('language', 'en');

// Check if variable exists
if ($vars->hasVariable('timezone')) {
    $timezone = $vars->getVariable('timezone');
}

// Remove variable
$vars->removeVariable('theme');
```

### 5.4. Complex Session Data
```php
use Katu\Tools\Session\Session;
use Katu\Tools\Session\LibraryCollection;

// Create session
$session = new Session();

// Create library collection
$libraries = new LibraryCollection();

// Add flash library
$flashLib = new FlashLibrary('messages');
$libraries->addLibrary($flashLib);

// Add variable library
$varLib = new VariableLibrary('settings');
$libraries->addLibrary($varLib);

// Store complex data
$userData = [
    'id' => 123,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'preferences' => [
        'theme' => 'dark',
        'notifications' => true
    ]
];

$session->set('user_data', $userData);

// Store libraries
$session->set('libraries', $libraries);
```

### 5.5. Controller Integration
```php
class UserController extends Controller
{
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $session = new Session();
        $flashLib = new FlashLibrary('messages');

        // Validate credentials
        if ($this->validateCredentials($request)) {
            // Set user session
            $session->set('user_id', $this->getUserId($request));
            $session->set('is_logged_in', true);

            // Add success message
            $success = new SuccessFlash("Login successful!");
            $flashLib->addFlash($success);

            return $this->redirectResponse('/dashboard');
        } else {
            // Add error message
            $error = new ErrorFlash("Invalid credentials provided.");
            $flashLib->addFlash($error);

            return $this->redirectResponse('/login');
        }
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        $session = new Session();
        $flashLib = new FlashLibrary('messages');

        // Clear session
        $session->clear();

        // Add success message
        $success = new SuccessFlash("You have been logged out successfully.");
        $flashLib->addFlash($success);

        return $this->redirectResponse('/');
    }
}
```

---

## 6. Advanced Features

### 6.1. Custom Flash Message Types
```php
class WarningFlash extends MessageFlash
{
    public function getClassCode(): Code
    {
        return new Code('warning');
    }
}

class InfoFlash extends MessageFlash
{
    public function getClassCode(): Code
    {
        return new Code('info');
    }
}

// Usage
$warning = new WarningFlash("Please review your input.");
$info = new InfoFlash("New features are available!");
```

### 6.2. Custom Library Types
```php
class UserPreferencesLibrary extends Library
{
    const KEY = 'user_preferences';

    public function setPreference(string $key, $value): UserPreferencesLibrary
    {
        $this[$key] = $value;
        return $this;
    }

    public function getPreference(string $key, $default = null)
    {
        return $this[$key] ?? $default;
    }

    public function getTheme(): string
    {
        return $this->getPreference('theme', 'light');
    }

    public function setTheme(string $theme): UserPreferencesLibrary
    {
        return $this->setPreference('theme', $theme);
    }
}

// Usage
$prefs = new UserPreferencesLibrary('user_preferences');
$prefs->setTheme('dark');
$prefs->setPreference('notifications', true);
$theme = $prefs->getTheme();
```

### 6.3. Session Security
```php
class SecureSession extends Session
{
    public function regenerateId(): Session
    {
        // Regenerate session ID for security
        session_regenerate_id(true);
        return $this;
    }

    public function validateSession(): bool
    {
        // Add custom session validation logic
        $lastActivity = $this->get('last_activity');
        $currentTime = time();

        // Check if session is expired (30 minutes)
        if ($lastActivity && ($currentTime - $lastActivity) > 1800) {
            $this->destroy();
            return false;
        }

        // Update last activity
        $this->set('last_activity', $currentTime);
        return true;
    }
}
```

### 6.4. Flash Message Templates
```php
class FlashMessageRenderer
{
    public function renderFlash(Flash $flash): string
    {
        $classCode = $flash->getClassCode();
        $message = '';

        if ($flash instanceof MessageFlash) {
            $message = $flash->getMessage();
        }

        $cssClass = $this->getCssClass($classCode);

        return "<div class='alert {$cssClass}'>{$message}</div>";
    }

    private function getCssClass(Code $classCode): string
    {
        switch ((string)$classCode) {
            case 'success':
                return 'alert-success';
            case 'error':
                return 'alert-danger';
            case 'warning':
                return 'alert-warning';
            case 'info':
                return 'alert-info';
            default:
                return 'alert-secondary';
        }
    }
}
```

---

## 7. Configuration

### 7.1. Session Options
```php
// Session options are automatically configured based on CookieConfig
$session = new Session();

// Options include:
// - cookie_domain: Domain for session cookies
// - cookie_httponly: HTTP-only cookie setting
// - cookie_lifetime: Cookie lifetime
// - cookie_path: Cookie path
// - cookie_secure: Secure cookie setting
// - gc_maxlifetime: Garbage collection lifetime
// - save_path: Session storage path
// - use_cookies: Use cookies for session ID
// - use_only_cookies: Use only cookies (no URL parameters)
// - use_strict_mode: Strict session mode
```

### 7.2. Storage Configuration
```php
// Session storage is automatically configured
$session = new Session();
$storage = $session->getStorage();

// Storage path: /tmp/session/
// Directory is automatically created if it doesn't exist
```

---

## 8. Best Practices

### 8.1. Security
- Always regenerate session ID after login
- Use secure cookie settings in production
- Implement session timeout
- Validate session data

### 8.2. Performance
- Don't store large objects in session
- Use appropriate session lifetime
- Clean up unused session data
- Consider session storage backend for high traffic

### 8.3. Flash Messages
- Clear flash messages after display
- Use appropriate message types
- Keep messages concise and user-friendly
- Implement proper message styling

### 8.4. Data Organization
- Use libraries for organized data storage
- Group related data together
- Use descriptive keys
- Implement proper data validation

---

## 9. Integration Examples

### 9.1. Middleware Integration
```php
class SessionMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = new Session();

        // Validate session
        if (!$session->validateSession()) {
            return $this->redirectToLogin();
        }

        // Add session to request
        $request = $request->withAttribute('session', $session);

        return $handler->handle($request);
    }
}
```

### 9.2. Template Integration
```php
// In Twig template
{% for flash in session.flash_messages %}
    <div class="alert alert-{{ flash.class_code }}">
        {{ flash.message }}
    </div>
{% endfor %}
```

### 9.3. API Integration
```php
class SessionController extends Controller
{
    public function getSessionData(ServerRequestInterface $request): ResponseInterface
    {
        $session = new Session();
        $data = $session->getContents();

        return $this->jsonResponse($data);
    }

    public function setSessionData(ServerRequestInterface $request): ResponseInterface
    {
        $session = new Session();
        $data = $request->getParsedBody();

        foreach ($data as $key => $value) {
            $session->set($key, $value);
        }

        return $this->jsonResponse(['success' => true]);
    }
}
```

---

## 10. Common Patterns

### 10.1. User Authentication Pattern
```php
// User login with session management
$user = User::authenticate($email, $password);
if ($user) {
    $session = new Session();
    $session->set("user_id", $user->id);
    $session->set("user_role", $user->role);

    $session->getFlashes()->addSuccess("Welcome back!");
    return $this->redirect("/dashboard");
}
```

### 10.2. Flash Message Pattern
```php
// Controller action with flash messages
public function createUser(ServerRequestInterface $request): ResponseInterface
{
    try {
        $user = new User($request->getParsedBody());
        $user->persist();

        $session = new Session();
        $session->getFlashes()->addSuccess("User created successfully!");

        return $this->redirect("/users");
    } catch (Exception $e) {
        $session = new Session();
        $session->getFlashes()->addError("Failed to create user: " . $e->getMessage());

        return $this->redirect("/users/create");
    }
}
```

### 10.3. Session Data Management
```php
// Session data with expiration
$session = new Session();
$session->set("cart", $cartData);
$session->set("last_activity", time());

// Check session timeout
if ($session->get("last_activity") < time() - 3600) {
    $session->destroy();
    return $this->redirect("/login");
}
```

---

## 11. Troubleshooting

### 11.1. Common Issues
- **Session Not Starting:** Check PHP session configuration
- **Flash Messages Not Displaying:** Ensure messages are cleared after display
- **Data Not Persisting:** Check session storage permissions
- **Security Issues:** Verify cookie settings and session regeneration

### 11.2. Debugging
- Enable session debugging in PHP
- Check session storage directory permissions
- Verify cookie configuration
- Monitor session data with `getContents()`

---

This documentation provides comprehensive coverage of the Session Management system. For specific implementation details, refer to the source code in `src/Tools/Session/`.
