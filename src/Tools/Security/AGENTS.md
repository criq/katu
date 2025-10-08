# Security System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Security system in the KATU framework. The security system provides password handling, JWT token management, encryption utilities, and secure data processing.

---

## 1. System Overview

### 1.1. Purpose
- **Password Security:** Secure password hashing and verification
- **JWT Tokens:** JSON Web Token creation and validation
- **Encryption:** Data encryption and decryption utilities
- **Authentication:** Secure authentication mechanisms
- **Authorization:** Access control and permission management
- **Data Protection:** Secure handling of sensitive data

### 1.2. Architecture
- **Core Classes:** `PlainPassword`, `JWT`, `PasswordEncoder`
- **Encryption:** Secure encryption algorithms
- **Token Management:** JWT token lifecycle
- **Password Handling:** Secure password operations
- **Security Utilities:** Additional security functions

---

## 2. Core Security Classes

### 2.1. PlainPassword (`Katu\Tools\Security\PlainPassword`)
**Location:** `PlainPassword.php`

Secure password handling with modern hashing:

```php
// Key methods:
public function __construct(?string $password = null)
public function setPassword(?string $password): PlainPassword
public function getPassword(): ?string
public function getHash(): string
public function verify(string $password): bool
public function needsRehash(): bool
public function getAlgorithm(): string
public function getOptions(): array
```

**Key Features:**
- Modern password hashing (Argon2, bcrypt)
- Automatic salt generation
- Password verification
- Rehashing detection
- Algorithm configuration
- Security options management

### 2.2. JWT (`Katu\Tools\Security\JWT`)
**Location:** `JWT.php`

JSON Web Token management with enhanced security:

```php
// Key methods:
public function __construct(?string $token = null)
public function setToken(?string $token): JWT
public function getToken(): ?string
public function create(array $payload, ?string $key = null, ?string $algorithm = null): string
public function verify(string $token, ?string $key = null): bool
public function getPayload(): ?array
public function getHeader(): ?array
public function isExpired(): bool
public function getExpiration(): ?int
public function getIssuedAt(): ?int
public function getSubject(): ?string
```

**Key Features:**
- Token creation and verification
- Payload and header management
- Expiration checking
- Algorithm support (HS256, RS256, etc.)
- Security validation
- Token parsing

### 2.3. PasswordEncoder (`Katu\Tools\Security\PasswordEncoder`)
**Location:** `PasswordEncoder.php`

Password encoding and verification utilities:

```php
// Key methods:
public static function encode(string $password, ?string $algorithm = null): string
public static function verify(string $password, string $hash): bool
public static function needsRehash(string $hash, ?string $algorithm = null): bool
public static function getAlgorithm(string $hash): ?string
public static function getOptions(string $hash): array
```

**Key Features:**
- Password encoding
- Hash verification
- Rehashing detection
- Algorithm detection
- Options extraction

---

## 3. Usage Patterns

### 3.1. Password Management
```php
use Katu\Tools\Security\PlainPassword;

// Create password object
$password = new PlainPassword("user_password");

// Get hashed password
$hash = $password->getHash();
echo "Hashed password: " . $hash;

// Verify password
$isValid = $password->verify("user_password");
if ($isValid) {
    echo "Password is valid";
}

// Check if rehashing is needed
if ($password->needsRehash()) {
    $newHash = $password->getHash();
    // Update stored hash
}
```

### 3.2. JWT Token Management
```php
use Katu\Tools\Security\JWT;

// Create JWT token
$jwt = new JWT();
$payload = [
    "user_id" => 123,
    "email" => "user@example.com",
    "exp" => time() + 3600, // 1 hour expiration
    "iat" => time(),
    "sub" => "user_123"
];

$token = $jwt->create($payload, "secret_key", "HS256");
echo "JWT Token: " . $token;

// Verify token
$jwt->setToken($token);
if ($jwt->verify($token, "secret_key")) {
    $payload = $jwt->getPayload();
    echo "User ID: " . $payload["user_id"];
}

// Check expiration
if ($jwt->isExpired()) {
    echo "Token has expired";
} else {
    echo "Token expires at: " . date("Y-m-d H:i:s", $jwt->getExpiration());
}
```

### 3.3. Password Encoding
```php
use Katu\Tools\Security\PasswordEncoder;

// Encode password
$password = "user_password";
$hash = PasswordEncoder::encode($password);
echo "Encoded password: " . $hash;

// Verify password
$isValid = PasswordEncoder::verify($password, $hash);
if ($isValid) {
    echo "Password is valid";
}

// Check if rehashing is needed
if (PasswordEncoder::needsRehash($hash)) {
    $newHash = PasswordEncoder::encode($password);
    // Update stored hash
}

// Get algorithm information
$algorithm = PasswordEncoder::getAlgorithm($hash);
$options = PasswordEncoder::getOptions($hash);
```

### 3.4. User Authentication
```php
class UserAuthentication
{
    public function authenticateUser(string $email, string $password): ?array
    {
        // Get user from database
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return null;
        }

        // Verify password
        $passwordObj = new PlainPassword();
        if (!$passwordObj->verify($password, $user["password_hash"])) {
            return null;
        }

        // Check if password needs rehashing
        if ($passwordObj->needsRehash()) {
            $newHash = $passwordObj->getHash();
            $this->updateUserPassword($user["id"], $newHash);
        }

        // Create JWT token
        $jwt = new JWT();
        $payload = [
            "user_id" => $user["id"],
            "email" => $user["email"],
            "exp" => time() + 3600,
            "iat" => time(),
            "sub" => "user_" . $user["id"]
        ];

        $token = $jwt->create($payload, $this->getJWTSecret(), "HS256");

        return [
            "user" => $user,
            "token" => $token,
            "expires_at" => $payload["exp"]
        ];
    }
}
```

### 3.5. Token Validation
```php
class TokenValidator
{
    public function validateToken(string $token): ?array
    {
        $jwt = new JWT();
        $jwt->setToken($token);

        // Verify token signature
        if (!$jwt->verify($token, $this->getJWTSecret())) {
            return null;
        }

        // Check expiration
        if ($jwt->isExpired()) {
            return null;
        }

        // Get payload
        $payload = $jwt->getPayload();
        if (!$payload) {
            return null;
        }

        // Validate required claims
        if (!isset($payload["user_id"]) || !isset($payload["email"])) {
            return null;
        }

        return $payload;
    }

    public function refreshToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        if (!$payload) {
            return null;
        }

        // Create new token with extended expiration
        $jwt = new JWT();
        $newPayload = $payload;
        $newPayload["exp"] = time() + 3600;
        $newPayload["iat"] = time();

        return $jwt->create($newPayload, $this->getJWTSecret(), "HS256");
    }
}
```

---

## 4. Advanced Features

### 4.1. Password Policy Enforcement
```php
class PasswordPolicy
{
    public function validatePassword(string $password): array
    {
        $errors = [];

        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }

        // Complexity requirements
        if (!preg_match("/[A-Z]/", $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        if (!preg_match("/[a-z]/", $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        if (!preg_match("/[0-9]/", $password)) {
            $errors[] = "Password must contain at least one number";
        }

        if (!preg_match("/[^A-Za-z0-9]/", $password)) {
            $errors[] = "Password must contain at least one special character";
        }

        return $errors;
    }

    public function isPasswordValid(string $password): bool
    {
        return empty($this->validatePassword($password));
    }
}
```

### 4.2. Secure Token Generation
```php
class SecureTokenGenerator
{
    public function generateAccessToken(int $userId): string
    {
        $jwt = new JWT();
        $payload = [
            "user_id" => $userId,
            "type" => "access",
            "exp" => time() + 3600, // 1 hour
            "iat" => time(),
            "jti" => $this->generateUniqueId()
        ];

        return $jwt->create($payload, $this->getJWTSecret(), "HS256");
    }

    public function generateRefreshToken(int $userId): string
    {
        $jwt = new JWT();
        $payload = [
            "user_id" => $userId,
            "type" => "refresh",
            "exp" => time() + 86400 * 30, // 30 days
            "iat" => time(),
            "jti" => $this->generateUniqueId()
        ];

        return $jwt->create($payload, $this->getRefreshSecret(), "HS256");
    }

    private function generateUniqueId(): string
    {
        return bin2hex(random_bytes(16));
    }
}
```

### 4.3. Password Reset System
```php
class PasswordReset
{
    public function generateResetToken(int $userId): string
    {
        $jwt = new JWT();
        $payload = [
            "user_id" => $userId,
            "type" => "password_reset",
            "exp" => time() + 3600, // 1 hour
            "iat" => time(),
            "jti" => $this->generateUniqueId()
        ];

        return $jwt->create($payload, $this->getResetSecret(), "HS256");
    }

    public function validateResetToken(string $token): ?int
    {
        $jwt = new JWT();
        $jwt->setToken($token);

        if (!$jwt->verify($token, $this->getResetSecret())) {
            return null;
        }

        if ($jwt->isExpired()) {
            return null;
        }

        $payload = $jwt->getPayload();
        if ($payload["type"] !== "password_reset") {
            return null;
        }

        return $payload["user_id"];
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $userId = $this->validateResetToken($token);
        if (!$userId) {
            return false;
        }

        // Validate new password
        $policy = new PasswordPolicy();
        if (!$policy->isPasswordValid($newPassword)) {
            return false;
        }

        // Hash new password
        $password = new PlainPassword($newPassword);
        $hash = $password->getHash();

        // Update user password
        $this->updateUserPassword($userId, $hash);

        return true;
    }
}
```

---

## 5. Security Best Practices

### 5.1. Password Security
- Use strong hashing algorithms (Argon2, bcrypt)
- Implement password policies
- Regular password rehashing
- Secure password storage
- Password complexity requirements

### 5.2. Token Security
- Use short-lived access tokens
- Implement refresh token rotation
- Secure token storage
- Token revocation mechanisms
- Algorithm validation

### 5.3. Data Protection
- Encrypt sensitive data
- Use secure random generation
- Implement proper key management
- Regular security audits
- Monitor for security breaches

---

## 6. Configuration

### 6.1. Password Configuration
```php
// Configure password hashing
$password = new PlainPassword();
$password->setAlgorithm("argon2id");
$password->setOptions([
    "memory_cost" => 65536,
    "time_cost" => 4,
    "threads" => 3
]);
```

### 6.2. JWT Configuration
```php
// Configure JWT settings
$jwt = new JWT();
$jwt->setAlgorithm("HS256");
$jwt->setIssuer("your-app.com");
$jwt->setAudience("your-app-users");
```

---

## 7. Integration Examples

### 7.1. Controller Integration
```php
class AuthController extends Controller
{
    public function login(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $email = $data["email"] ?? null;
        $password = $data["password"] ?? null;

        if (!$email || !$password) {
            return $this->errorResponse("Email and password are required");
        }

        $auth = new UserAuthentication();
        $result = $auth->authenticateUser($email, $password);

        if (!$result) {
            return $this->errorResponse("Invalid credentials");
        }

        return $this->jsonResponse([
            "user" => $result["user"],
            "token" => $result["token"],
            "expires_at" => $result["expires_at"]
        ]);
    }
}
```

### 7.2. Middleware Integration
```php
class AuthMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->extractToken($request);
        if (!$token) {
            throw new UnauthorizedException("No token provided");
        }

        $validator = new TokenValidator();
        $payload = $validator->validateToken($token);
        if (!$payload) {
            throw new UnauthorizedException("Invalid token");
        }

        // Add user to request
        $request = $request->withAttribute("user_id", $payload["user_id"]);
        $request = $request->withAttribute("user_email", $payload["email"]);

        return $handler->handle($request);
    }
}
```

---

## 8. Common Patterns

### 8.1. Authentication Flow
```php
// User login with password verification
$user = User::getByEmail($email);
if ($user && $user->getPassword()->verify($password)) {
    $token = JWT::create([
        "user_id" => $user->id,
        "email" => $user->email,
        "exp" => time() + 3600
    ]);

    $this->setAuthToken($token);
    return $this->redirect("/dashboard");
}
```

### 8.2. Password Management
```php
// Password creation and verification
$password = new PlainPassword($userInput);
$hashedPassword = $password->getHash();

// Store in database
$user->password_hash = $hashedPassword;
$user->persist();

// Later verification
$storedPassword = new PlainPassword($user->password_hash);
if ($storedPassword->verify($loginPassword)) {
    // Login successful
}
```

### 8.3. Token Management
```php
// Create and verify JWT tokens
$token = JWT::create([
    "user_id" => $user->id,
    "role" => $user->role,
    "exp" => time() + 3600
]);

// Verify token
$payload = JWT::verify($token);
if ($payload && $payload["user_id"]) {
    $user = User::get($payload["user_id"]);
}
```

---

## 9. Troubleshooting

### 9.1. Common Issues
- **Token Verification Fails:** Check secret key and algorithm
- **Password Verification Fails:** Verify hash format and algorithm
- **Token Expiration:** Check token expiration settings
- **Algorithm Mismatch:** Ensure consistent algorithm usage

### 9.2. Debugging
- Enable security logging
- Check token payload
- Verify password hashes
- Monitor authentication attempts

---

This documentation provides comprehensive coverage of the Security system. For specific implementation details, refer to the source code in `src/Tools/Security/`.
