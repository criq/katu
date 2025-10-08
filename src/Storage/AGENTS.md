# Storage System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Storage system in the KATU framework. The storage system provides abstract file storage with multiple adapters, entity management, and seamless integration with the application.

---

## 1. System Overview

### 1.1. Purpose
- **Abstract Storage:** Unified interface for different storage backends
- **File Management:** File operations (read, write, delete, list)
- **Entity System:** Storage entities with metadata
- **Adapter Support:** Multiple storage adapters (Local, S3, etc.)
- **Path Management:** Hierarchical path organization
- **Package Support:** Serialization and deserialization

### 1.2. Architecture
- **Core Classes:** `Storage`, `Entity`, `FileInterface`
- **Adapters:** Local, S3, and other storage adapters
- **Entity Management:** File entities with metadata
- **Path Operations:** Path-based file operations
- **Package System:** Entity serialization

---

## 2. Core Storage Classes

### 2.1. Storage (`Katu\Storage\Storage`)
**Location:** `Storage.php`

Abstract storage interface:

```php
// Key methods:
abstract public function deleteByPath(string $path): bool
abstract public function readPath(string $path)
abstract public function writeToPath(string $path, $contents): Entity
abstract public function listEntities(): iterable
public function getEntity(string $path): ?Entity
public function hasEntity(string $path): bool
public function deleteEntity(Entity $entity): bool
public function writeEntity(Entity $entity, $contents): Entity
public function readEntity(Entity $entity)
```

**Key Features:**
- Abstract storage interface
- Path-based operations
- Entity management
- Package serialization support
- Multiple storage adapters

### 2.2. Entity (`Katu\Storage\Entity`)
**Location:** `Entity.php`

Storage entity representation:

```php
// Key methods:
public function __construct(string $path)
public function getPath(): string
public function getName(): string
public function getExtension(): string
public function getDirectory(): string
public function getSize(): int
public function getModifiedTime(): int
public function getCreatedTime(): int
public function isDirectory(): bool
public function isFile(): bool
public function exists(): bool
public function getPackage(): Package
```

**Key Features:**
- Path management
- File metadata
- Directory detection
- Package serialization
- Existence checking

### 2.3. FileInterface (`Katu\Storage\FileInterface`)
**Location:** `FileInterface.php`

File interface for storage operations:

```php
// Key methods:
public function getPath(): string
public function getContents(): string
public function setContents(string $contents): FileInterface
public function exists(): bool
public function delete(): bool
public function getSize(): int
public function getModifiedTime(): int
```

---

## 3. Storage Adapters

### 3.1. Local Adapter (`Adapters/Local.php`)
**Location:** `Adapters/Local.php`

Local file system storage:

```php
use Katu\Storage\Adapters\Local;

// Create local storage
$storage = new Local("/path/to/storage/directory");

// Basic operations
$storage->writeToPath("file.txt", "Hello World");
$content = $storage->readPath("file.txt");
$storage->deleteByPath("file.txt");

// Entity operations
$entity = $storage->getEntity("file.txt");
if ($entity && $entity->exists()) {
    $content = $storage->readEntity($entity);
}
```

**Key Features:**
- Local file system storage
- Directory creation
- File permissions
- Path validation
- Cross-platform support

### 3.2. S3 Adapter (`Adapters/S3.php`)
**Location:** `Adapters/S3.php`

Amazon S3 storage:

```php
use Katu\Storage\Adapters\S3;

// Create S3 storage
$storage = new S3("bucket-name", "region", "access-key", "secret-key");

// Basic operations
$storage->writeToPath("folder/file.txt", "Hello World");
$content = $storage->readPath("folder/file.txt");
$storage->deleteByPath("folder/file.txt");
```

**Key Features:**
- Amazon S3 integration
- Bucket management
- Region support
- Credential management
- Cloud storage

### 3.3. Custom Adapter
**Location:** `Adapters/`

Creating custom storage adapters:

```php
class CustomAdapter extends Storage
{
    public function deleteByPath(string $path): bool
    {
        // Custom deletion logic
        return $this->customDelete($path);
    }

    public function readPath(string $path)
    {
        // Custom reading logic
        return $this->customRead($path);
    }

    public function writeToPath(string $path, $contents): Entity
    {
        // Custom writing logic
        $this->customWrite($path, $contents);
        return new Entity($path);
    }

    public function listEntities(): iterable
    {
        // Custom listing logic
        return $this->customList();
    }
}
```

---

## 4. Usage Patterns

### 4.1. Basic File Operations
```php
use Katu\Storage\Storage;
use Katu\Storage\Adapters\Local;

// Create storage
$storage = new Local("/var/storage");

// Write file
$entity = $storage->writeToPath("documents/file.txt", "File content");
echo "File written to: " . $entity->getPath();

// Read file
$content = $storage->readPath("documents/file.txt");
echo "File content: " . $content;

// Check if file exists
if ($storage->hasEntity("documents/file.txt")) {
    echo "File exists";
}

// Delete file
$storage->deleteByPath("documents/file.txt");
```

### 4.2. Entity Management
```php
// Get entity
$entity = $storage->getEntity("documents/file.txt");
if ($entity && $entity->exists()) {
    echo "File name: " . $entity->getName();
    echo "File extension: " . $entity->getExtension();
    echo "File size: " . $entity->getSize() . " bytes";
    echo "Modified: " . date("Y-m-d H:i:s", $entity->getModifiedTime());
    echo "Is directory: " . ($entity->isDirectory() ? "Yes" : "No");
}

// Read entity content
$content = $storage->readEntity($entity);
echo "Content: " . $content;

// Write to entity
$storage->writeEntity($entity, "New content");

// Delete entity
$storage->deleteEntity($entity);
```

### 4.3. Directory Operations
```php
// List all entities
$entities = $storage->listEntities();
foreach ($entities as $entity) {
    echo "Path: " . $entity->getPath();
    echo "Type: " . ($entity->isDirectory() ? "Directory" : "File");
    echo "Size: " . $entity->getSize();
}

// Create directory structure
$storage->writeToPath("folder/subfolder/file.txt", "Content");
$storage->writeToPath("folder/another-file.txt", "Another content");

// List directory contents
$folderEntities = array_filter($entities, function($entity) {
    return strpos($entity->getPath(), "folder/") === 0;
});
```

### 4.4. File Upload Handling
```php
class FileUploadHandler
{
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function handleUpload(array $file, string $targetPath): ?Entity
    {
        // Validate file
        if (!$this->validateFile($file)) {
            return null;
        }

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file["name"]);
        $path = $targetPath . "/" . $filename;

        // Read uploaded file
        $content = file_get_contents($file["tmp_name"]);

        // Write to storage
        $entity = $this->storage->writeToPath($path, $content);

        return $entity;
    }

    private function validateFile(array $file): bool
    {
        // Check for upload errors
        if ($file["error"] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Check file size
        if ($file["size"] > 10 * 1024 * 1024) { // 10MB limit
            return false;
        }

        // Check file type
        $allowedTypes = ["image/jpeg", "image/png", "application/pdf"];
        if (!in_array($file["type"], $allowedTypes)) {
            return false;
        }

        return true;
    }

    private function generateUniqueFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $uniqueId = uniqid();

        return $name . "_" . $uniqueId . "." . $extension;
    }
}
```

### 4.5. File Management System
```php
class FileManager
{
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function createDirectory(string $path): bool
    {
        try {
            $this->storage->writeToPath($path . "/.gitkeep", "");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function copyFile(string $sourcePath, string $targetPath): bool
    {
        try {
            $content = $this->storage->readPath($sourcePath);
            $this->storage->writeToPath($targetPath, $content);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function moveFile(string $sourcePath, string $targetPath): bool
    {
        try {
            // Copy file
            if (!$this->copyFile($sourcePath, $targetPath)) {
                return false;
            }

            // Delete original
            $this->storage->deleteByPath($sourcePath);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getFileInfo(string $path): ?array
    {
        $entity = $this->storage->getEntity($path);
        if (!$entity || !$entity->exists()) {
            return null;
        }

        return [
            "path" => $entity->getPath(),
            "name" => $entity->getName(),
            "extension" => $entity->getExtension(),
            "size" => $entity->getSize(),
            "modified" => $entity->getModifiedTime(),
            "created" => $entity->getCreatedTime(),
            "is_directory" => $entity->isDirectory()
        ];
    }
}
```

---

## 5. Advanced Features

### 5.1. Package Serialization
```php
// Serialize entity to package
$entity = $storage->getEntity("file.txt");
$package = $entity->getPackage();

// Store package in database
$packageData = $package->getData();
$database->store("file_packages", $packageData);

// Restore entity from package
$restoredPackage = new Package($packageData);
$restoredEntity = Entity::createFromPackage($restoredPackage);
```

### 5.2. Storage Adapter Switching
```php
class StorageManager
{
    private $adapters = [];
    private $defaultAdapter;

    public function addAdapter(string $name, Storage $adapter): void
    {
        $this->adapters[$name] = $adapter;
    }

    public function setDefaultAdapter(string $name): void
    {
        $this->defaultAdapter = $this->adapters[$name];
    }

    public function getAdapter(string $name = null): Storage
    {
        if ($name && isset($this->adapters[$name])) {
            return $this->adapters[$name];
        }
        return $this->defaultAdapter;
    }

    public function writeToAdapter(string $adapterName, string $path, $content): Entity
    {
        $adapter = $this->getAdapter($adapterName);
        return $adapter->writeToPath($path, $content);
    }
}

// Usage
$manager = new StorageManager();
$manager->addAdapter("local", new Local("/var/storage"));
$manager->addAdapter("s3", new S3("bucket", "region", "key", "secret"));
$manager->setDefaultAdapter("local");

// Write to specific adapter
$entity = $manager->writeToAdapter("s3", "file.txt", "content");
```

### 5.3. File Synchronization
```php
class FileSynchronizer
{
    private $sourceStorage;
    private $targetStorage;

    public function __construct(Storage $source, Storage $target)
    {
        $this->sourceStorage = $source;
        $this->targetStorage = $target;
    }

    public function synchronizeDirectory(string $path): array
    {
        $results = [];
        $entities = $this->sourceStorage->listEntities();

        foreach ($entities as $entity) {
            if (strpos($entity->getPath(), $path) === 0) {
                $result = $this->synchronizeFile($entity);
                $results[] = $result;
            }
        }

        return $results;
    }

    private function synchronizeFile(Entity $entity): array
    {
        $sourcePath = $entity->getPath();
        $targetPath = $sourcePath; // Same path in target

        try {
            // Check if target exists
            $targetEntity = $this->targetStorage->getEntity($targetPath);

            if (!$targetEntity || !$targetEntity->exists()) {
                // Copy file
                $content = $this->sourceStorage->readEntity($entity);
                $this->targetStorage->writeToPath($targetPath, $content);
                return ["action" => "copied", "path" => $sourcePath];
            } else {
                // Check if source is newer
                if ($entity->getModifiedTime() > $targetEntity->getModifiedTime()) {
                    $content = $this->sourceStorage->readEntity($entity);
                    $this->targetStorage->writeToPath($targetPath, $content);
                    return ["action" => "updated", "path" => $sourcePath];
                } else {
                    return ["action" => "skipped", "path" => $sourcePath];
                }
            }
        } catch (Exception $e) {
            return ["action" => "error", "path" => $sourcePath, "error" => $e->getMessage()];
        }
    }
}
```

---

## 6. Configuration

### 6.1. Local Storage Configuration
```php
// Local storage with custom directory
$storage = new Local("/var/app/storage", 0755);

// With custom permissions
$storage->setPermissions(0644);
$storage->setDirectoryPermissions(0755);
```

### 6.2. S3 Storage Configuration
```php
// S3 storage with custom settings
$storage = new S3("my-bucket", "us-east-1", "access-key", "secret-key");
$storage->setPrefix("app-files/");
$storage->setRegion("us-west-2");
```

---

## 7. Best Practices

### 7.1. Path Management
- Use consistent path separators
- Implement path validation
- Avoid path traversal attacks
- Use hierarchical organization

### 7.2. Error Handling
- Handle storage exceptions
- Implement fallback mechanisms
- Log storage operations
- Validate file operations

### 7.3. Performance
- Use appropriate storage adapters
- Implement caching for frequently accessed files
- Optimize file operations
- Monitor storage usage

### 7.4. Security
- Validate file types and sizes
- Implement access controls
- Use secure file permissions
- Sanitize file paths

---

## 8. Integration Examples

### 8.1. Model Integration
```php
class Document extends Model
{
    public function getFileEntity(): ?Entity
    {
        if (!$this->filePath) {
            return null;
        }

        $storage = $this->getStorage();
        return $storage->getEntity($this->filePath);
    }

    public function getFileContent(): ?string
    {
        $entity = $this->getFileEntity();
        if (!$entity) {
            return null;
        }

        $storage = $this->getStorage();
        return $storage->readEntity($entity);
    }

    public function setFileContent(string $content): void
    {
        $storage = $this->getStorage();
        $entity = $storage->writeToPath($this->filePath, $content);
        $this->filePath = $entity->getPath();
    }
}
```

### 8.2. Controller Integration
```php
class FileController extends Controller
{
    public function uploadFile(ServerRequestInterface $request): ResponseInterface
    {
        $uploadedFile = $request->getUploadedFiles()["file"] ?? null;
        if (!$uploadedFile) {
            return $this->errorResponse("No file uploaded");
        }

        $handler = new FileUploadHandler($this->getStorage());
        $entity = $handler->handleUpload($uploadedFile->toArray(), "uploads");

        if (!$entity) {
            return $this->errorResponse("File upload failed");
        }

        return $this->jsonResponse([
            "success" => true,
            "file_path" => $entity->getPath(),
            "file_size" => $entity->getSize()
        ]);
    }
}
```

---

## 9. Common Patterns

### 9.1. File Upload Pattern
```php
// Complete file upload with storage
$upload = new Upload($_FILES["file"]);
if ($upload->isValid()) {
    $storage = new FilesystemStorage("/uploads");
    $entity = $storage->writeToPath($upload->getName(), $upload->getContents());

    // Store entity reference in database
    $file = new File();
    $file->path = $entity->getPath();
    $file->size = $entity->getSize();
    $file->persist();
}
```

### 9.2. Cloud Storage Pattern
```php
// Google Cloud Storage integration
$storage = new GoogleCloudStorage([
    "project_id" => "my-project",
    "bucket" => "my-bucket"
]);

$entity = $storage->writeToPath("documents/file.pdf", $content);
$url = $entity->getURL();
```

### 9.3. Storage Abstraction
```php
// Storage abstraction for different adapters
class FileManager
{
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    public function storeFile(string $path, $content): Entity
    {
        return $this->storage->writeToPath($path, $content);
    }
}
```

---

## 10. Troubleshooting

### 10.1. Common Issues
- **Permission Errors:** Check file system permissions
- **Path Issues:** Verify path format and existence
- **Storage Failures:** Check adapter configuration
- **Memory Issues:** Monitor file sizes and memory usage

### 10.2. Debugging
- Enable storage logging
- Check adapter status
- Verify file operations
- Monitor storage performance

---

This documentation provides comprehensive coverage of the Storage system. For specific implementation details, refer to the source code in `src/Storage/`.
