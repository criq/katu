# Storage System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Storage system in the KATU framework. The storage system provides a unified abstraction for file storage across multiple backends (local filesystem, Google Cloud Storage, etc.) with consistent APIs, URI-based access, and efficient caching.

---

## 1. System Overview

### 1.1. Purpose
- **Abstract Storage:** Unified interface for different storage backends (local, GCS, etc.)
- **File Operations:** Read, write, delete, and list files consistently
- **URI-Based Access:** Objects can be created from URIs (`local://`, `gcs://`)
- **Efficient Caching:** Lazy loading and caching of metadata and file contents
- **Streaming Support:** PSR-7 StreamInterface for memory-efficient processing
- **Path Abstraction:** Flat prefix-based paths (consistent across hierarchical and flat storage)

### 1.2. Architecture
- **Core Classes:** `StorageService` (manages storage backend), `StorageObject` (represents individual files)
- **Service Pattern:** Service manages listing and path operations; Objects handle individual file operations
- **Collections:** `StorageServiceCollection` (multiple services), `StorageObjectCollection` (multiple objects)
- **Implementations:** `LocalStorageService`/`LocalStorageObject` (filesystem), `GoogleCloudStorageService`/`GoogleCloudStorageObject` (GCS)

---

## 2. Core Storage Classes

### 2.1. StorageService (`Katu\Storage\StorageService`)
**Location:** `StorageService.php`

Abstract base class for storage services:

```php
// Abstract methods:
abstract public function deleteByPath(string $path): bool
abstract public function getIsCompatibleWithURI(string $uri): bool
abstract public function getObjectByURI(string $uri): StorageObject
abstract public function getObjectIterator(?string $prefix = null): iterable
abstract public function readPath(string $path): string
abstract public function writePath(string $path, string $contents): void

// Concrete methods:
public function getObjects(?string $prefix = null): StorageObjectCollection
```

**Key Features:**
- Manages storage backend connection
- Provides iterator for listing objects (with optional prefix filter)
- Path-based operations (read, write, delete)
- URI compatibility checking and object creation
- Flat prefix-based iteration (treats paths as prefixes, not hierarchical)

### 2.2. StorageObject (`Katu\Storage\StorageObject`)
**Location:** `StorageObject.php`

Abstract base class for storage objects (individual files):

```php
// Abstract methods:
abstract public function delete(): bool
abstract public function exists(): bool
abstract public function getFile(): \Katu\Files\File
abstract public function getSize(): \Katu\Types\TFileSize
abstract public function getStream(): \Psr\Http\Message\StreamInterface
abstract public function getType(): ?string  // MIME type
abstract public function getURI(): string
abstract public function isReadable(): bool
abstract public function isWritable(): bool
abstract public function read(): string
abstract public function write(string $contents): StorageObject

// Concrete methods:
public function getName(): string  // basename of path
public function getPath(): string
public function getService(): StorageService
```

**Key Features:**
- Represents individual files/objects
- URI generation (scheme://path format)
- File operations (read, write, delete)
- Metadata access (size, type, permissions)
- Local file caching and streaming support
- MIME type detection

---

## 3. Storage Service Implementations

### 3.1. LocalStorageService (`Katu\Storage\Services\LocalStorageService`)
**Location:** `Services/LocalStorageService.php`

Local filesystem storage service:

```php
use Katu\Storage\Services\LocalStorageService;

// Create service
$service = new LocalStorageService("/var/storage");

// List objects
foreach ($service->getObjectIterator("prefix/") as $object) {
    echo $object->getPath();
}

// Get objects as collection
$objects = $service->getObjects("prefix/");

// Path operations
$service->writePath("file.txt", "content");
$content = $service->readPath("file.txt");
$service->deleteByPath("file.txt");

// URI operations
if ($service->getIsCompatibleWithURI("local://path/to/file.txt")) {
    $object = $service->getObjectByURI("local://path/to/file.txt");
}
```

**Key Features:**
- Manages a root directory
- Recursive iteration with prefix filtering
- Path normalization (handles both `/` and `\`)
- Automatic directory creation on write
- Relative paths (paths are relative to service root)

### 3.2. GoogleCloudStorageService (`Katu\Storage\Services\GoogleCloudStorageService`)
**Location:** `Services/GoogleCloudStorageService.php`

Google Cloud Storage service (single bucket):

```php
use Katu\Storage\Services\GoogleCloudStorageService;
use Google\Cloud\Storage\Bucket;

// Create service
$bucket = (new \App\Classes\ThirdParty\Google\Storage)->getBucket("bucket-name");
$service = new GoogleCloudStorageService($bucket);

// List objects
foreach ($service->getObjectIterator("prefix/") as $object) {
    echo $object->getPath();
}

// Path operations
$service->writePath("path/to/file.txt", "content");
$content = $service->readPath("path/to/file.txt");
$service->deleteByPath("path/to/file.txt");

// URI operations
if ($service->getIsCompatibleWithURI("gcs://bucket-name/path/to/file.txt")) {
    $object = $service->getObjectByURI("gcs://bucket-name/path/to/file.txt");
}
```

**Key Features:**
- Single bucket per service instance
- Efficient API calls (uses `fields` parameter to fetch only needed metadata)
- Flat prefix-based listing (no delimiter, treats paths as prefixes)
- Preloads metadata during iteration (name, contentType, size, bucket)
- Lazy loading for underlying GCS StorageObject

---

## 4. Storage Object Implementations

### 4.1. LocalStorageObject (`Katu\Storage\Services\LocalStorageObject`)
**Location:** `Services/LocalStorageObject.php`

Local filesystem file representation:

```php
// Created via service
$object = $service->getObjectByURI("local://path/to/file.txt");

// Or during iteration
foreach ($service->getObjectIterator() as $object) {
    // Access methods
    $uri = $object->getURI();           // "local://path/to/file.txt"
    $type = $object->getType();         // MIME type (e.g., "image/jpeg")
    $size = $object->getSize();         // TFileSize object
    $name = $object->getName();         // "file.txt"
    $path = $object->getPath();         // "path/to/file.txt"

    // Operations
    $exists = $object->exists();
    $isReadable = $object->isReadable();
    $isWritable = $object->isWritable();

    // Content
    $content = $object->read();
    $object->write("new content");
    $object->delete();

    // File/Stream access
    $file = $object->getFile();         // \Katu\Files\File instance
    $stream = $object->getStream();     // PSR-7 StreamInterface
}
```

**Key Features:**
- Direct filesystem access (no caching needed)
- MIME type detection using `finfo`
- Standard file operations with permission checking

### 4.2. GoogleCloudStorageObject (`Katu\Storage\Services\GoogleCloudStorageObject`)
**Location:** `Services/GoogleCloudStorageObject.php`

Google Cloud Storage object representation:

```php
// Created via service
$object = $service->getObjectByURI("gcs://bucket-name/path/to/file.jpg");

// Or during iteration (with preloaded metadata)
foreach ($service->getObjectIterator() as $object) {
    // Access methods
    $uri = $object->getURI();           // "gcs://bucket-name/path/to/file.jpg"
    $type = $object->getType();         // MIME type from metadata
    $size = $object->getSize();         // TFileSize object
    $name = $object->getName();         // "file.jpg"
    $path = $object->getPath();         // "path/to/file.jpg"

    // Operations
    $exists = $object->exists();
    $isReadable = $object->isReadable();
    $isWritable = $object->isWritable();

    // Content
    $content = $object->read();         // Downloads content
    $object->write("new content");      // Uploads content, invalidates cache
    $object->delete();                  // Deletes object, invalidates cache

    // File/Stream access
    $file = $object->getFile();         // Downloads and caches locally
    $stream = $object->getStream();     // Streams from GCS or cached file

    // GCS-specific
    $isPublic = $object->getIsPublic(); // Checks ACL
    $publicURL = $object->getPublicURL(); // Returns TURL if public
}
```

**Key Features:**
- Lazy loading: Metadata loaded on first access (cached after)
- Efficient iteration: Metadata preloaded during `getObjectIterator()`
- Local file caching: `getFile()` downloads and caches to temp directory
- Streaming: `getStream()` streams directly from GCS or uses cached file
- Cache invalidation: Cache cleared on write/delete operations
- ACL support: `getIsPublic()` checks object ACL (cached after first check)
- Public URL: Generates public URL if object is publicly accessible

---

## 4. URI System

### 4.1. URI Formats
- **Local:** `local://path/to/file.txt`
- **GCS:** `gcs://bucket-name/path/to/file.jpg`

### 4.2. URI Operations
```php
// Check compatibility
$isCompatible = $service->getIsCompatibleWithURI("gcs://bucket-name/file.txt");

// Create object from URI
$object = $service->getObjectByURI("gcs://bucket-name/file.txt");

// Using collection (automatically finds compatible service)
$services = new StorageServiceCollection([
    new LocalStorageService("/var/storage"),
    new GoogleCloudStorageService($bucket),
]);
$object = $services->getObjectFromURI("gcs://bucket-name/file.txt");
```

---

## 5. Usage Patterns

### 5.1. Basic File Operations
```php
use Katu\Storage\Services\LocalStorageService;
use Katu\Storage\StorageServiceCollection;

// Create service
$service = new LocalStorageService("/var/storage");

// Write file
$service->writePath("documents/file.txt", "File content");

// Read file
$content = $service->readPath("documents/file.txt");

// Get object and operate on it
$object = $service->getObjectByURI("local://documents/file.txt");
if ($object->exists()) {
    $size = $object->getSize();
    $type = $object->getType();
    $content = $object->read();
}

// Delete file
$object->delete();
// Or: $service->deleteByPath("documents/file.txt");
```

### 5.2. Listing Objects
```php
// List all objects
foreach ($service->getObjectIterator() as $object) {
    echo "Path: " . $object->getPath() . "\n";
    echo "Size: " . $object->getSize() . "\n";
    echo "Type: " . $object->getType() . "\n";
}

// List with prefix filter
foreach ($service->getObjectIterator("images/") as $object) {
    // Only objects with paths starting with "images/"
    echo $object->getPath() . "\n";
}

// Get as collection
$objects = $service->getObjects("prefix/");
foreach ($objects as $object) {
    // Process object
}
```

### 5.3. URI-Based Object Creation
```php
use Katu\Storage\StorageServiceCollection;

// Create collection of services
$services = new StorageServiceCollection([
    new LocalStorageService(\App\App::getFileDir()),
    new GoogleCloudStorageService($gcsBucket),
]);

// Get object from URI (automatically finds compatible service)
$object = $services->getObjectFromURI("gcs://jidelniplan/PRODUCTION/RECIPE_VERSION_FILES/2025/08/08/file.png");

if ($object) {
    echo "Size: " . $object->getSize();
    echo "URI: " . $object->getURI();
}
```

### 5.4. Streaming Large Files
```php
// Memory-efficient streaming
$stream = $object->getStream();

// Option 1: Read all at once
$content = $stream->getContents();

// Option 2: Process in chunks
while (!$stream->eof()) {
    $chunk = $stream->read(8192); // 8KB chunks
    // Process chunk...
}

$stream->close();

// Stream directly to HTTP response
$response->getBody()->write($stream->getContents());
```

### 5.5. Local File Access
```php
// Get local file (for GCS, downloads and caches)
$file = $object->getFile();

// Use with other KATU file utilities
$image = new \Katu\Tools\Images\Image($file);
$content = $file->get();
```

### 5.6. GCS-Specific Features
```php
// Check if object is public
if ($object->getIsPublic()) {
    $publicURL = $object->getPublicURL();
    echo "Public URL: " . $publicURL;
}

// Access preloaded metadata (if created from iterator)
$info = $object->getStorageObjectInfo(); // Returns full GCS metadata array
```

---

## 6. Performance Considerations

### 6.1. Lazy Loading
- **Metadata:** Loaded on first access, cached for subsequent calls
- **Local File:** GCS objects download and cache on first `getFile()` call
- **ACL:** GCS `getIsPublic()` checks ACL on first call, cached after

### 6.2. Efficient Iteration
- **GCS:** Uses `fields` parameter to fetch only needed metadata (name, contentType, size, bucket)
- **Preloading:** Objects created during iteration have metadata preloaded
- **No Recursion:** Both services use flat prefix-based iteration

### 6.3. Streaming vs Caching
- **`getStream()`:** Streams directly from source (memory efficient, good for large files)
- **`getFile()`:** Downloads and caches locally (good for multiple operations on same file)
- **Smart Caching:** `getStream()` uses cached file if available, otherwise streams from source

### 6.4. Cache Invalidation
- **Write Operations:** Cache invalidated (metadata and local file)
- **Delete Operations:** Cache invalidated
- **Read Operations:** Uses cached data when available

---

## 7. Advanced Features

### 7.1. Service Collection Usage
```php
use Katu\Storage\StorageServiceCollection;

$services = new StorageServiceCollection([
    new LocalStorageService("/var/storage"),
    new GoogleCloudStorageService($gcsBucket),
]);

// Automatically finds compatible service for URI
$object = $services->getObjectFromURI("gcs://bucket/file.txt");
if ($object) {
    // Object from appropriate service
}
```

### 7.2. Custom Storage Service
```php
class CustomStorageService extends StorageService
{
    public function getObjectIterator(?string $prefix = null): iterable
    {
        // Yield StorageObject instances
    }

    public function readPath(string $path): string
    {
        // Read logic
    }

    public function writePath(string $path, string $contents): void
    {
        // Write logic
    }

    public function deleteByPath(string $path): bool
    {
        // Delete logic
    }

    public function getIsCompatibleWithURI(string $uri): bool
    {
        // Check if this service can handle the URI
    }

    public function getObjectByURI(string $uri): StorageObject
    {
        // Create and return StorageObject instance
    }
}
```

---

## 8. Path and URI Conventions

### 8.1. Path Format
- **Relative:** Paths are relative to service root (not absolute)
- **Separators:** Forward slashes (`/`) normalized across platforms
- **Prefix-Based:** Paths treated as flat prefixes (no true directory hierarchy in GCS)

### 8.2. URI Format
- **Scheme:** `local://` or `gcs://`
- **Path:** Relative path from service root
- **GCS:** Includes bucket name: `gcs://bucket-name/path/to/file.txt`
- **Local:** Direct path: `local://path/to/file.txt`

### 8.3. Examples
```
# Local URIs
local://r/r/9/1u5v8pcxs6mrxg16hvy2radj6bu27uud.jpg
local://documents/report.pdf

# GCS URIs
gcs://jidelniplan/PRODUCTION/RECIPE_VERSION_FILES/2025/08/08/file.png
gcs://bucket-name/folder/subfolder/file.jpg
```

---

## 9. Best Practices

### 9.1. Service vs Object
- **Use Service:** For listing, path-based operations, URI checking
- **Use Object:** For individual file operations (read, write, delete, metadata)

### 9.2. Performance
- **Iteration:** Use `getObjectIterator()` for large datasets (memory efficient)
- **Streaming:** Use `getStream()` for large files to avoid loading into memory
- **Caching:** Use `getFile()` when you need multiple operations on the same file
- **Prefixes:** Use prefix filtering to limit iteration scope

### 9.3. Error Handling
- **Exceptions:** Service methods throw exceptions on errors
- **Return Values:** Object methods return appropriate types (bool, string, etc.)
- **Validation:** Objects validate service type in their methods

### 9.4. Path Management
- **Consistency:** Always use relative paths (normalized to forward slashes)
- **Prefix Matching:** Treat paths as flat prefixes, not hierarchical directories
- **URI Parsing:** Use service methods (`extractPathFromURI()`) for URI parsing

---

## 10. Integration Examples

### 10.1. Model Integration
```php
class Document extends Model
{
    public function getStorageObject(): ?StorageObject
    {
        if (!$this->uri) {
            return null;
        }

        $services = new StorageServiceCollection([
            new LocalStorageService(\App\App::getFileDir()),
            new GoogleCloudStorageService($this->getGCSBucket()),
        ]);

        return $services->getObjectFromURI($this->uri);
    }

    public function getFileContent(): ?string
    {
        $object = $this->getStorageObject();
        return $object ? $object->read() : null;
    }

    public function setFileContent(string $content): void
    {
        $service = new LocalStorageService(\App\App::getFileDir());
        $object = $service->getObjectByURI($this->uri);
        $object->write($content);
    }
}
```

### 10.2. Controller Integration
```php
class FileController extends Controller
{
    public function downloadFile(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getQueryParams()["uri"] ?? null;
        if (!$uri) {
            throw new \Katu\Exceptions\NotFoundException;
        }

        $services = new StorageServiceCollection([
            new LocalStorageService(\App\App::getFileDir()),
            new GoogleCloudStorageService($this->getGCSBucket()),
        ]);

        $object = $services->getObjectFromURI($uri);
        if (!$object || !$object->exists()) {
            throw new \Katu\Exceptions\NotFoundException;
        }

        // Stream file to response
        $stream = $object->getStream();
        return $response
            ->withHeader("Content-Type", $object->getType() ?: "application/octet-stream")
            ->withHeader("Content-Length", (string)$object->getSize())
            ->withBody($stream);
    }
}
```

### 10.3. Image Processing
```php
// Get file for image processing
$object = $services->getObjectFromURI("gcs://bucket/image.jpg");
$file = $object->getFile();  // Downloads and caches

// Use with image utilities
$image = new \Katu\Tools\Images\Image($file);
$thumbnail = $image->getImageVersion("THUMBNAIL");
```

---

## 11. Troubleshooting

### 11.1. Common Issues
- **Path Not Found:** Check that paths are relative to service root
- **Permission Errors:** Verify filesystem permissions for local storage
- **GCS Errors:** Check bucket permissions and credentials
- **Cache Issues:** Clear cache by recreating object or invalidating manually

### 11.2. Debugging
- **Check URI:** Verify URI format matches expected scheme
- **Service Compatibility:** Use `getIsCompatibleWithURI()` to verify service can handle URI
- **Object Existence:** Always check `exists()` before operations
- **Metadata Loading:** Check if metadata is preloaded (during iteration) or lazy-loaded

---

This documentation provides comprehensive coverage of the Storage system. For specific implementation details, refer to the source code in `src/Storage/`.
