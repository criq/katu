# File Management System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the File Management system in the KATU framework. The file system provides comprehensive file handling, upload management, file collections, and file format support.

---

## 1. System Overview

### 1.1. Purpose
- **File Operations:** Complete file system operations (create, read, update, delete)
- **Upload Management:** HTTP file upload handling with validation
- **File Collections:** Collection management for multiple files
- **Format Support:** Various file format handling and processing
- **Temporary Files:** Temporary file management and cleanup
- **URL Generation:** File URL generation with caching support
- **Stream Integration:** PSR-7 stream interface support
- **Image Support:** Image file detection and processing

### 1.2. Architecture
- **Core Classes:** `File`, `FileCollection`, `Upload`, `UploadCollection`
- **Format Classes:** Specialized format handlers
- **Temporary Files:** Temporary file management
- **Stream Support:** PSR-7 stream interface integration
- **URL System:** File URL generation and caching
- **Collection System:** File collection management

---

## 2. Core File Classes

### 2.1. File (`Katu\Files\File`)
**Location:** `File.php`

Main file handling class with comprehensive functionality:

```php
// Key methods:
public function __construct()
public function setPath(): File
public function getPath(): string
public function getRelativePath(): string
public function exists(): bool
public function get(): mixed
public function set($data): bool
public function append($data): bool
public function getType(): ?string
public function getSize(): ?\Katu\Types\TFileSize
public function getMime(): ?string
public function getFilename(): ?string
public function getExtension(): ?string
public function getDir(): File
public function getBasename(): string
public function getFiles(): FileCollection
public function getDirs(): array
public function isFile(): bool
public function isDir(): bool
public function isPhpFile(): bool
public function isReadable(): bool
public function isWritable(): bool
public function makeDir($mode = 0777, $recursive = true): bool
public function touch(): File
public function chmod($mode): bool
public function copy(File $destination): File
public function move(File $destination): bool
public function delete(): bool
public function getModifiedTime(): ?\Katu\Tools\Calendar\Time
public function getHash($function = "sha1"): string
public function getURL(): ?\Katu\Types\TURL
public function getHashedURL(?string $algo = "sha1", ?string $paramName = "hash"): \Katu\Types\TURL
public function getStream(): \Psr\Http\Message\StreamInterface
```

**Key Features:**
- Complete file system operations
- Path management and manipulation
- File type detection
- MIME type detection
- Directory operations
- File copying and moving
- Hash generation
- URL generation with caching
- PSR-7 stream support
- Image file detection

### 2.2. FileCollection (`Katu\Files\FileCollection`)
**Location:** `FileCollection.php`

Collection for managing multiple files:

```php
// Key methods:
public function filterByExtension(string $extension): FileCollection
public function filterByRegex(string $regex): FileCollection
public function getFirst(): ?File
```

**Key Features:**
- File filtering by extension
- Regex-based filtering
- Collection management
- First file retrieval

### 2.3. Upload (`Katu\Files\Upload`)
**Location:** `Upload.php`

HTTP file upload handling:

```php
// Key methods:
public function __construct(UploadedFileInterface $uploadedFile)
public function getError(): ?int
public function isInError(): bool
public function getErrorMessage(): ?string
public function getErrorId(): ?int
public function getException(): ?\Throwable
public function getFileName(): string
public function getExtension(): ?string
public function getFileSize(): \Katu\Types\TFileSize
public function getFileType(): string
public function isType(array $types): bool
public function getIsSupportedImage(): bool
public function getStream(): \Psr\Http\Message\StreamInterface
public static function getMaxSize(): \Katu\Types\TFileSize
```

**Key Features:**
- PSR-7 upload integration
- Error handling and reporting
- File type validation
- Image support detection
- Size validation
- Stream access

### 2.4. UploadCollection (`Katu\Files\UploadCollection`)
**Location:** `UploadCollection.php`

Collection for managing multiple uploads with filtering and validation capabilities.

---

## 3. File Operations

### 3.1. Basic File Operations
```php
// Create file
$file = new File("/path/to/file.txt");

// Check existence
if ($file->exists()) {
    // File exists
}

// Read file content
$content = $file->get();

// Write file content
$file->set("Hello World");

// Append to file
$file->append("More content");

// Get file info
$size = $file->getSize();
$mime = $file->getMime();
$extension = $file->getExtension();
```

### 3.2. Directory Operations
```php
// Create directory
$dir = new File("/path/to/directory");
$dir->makeDir();

// Get directory contents
$files = $dir->getFiles();
$subdirs = $dir->getDirs();

// Check directory
if ($dir->isDir()) {
    // It's a directory
}
```

### 3.3. File Manipulation
```php
// Copy file
$source = new File("/source/file.txt");
$destination = new File("/destination/file.txt");
$source->copy($destination);

// Move file
$source->move($destination);

// Delete file
$file->delete();

// Change permissions
$file->chmod(0644);
```

---

## 4. Upload Management

### 4.1. Basic Upload Handling
```php
// Handle single upload
$upload = new Upload($uploadedFile);

// Check for errors
if ($upload->isInError()) {
    $errorMessage = $upload->getErrorMessage();
    $errorId = $upload->getErrorId();
}

// Get upload info
$fileName = $upload->getFileName();
$fileSize = $upload->getFileSize();
$fileType = $upload->getFileType();
$extension = $upload->getExtension();
```

### 4.2. Upload Validation
```php
// Check file type
if ($upload->isType(["image/jpeg", "image/png"])) {
    // Valid image type
}

// Check if supported image
if ($upload->getIsSupportedImage()) {
    // Supported image format
}

// Check file size
$maxSize = Upload::getMaxSize();
if ($upload->getFileSize()->getBytes() > $maxSize->getBytes()) {
    // File too large
}
```

### 4.3. Upload Processing
```php
public function processUpload(Upload $upload): File
{
    if ($upload->isInError()) {
        throw new \Katu\Exceptions\InputErrorException($upload->getErrorMessage());
    }

    // Validate file type
    if (!$upload->isType(["image/jpeg", "image/png", "image/gif"])) {
        throw new \Katu\Exceptions\InputErrorException("Invalid file type");
    }

    // Create destination file
    $destination = File::createTemporaryWithExtension($upload->getExtension());

    // Save upload
    $destination->set($upload->getStream()->getContents());

    return $destination;
}
```

---

## 5. File Collections

### 5.1. Collection Operations
```php
// Create file collection
$files = new FileCollection([
    new File("/path/file1.txt"),
    new File("/path/file2.txt"),
    new File("/path/file3.txt")
]);

// Filter by extension
$textFiles = $files->filterByExtension("txt");

// Filter by regex
$imageFiles = $files->filterByRegex("/\.(jpg|png|gif)$/");

// Get first file
$firstFile = $files->getFirst();
```

### 5.2. Collection Processing
```php
public function processFiles(FileCollection $files): array
{
    $results = [];

    foreach ($files as $file) {
        if ($file->isFile()) {
            $results[] = [
                "name" => $file->getFilename(),
                "size" => $file->getSize(),
                "type" => $file->getMime()
            ];
        }
    }

    return $results;
}
```

---

## 6. Temporary Files

### 6.1. Temporary File Creation
```php
// Create temporary file with specific name
$tempFile = File::createTemporaryWithFileName("myfile_{8}.txt");

// Create temporary file with extension
$tempFile = File::createTemporaryWithExtension("txt");

// Create temporary file from source
$tempFile = File::createTemporaryFromSrc($content, "txt");

// Create temporary file from URL
$tempFile = File::createTemporaryFromURL("https://example.com/file.txt", "txt");
```

### 6.2. Temporary File Management
```php
public function handleTemporaryFile($content): File
{
    // Create temporary file
    $tempFile = File::createTemporaryWithExtension("tmp");

    // Write content
    $tempFile->set($content);

    // Process file
    $this->processFile($tempFile);

    // Clean up (automatic with temporary files)
    return $tempFile;
}
```

---

## 7. URL Generation

### 7.1. Basic URL Generation
```php
// Get file URL
$url = $file->getURL();

// Generate hashed URL
$hashedUrl = $file->getHashedURL("sha1", "hash");

// Get cached hashed URL
$cachedUrl = $file->getCachedHashedURL(new Timeout(3600), "sha1", "hash");
```

### 7.2. URL Caching
```php
public function getFileUrl(File $file): \Katu\Types\TURL
{
    // Use cached URL for performance
    return $file->getCachedHashedURL(
        new Timeout(86400), // 24 hours
        "sha1",
        "hash"
    );
}
```

---

## 8. File Format Support

### 8.1. Image File Support
```php
// Check if file is supported image
if ($file->getIsSupportedImage()) {
    // Process as image
}

// Get supported image types
$supportedTypes = File::getSupportedImageTypes();
// Returns: ["image/gif", "image/jpeg", "image/png", "image/webp"]
```

### 8.2. File Type Detection
```php
// Get MIME type
$mimeType = $file->getMime();

// Get extension
$extension = $file->getExtension();

// Check if PHP file
if ($file->isPhpFile()) {
    // Include PHP file
    $file->includeOnce();
}
```

---

## 9. Stream Integration

### 9.1. PSR-7 Stream Support
```php
// Get file stream
$stream = $file->getStream();

// Use with PSR-7 response
$response = $response->withBody($stream);
```

### 9.2. Upload Stream Handling
```php
public function handleUploadStream(Upload $upload): string
{
    $stream = $upload->getStream();
    $content = $stream->getContents();

    // Process stream content
    return $this->processContent($content);
}
```

---

## 10. File Security

### 10.1. File Validation
```php
public function validateFile(File $file): bool
{
    // Check if file exists
    if (!$file->exists()) {
        return false;
    }

    // Check if readable
    if (!$file->isReadable()) {
        return false;
    }

    // Check file size
    if ($file->getSize()->getBytes() > 10 * 1024 * 1024) { // 10MB
        return false;
    }

    // Check file type
    $allowedTypes = ["text/plain", "application/pdf"];
    if (!in_array($file->getMime(), $allowedTypes)) {
        return false;
    }

    return true;
}
```

### 10.2. Secure File Operations
```php
public function secureFileOperation(File $file): bool
{
    try {
        // Validate file
        if (!$this->validateFile($file)) {
            throw new \Katu\Exceptions\FileNotFoundException("Invalid file");
        }

        // Perform secure operation
        $this->processFile($file);

        return true;
    } catch (\Katu\Exceptions\Exception $e) {
        // Log error
        \App\App::getLogger()->error($e);
        return false;
    }
}
```

---

## 11. Best Practices

### 11.1. File Operations
- Always check file existence before operations
- Use appropriate error handling
- Validate file types and sizes
- Use temporary files for processing
- Clean up temporary files

### 11.2. Upload Handling
- Validate upload errors
- Check file types and sizes
- Use secure file names
- Implement proper error messages
- Handle upload limits

### 11.3. Performance Considerations
- Use file caching for frequently accessed files
- Implement proper file cleanup
- Monitor disk space usage
- Use appropriate file permissions
- Optimize file operations

### 11.4. Security Considerations
- Validate all file types
- Check file sizes
- Sanitize file names
- Use secure file paths
- Implement proper access controls

---

## 12. Common Patterns

### 12.1. File Upload Processing
```php
public function processFileUpload(Upload $upload): File
{
    // Validate upload
    if ($upload->isInError()) {
        throw new \Katu\Exceptions\InputErrorException($upload->getErrorMessage());
    }

    // Validate file type
    if (!$upload->isType(["image/jpeg", "image/png"])) {
        throw new \Katu\Exceptions\InputErrorException("Invalid file type");
    }

    // Create destination file
    $destination = File::createTemporaryWithExtension($upload->getExtension());

    // Save file
    $destination->set($upload->getStream()->getContents());

    return $destination;
}
```

### 12.2. File Collection Processing
```php
public function processFileCollection(FileCollection $files): array
{
    $results = [];

    foreach ($files as $file) {
        if ($file->isFile() && $this->validateFile($file)) {
            $results[] = [
                "name" => $file->getFilename(),
                "size" => $file->getSize()->getBytes(),
                "type" => $file->getMime(),
                "url" => $file->getURL()
            ];
        }
    }

    return $results;
}
```

### 12.3. File Cleanup
```php
public function cleanupFiles(FileCollection $files): void
{
    foreach ($files as $file) {
        if ($file->exists()) {
            try {
                $file->delete();
            } catch (\Katu\Exceptions\Exception $e) {
                // Log cleanup errors
                \App\App::getLogger()->warning("Failed to delete file: " . $file->getPath());
            }
        }
    }
}
```

---

## 13. Troubleshooting

### 13.1. Common Issues
- **File Not Found:** Check file paths and permissions
- **Upload Errors:** Validate upload configuration and limits
- **Permission Errors:** Check file and directory permissions
- **Size Limits:** Verify upload size limits

### 13.2. Debugging
- Use `exists()` to check file existence
- Check `isReadable()` and `isWritable()` for permissions
- Monitor file sizes with `getSize()`
- Use logging for file operations

### 13.3. Performance Issues
- Monitor disk space usage
- Implement file cleanup routines
- Use appropriate file caching
- Optimize file operations

---

This documentation provides comprehensive coverage of the KATU File Management system. For specific implementation details, refer to the file classes in `src/Files/` and the application-specific file handling.
