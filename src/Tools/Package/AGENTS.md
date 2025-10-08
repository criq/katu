# Package System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Package system in the KATU framework. The package system provides data serialization, encryption, and transmission capabilities for secure data packaging and exchange.

---

## 1. System Overview

### 1.1. Purpose
- **Data Serialization:** Secure serialization of complex data structures
- **Encryption:** End-to-end encryption for sensitive data
- **Data Transmission:** Secure data exchange between systems
- **Package Management:** Collection and management of multiple packages
- **Interface Compliance:** Standardized package interface implementation
- **JSON Integration:** JSON serialization and deserialization

### 1.2. Architecture
- **Core Classes:** `Package`, `PackageCollection`, `PackagedInterface`
- **Encryption System:** Integration with encryption types
- **JSON Support:** JSON serialization and deserialization
- **Collection Management:** Package collection handling
- **Interface System:** Standardized package interface
- **String Conversion:** Portable string representation

---

## 2. Core Package Classes

### 2.1. Package (`Katu\Tools\Package\Package`)
**Location:** `Package.php`

Main package class for data serialization and encryption:

```php
// Key methods:
public function __construct(array $payload)
public function __toString(): string
public static function createFromJSON(TJSON $json): Package
public static function createFromPortableString(string $string): Package
public function getPayload(): array
public function getJSON(): TJSON
public function getHash(): string
public function getPortableString(): TEncryptedStringJSONPortableString
public function jsonSerialize()
```

**Key Features:**
- Data payload management
- JSON serialization
- Encryption support
- Portable string conversion
- Hash generation
- String conversion

### 2.2. PackageCollection (`Katu\Tools\Package\PackageCollection`)
**Location:** `PackageCollection.php`

Collection for managing multiple packages:

```php
// Key methods:
public static function createFromJSON(TJSON $json): PackageCollection
public function getPayloads(): TPayloadCollection
public function getJSON(): TJSON
```

**Key Features:**
- Package collection management
- JSON serialization
- Payload collection conversion
- Array-based storage

### 2.3. PackagedInterface (`Katu\Tools\Package\PackagedInterface`)
**Location:** `PackagedInterface.php`

Interface for objects that can be packaged:

```php
// Key methods:
public function getPackage(): Package
public static function createFromPackage(Package $package)
```

**Key Features:**
- Standardized package interface
- Package creation
- Package restoration
- Interface compliance

---

## 3. Package Creation

### 3.1. Basic Package Creation
```php
// Create package with data
$data = [
    "user_id" => 123,
    "name" => "John Doe",
    "email" => "john@example.com",
    "settings" => [
        "theme" => "dark",
        "notifications" => true
    ]
];

$package = new Package($data);

// Get package payload
$payload = $package->getPayload();

// Get JSON representation
$json = $package->getJSON();
```

### 3.2. Package from JSON
```php
// Create package from JSON
$json = new TJSON($data);
$package = Package::createFromJSON($json);

// Or from JSON string
$jsonString = '{"user_id": 123, "name": "John Doe"}';
$json = TJSON::createFromContents($jsonString);
$package = Package::createFromJSON($json);
```

### 3.3. Package from Portable String
```php
// Create package from encrypted string
$encryptedString = "encrypted_data_string";
$package = Package::createFromPortableString($encryptedString);

// Get original data
$payload = $package->getPayload();
```

---

## 4. Package Serialization

### 4.1. JSON Serialization
```php
// Get JSON representation
$json = $package->getJSON();
$jsonString = (string)$json;

// Serialize to array
$array = $package->jsonSerialize();

// Get hash for integrity checking
$hash = $package->getHash();
```

### 4.2. Portable String Serialization
```php
// Get encrypted portable string
$portableString = $package->getPortableString();
$encryptedString = (string)$portableString;

// Convert back to package
$restoredPackage = Package::createFromPortableString($encryptedString);
```

### 4.3. String Conversion
```php
// Automatic string conversion
$package = new Package($data);
$string = (string)$package; // Returns encrypted portable string

// Direct conversion
$encryptedString = $package->getPortableString();
```

---

## 5. Package Collections

### 5.1. Collection Management
```php
// Create package collection
$collection = new PackageCollection();

// Add packages
$collection->append(new Package($data1));
$collection->append(new Package($data2));
$collection->append(new Package($data3));

// Get collection JSON
$json = $collection->getJSON();

// Get payloads
$payloads = $collection->getPayloads();
```

### 5.2. Collection from JSON
```php
// Create collection from JSON
$json = new TJSON([
    ["user_id" => 1, "name" => "John"],
    ["user_id" => 2, "name" => "Jane"],
    ["user_id" => 3, "name" => "Bob"]
]);

$collection = PackageCollection::createFromJSON($json);

// Access packages
foreach ($collection as $package) {
    $payload = $package->getPayload();
    echo "User: " . $payload["name"] . "\n";
}
```

---

## 6. PackagedInterface Implementation

### 6.1. Basic Implementation
```php
class User implements PackagedInterface
{
    public $id;
    public $name;
    public $email;

    public function __construct(array $data = [])
    {
        $this->id = $data["id"] ?? null;
        $this->name = $data["name"] ?? null;
        $this->email = $data["email"] ?? null;
    }

    public function getPackage(): Package
    {
        return new Package([
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        return new static($data);
    }
}
```

### 6.2. Advanced Implementation
```php
class Order implements PackagedInterface
{
    public $id;
    public $items;
    public $total;
    public $createdAt;

    public function getPackage(): Package
    {
        return new Package([
            "id" => $this->id,
            "items" => $this->items,
            "total" => $this->total,
            "created_at" => $this->createdAt?->format("c")
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        $order = new static();
        $order->id = $data["id"];
        $order->items = $data["items"];
        $order->total = $data["total"];
        $order->createdAt = $data["created_at"] ? new \DateTime($data["created_at"]) : null;
        return $order;
    }
}
```

---

## 7. Data Transmission

### 7.1. Secure Data Exchange
```php
// Create package with sensitive data
$sensitiveData = [
    "user_id" => 123,
    "api_key" => "secret_key",
    "permissions" => ["read", "write"]
];

$package = new Package($sensitiveData);

// Get encrypted string for transmission
$encryptedString = (string)$package;

// Transmit securely
$this->sendSecureData($encryptedString);
```

### 7.2. Data Reception and Decryption
```php
// Receive encrypted data
$encryptedString = $this->receiveSecureData();

// Decrypt and restore package
$package = Package::createFromPortableString($encryptedString);

// Get original data
$data = $package->getPayload();

// Verify integrity
$expectedHash = $this->getExpectedHash();
$actualHash = $package->getHash();
if ($actualHash !== $expectedHash) {
    throw new \Exception("Data integrity check failed");
}
```

---

## 8. Job System Integration

### 8.1. Job Serialization
```php
class DataProcessingJob extends \Katu\Tools\Jobs\Job implements PackagedInterface
{
    public function getCallback(): callable
    {
        return function() {
            $this->processData();
        };
    }

    public function getPackage(): Package
    {
        return new Package([
            "class" => get_class($this),
            "args" => $this->getArgs(),
            "interval" => (string)$this->getInterval(),
            "timeout" => (string)$this->getTimeout()
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        $job = new static($data["args"]);
        $job->setInterval(new \Katu\Tools\Calendar\Timeout($data["interval"]));
        $job->setTimeout(new \Katu\Tools\Calendar\Timeout($data["timeout"]));
        return $job;
    }
}
```

### 8.2. Job Queue Transmission
```php
// Create job package
$job = new DataProcessingJob(["param" => "value"]);
$package = $job->getPackage();

// Serialize for queue
$serialized = (string)$package;

// Store in queue
$this->queueJob($serialized);

// Retrieve and restore
$serialized = $this->getNextJob();
$package = Package::createFromPortableString($serialized);
$job = DataProcessingJob::createFromPackage($package);

// Execute job
$job->run();
```

---

## 9. Cache Integration

### 9.1. Cached Package Storage
```php
class PackageCache
{
    public function store(string $key, Package $package): void
    {
        $serialized = (string)$package;
        \Katu\Cache\General::set($key, $serialized, new \Katu\Tools\Calendar\Timeout("1 hour"));
    }

    public function retrieve(string $key): ?Package
    {
        $serialized = \Katu\Cache\General::get($key);
        if ($serialized) {
            return Package::createFromPortableString($serialized);
        }
        return null;
    }
}
```

### 9.2. Package with Metadata
```php
class MetadataPackage implements PackagedInterface
{
    public $data;
    public $metadata;

    public function getPackage(): Package
    {
        return new Package([
            "data" => $this->data,
            "metadata" => [
                "created_at" => time(),
                "version" => "1.0",
                "checksum" => $this->getChecksum()
            ]
        ]);
    }

    private function getChecksum(): string
    {
        return md5(serialize($this->data));
    }
}
```

---

## 10. Best Practices

### 10.1. Package Design
- Keep package payloads focused and minimal
- Include necessary metadata for restoration
- Use appropriate data types
- Implement proper validation

### 10.2. Security
- Use encryption for sensitive data
- Implement integrity checking
- Validate package contents
- Handle decryption errors gracefully

### 10.3. Performance
- Minimize package size
- Use efficient serialization
- Implement appropriate caching
- Monitor package processing time

### 10.4. Error Handling
- Implement proper exception handling
- Validate package integrity
- Handle decryption failures
- Provide meaningful error messages

---

## 11. Common Patterns

### 11.1. API Response Packaging
```php
class APIResponse implements PackagedInterface
{
    public $data;
    public $status;
    public $message;

    public function getPackage(): Package
    {
        return new Package([
            "data" => $this->data,
            "status" => $this->status,
            "message" => $this->message,
            "timestamp" => time()
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        $response = new static();
        $response->data = $data["data"];
        $response->status = $data["status"];
        $response->message = $data["message"];
        return $response;
    }
}
```

### 11.2. Configuration Packaging
```php
class Configuration implements PackagedInterface
{
    public $settings;
    public $environment;

    public function getPackage(): Package
    {
        return new Package([
            "settings" => $this->settings,
            "environment" => $this->environment,
            "version" => "1.0"
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        $config = new static();
        $config->settings = $data["settings"];
        $config->environment = $data["environment"];
        return $config;
    }
}
```

### 11.3. Event Packaging
```php
class EventPackage implements PackagedInterface
{
    public $eventName;
    public $data;
    public $timestamp;

    public function getPackage(): Package
    {
        return new Package([
            "event" => $this->eventName,
            "data" => $this->data,
            "timestamp" => $this->timestamp,
            "id" => uniqid()
        ]);
    }

    public static function createFromPackage(Package $package)
    {
        $data = $package->getPayload();
        $event = new static();
        $event->eventName = $data["event"];
        $event->data = $data["data"];
        $event->timestamp = $data["timestamp"];
        return $event;
    }
}
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Decryption Errors:** Check encryption configuration and keys
- **Package Corruption:** Verify data integrity and checksums
- **Serialization Issues:** Check data types and structure
- **Memory Issues:** Monitor package size and memory usage

### 12.2. Debugging
- Use package hash for integrity checking
- Log package creation and restoration
- Monitor serialization performance
- Validate package contents

### 12.3. Performance Issues
- Optimize package payload size
- Use appropriate caching strategies
- Monitor serialization time
- Implement efficient data structures

---

This documentation provides comprehensive coverage of the KATU Package system. For specific implementation details, refer to the package classes in `src/Tools/Package/` and the integration with encryption and JSON systems.
