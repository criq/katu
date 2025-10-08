# Cache System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Cache system in the KATU framework. The cache system provides multiple caching backends, runtime memory caching, and advanced caching strategies.

---

## 1. System Overview

### 1.1. Purpose
- **Multi-Backend Support:** APC, File, Memcached, Redis caching
- **Runtime Caching:** In-memory caching for performance
- **Cache Management:** Cache invalidation, expiration, and cleanup
- **Performance Optimization:** Reduce database and external service calls
- **Cache Strategies:** Different caching patterns for different use cases

### 1.2. Architecture
- **Core Classes:** `General`, `Runtime`, `Ledger`, `Pickle`
- **Adapters:** APC, File, Memcached, Redis adapters
- **Storage:** File-based and memory-based storage
- **Serialization:** Pickle-based object serialization
- **Management:** Cache ledger and key management

---

## 2. Core Classes

### 2.1. General (`Katu\Cache\General`)
**Location:** `General.php`

Main cache interface with adapter support:

```php
// Key methods:
public function __construct(?Adapter $adapter = null)
public function setAdapter(?Adapter $adapter): General
public function getAdapter(): ?Adapter
public function get(string $key, $default = null)
public function set(string $key, $value, ?int $ttl = null): bool
public function delete(string $key): bool
public function clear(): bool
public function has(string $key): bool
public function getMultiple(array $keys, $default = null): array
public function setMultiple(array $values, ?int $ttl = null): bool
public function deleteMultiple(array $keys): bool
```

**Key Features:**
- Adapter-based caching
- Multiple key operations
- TTL support
- Default value handling
- Cache existence checking

### 2.2. Runtime (`Katu\Cache\Runtime`)
**Location:** `Runtime.php`

In-memory runtime caching:

```php
// Key methods:
public function get(string $key, $default = null)
public function set(string $key, $value, ?int $ttl = null): bool
public function delete(string $key): bool
public function clear(): bool
public function has(string $key): bool
public function getMultiple(array $keys, $default = null): array
public function setMultiple(array $values, ?int $ttl = null): bool
public function deleteMultiple(array $keys): bool
```

**Key Features:**
- In-memory storage
- Request-scoped caching
- High performance
- Automatic cleanup

### 2.3. Ledger (`Katu\Cache\Ledger`)
**Location:** `Ledger.php`

Cache key management and tracking:

```php
// Key methods:
public function addKey(LedgerKey $key): Ledger
public function getKeys(): LedgerKeyCollection
public function hasKey(string $key): bool
public function removeKey(string $key): Ledger
public function clear(): Ledger
```

**Key Features:**
- Key tracking
- Cache management
- Key collections
- Cleanup operations

### 2.4. Pickle (`Katu\Cache\Pickle`)
**Location:** `Pickle.php`

Object serialization for caching:

```php
// Key methods:
public static function serialize($value): string
public static function unserialize(string $value)
public static function isSerialized(string $value): bool
```

**Key Features:**
- Object serialization
- Safe unserialization
- Serialization detection
- Complex object support

---

## 3. Cache Adapters

### 3.1. Adapter Interface (`Adapter.php`)
**Location:** `Adapter.php`

Base interface for all cache adapters:

```php
// Key methods:
public function get(string $key, $default = null);
public function set(string $key, $value, ?int $ttl = null): bool;
public function delete(string $key): bool;
public function clear(): bool;
public function has(string $key): bool;
public function getMultiple(array $keys, $default = null): array;
public function setMultiple(array $values, ?int $ttl = null): bool;
public function deleteMultiple(array $keys): bool;
```

### 3.2. APC Adapter (`Adapters/APC.php`)
**Location:** `Adapters/APC.php`

APC (Alternative PHP Cache) adapter:

```php
$adapter = new APC();
$cache = new General($adapter);
```

**Key Features:**
- Shared memory caching
- High performance
- PHP extension based
- Automatic cleanup

### 3.3. File Adapter (`Adapters/File.php`)
**Location:** `Adapters/File.php`

File-based caching adapter:

```php
$adapter = new File("/path/to/cache/directory");
$cache = new General($adapter);
```

**Key Features:**
- File system storage
- Persistent across requests
- Configurable directory
- Automatic file management

### 3.4. Memcached Adapter (`Adapters/Memcached.php`)
**Location:** `Adapters/Memcached.php`

Memcached server adapter:

```php
$adapter = new Memcached("localhost", 11211);
$cache = new General($adapter);
```

**Key Features:**
- Distributed caching
- Network-based storage
- Multiple server support
- High availability

### 3.5. Redis Adapter (`Adapters/Redis.php`)
**Location:** `Adapters/Redis.php`

Redis server adapter:

```php
$adapter = new Redis("localhost", 6379);
$cache = new General($adapter);
```

**Key Features:**
- Redis server integration
- Advanced data structures
- Persistence support
- Clustering support

---

## 4. Usage Patterns

### 4.1. Basic Caching
```php
use Katu\Cache\General;
use Katu\Cache\Adapters\File;

// Create cache with file adapter
$cache = new General(new File("/tmp/cache"));

// Set cache value
$cache->set("user_123", $userData, 3600); // 1 hour TTL

// Get cache value
$userData = $cache->get("user_123");

// Check if key exists
if ($cache->has("user_123")) {
    echo "User data is cached";
}

// Delete cache
$cache->delete("user_123");
```

### 4.2. Runtime Caching
```php
use Katu\Cache\Runtime;

// Create runtime cache
$runtime = new Runtime();

// Cache expensive computation
$result = $runtime->get("expensive_calculation", function() {
    return performExpensiveCalculation();
});

// Multiple operations
$runtime->setMultiple([
    "key1" => "value1",
    "key2" => "value2"
], 1800); // 30 minutes TTL

$values = $runtime->getMultiple(["key1", "key2"]);
```

### 4.3. Cache with Ledger
```php
use Katu\Cache\General;
use Katu\Cache\Ledger;
use Katu\Cache\LedgerKey;

// Create cache with ledger
$cache = new General();
$ledger = new Ledger();

// Add keys to ledger
$ledger->addKey(new LedgerKey("user_123", "user_cache"));
$ledger->addKey(new LedgerKey("user_456", "user_cache"));

// Cache with tracking
$cache->set("user_123", $userData);
$cache->set("user_456", $otherUserData);

// Get all tracked keys
$keys = $ledger->getKeys();
foreach ($keys as $key) {
    if ($key->getGroup() === "user_cache") {
        $cache->delete($key->getKey());
    }
}
```

### 4.4. Object Serialization
```php
use Katu\Cache\Pickle;

// Serialize complex object
$user = new User();
$user->setName("John Doe");
$user->setEmail("john@example.com");

$serialized = Pickle::serialize($user);
$cache->set("user_object", $serialized);

// Unserialize object
$serialized = $cache->get("user_object");
$user = Pickle::unserialize($serialized);
```

### 4.5. Cache Strategies
```php
// Cache-aside pattern
function getUserData($userId) {
    $cache = new General();
    $key = "user_{$userId}";

    // Try cache first
    $userData = $cache->get($key);
    if ($userData !== null) {
        return $userData;
    }

    // Load from database
    $userData = loadUserFromDatabase($userId);

    // Cache for future use
    $cache->set($key, $userData, 3600);

    return $userData;
}

// Write-through pattern
function updateUserData($userId, $data) {
    $cache = new General();
    $key = "user_{$userId}";

    // Update database
    updateUserInDatabase($userId, $data);

    // Update cache
    $cache->set($key, $data, 3600);
}

// Write-behind pattern
function updateUserDataAsync($userId, $data) {
    $cache = new General();
    $key = "user_{$userId}";

    // Update cache immediately
    $cache->set($key, $data, 3600);

    // Queue database update
    queueDatabaseUpdate($userId, $data);
}
```

---

## 5. Advanced Features

### 5.1. Cache Invalidation
```php
// Tag-based invalidation
function invalidateByTag($tag) {
    $ledger = new Ledger();
    $cache = new General();

    $keys = $ledger->getKeys();
    foreach ($keys as $key) {
        if ($key->getTag() === $tag) {
            $cache->delete($key->getKey());
            $ledger->removeKey($key->getKey());
        }
    }
}

// Time-based invalidation
function invalidateExpired() {
    $cache = new General();
    $ledger = new Ledger();

    $keys = $ledger->getKeys();
    foreach ($keys as $key) {
        if ($key->isExpired()) {
            $cache->delete($key->getKey());
            $ledger->removeKey($key->getKey());
        }
    }
}
```

### 5.2. Cache Warming
```php
function warmCache() {
    $cache = new General();

    // Pre-load frequently accessed data
    $users = getFrequentlyAccessedUsers();
    foreach ($users as $user) {
        $cache->set("user_{$user->getId()}", $user, 3600);
    }

    // Pre-load configuration
    $config = getApplicationConfig();
    $cache->set("app_config", $config, 7200);
}
```

### 5.3. Cache Statistics
```php
function getCacheStats() {
    $cache = new General();
    $ledger = new Ledger();

    $stats = [
        "total_keys" => count($ledger->getKeys()),
        "hit_rate" => calculateHitRate(),
        "memory_usage" => getMemoryUsage(),
        "expired_keys" => countExpiredKeys()
    ];

    return $stats;
}
```

---

## 6. Configuration

### 6.1. Adapter Configuration
```php
// File adapter configuration
$fileAdapter = new File("/var/cache/app", 0755);

// Redis adapter configuration
$redisAdapter = new Redis("localhost", 6379, 0, "password");

// Memcached adapter configuration
$memcachedAdapter = new Memcached("localhost", 11211);
```

### 6.2. Cache Configuration
```php
// Default TTL configuration
$cache = new General();
$cache->setDefaultTtl(3600); // 1 hour

// Namespace configuration
$cache = new General();
$cache->setNamespace("app_v1");
```

---

## 7. Best Practices

### 7.1. Key Naming
- Use descriptive, hierarchical keys
- Include version information
- Use consistent naming conventions
- Avoid special characters

### 7.2. TTL Management
- Set appropriate TTL values
- Use different TTLs for different data types
- Consider data volatility
- Implement cache warming

### 7.3. Memory Management
- Monitor memory usage
- Implement cache size limits
- Use appropriate adapters
- Clean up expired keys

### 7.4. Error Handling
- Handle cache failures gracefully
- Implement fallback mechanisms
- Log cache errors
- Monitor cache performance

---

## 8. Integration Examples

### 8.1. Model Integration
```php
class User extends Model
{
    public function getCachedData(): array
    {
        $cache = new General();
        $key = "user_data_{$this->getId()}";

        $data = $cache->get($key);
        if ($data === null) {
            $data = $this->loadData();
            $cache->set($key, $data, 3600);
        }

        return $data;
    }
}
```

### 8.2. Controller Integration
```php
class UserController extends Controller
{
    public function getUser(ServerRequestInterface $request): ResponseInterface
    {
        $cache = new General();
        $userId = $request->getAttribute("userId");
        $key = "user_{$userId}";

        $user = $cache->get($key);
        if ($user === null) {
            $user = User::get($userId);
            $cache->set($key, $user, 1800);
        }

        return $this->jsonResponse($user);
    }
}
```

---

## 9. Troubleshooting

### 9.1. Common Issues
- **Cache Misses:** Check key naming and TTL settings
- **Memory Issues:** Monitor memory usage and implement limits
- **Serialization Errors:** Ensure objects are serializable
- **Adapter Failures:** Check adapter configuration and connectivity

### 9.2. Debugging
- Enable cache logging
- Monitor cache hit rates
- Check adapter status
- Verify key existence

---

This documentation provides comprehensive coverage of the Cache system. For specific implementation details, refer to the source code in `src/Cache/`.
