# Image Processing System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Image Processing system in the KATU framework. The image system provides advanced image manipulation, filtering, versioning, and QR code generation capabilities.

---

## 1. System Overview

### 1.1. Purpose
- **Image Manipulation:** Resize, crop, filter, and transform images
- **Version Management:** Create multiple versions of images with different filters
- **Filter System:** Extensible filter system for image processing
- **QR Code Generation:** Generate QR codes with customizable options
- **Source Management:** Handle images from various sources (files, URLs, models)
- **Storage Integration:** Seamless integration with storage system

### 1.2. Architecture
- **Core Classes:** `Image`, `ImageVersion`, `Version`, `Source`
- **Filter System:** Extensible filter classes with Intervention Image integration
- **Source System:** Multiple source types (File, URL, FileModel)
- **Version System:** Image versioning with different configurations
- **QR Code System:** QR code generation with Endroid QR Code

---

## 2. Core Image Classes

### 2.1. Image (`Katu\Tools\Images\Image`)
**Location:** `Image.php`

The main image processing class:

```php
// Key methods:
public function __construct($input)
public function getSource(): Source
public function getURL(): ?TURL
public function getURI(): ?string
public function getVersion(Version $version): ImageVersion
public function getVersions(): ImageVersionCollection
public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
public function getPackage(): Package
```

**Key Features:**
- Multiple input source support
- URL generation for web access
- Version management
- Package serialization
- REST API integration

### 2.2. ImageVersion (`Katu\Tools\Images\ImageVersion`)
**Location:** `ImageVersion.php`

Image version with specific configuration:

```php
// Key methods:
public function __construct(Image $image, Version $version)
public function getImage(): Image
public function getVersion(): Version
public function getURL(): ?TURL
public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
```

**Key Features:**
- Version-specific image handling
- URL generation for versions
- REST response support

### 2.3. Version (`Katu\Tools\Images\Version`)
**Location:** `Version.php`

Image version configuration:

```php
// Key methods:
public function __construct(string $title, string $extension, ?FilterCollection $filters = null)
public function getTitle(): string
public function getExtension(): string
public function getFilters(): FilterCollection
public function addFilter(Filter $filter): Version
```

**Key Features:**
- Version naming and identification
- File extension management
- Filter collection management
- Configurable processing pipeline

### 2.4. Source (`Katu\Tools\Images\Source`)
**Location:** `Source.php`

Abstract image source handling:

```php
// Key methods:
abstract public function getURI(): ?string
abstract public function getFile(): ?File
public static function createFromInput($input): Source
```

**Key Features:**
- Multiple source type support
- File and URI access
- Input type detection

---

## 3. Image Sources

### 3.1. File Source (`Sources/File.php`)
**Location:** `Sources/File.php`

File-based image source:

```php
$source = new File($filePath);
$image = new Image($source);
```

### 3.2. URL Source (`Sources/URL.php`)
**Location:** `Sources/URL.php`

URL-based image source:

```php
$source = new URL("https://example.com/image.jpg");
$image = new Image($source);
```

### 3.3. FileModel Source (`Sources/FileModel.php`)
**Location:** `Sources/FileModel.php`

Model-based file source:

```php
$source = new FileModel($fileModel);
$image = new Image($source);
```

---

## 4. Image Filters

### 4.1. Filter System (`Filter.php`)
**Location:** `Filter.php`

Abstract base class for image filters:

```php
// Key methods:
abstract public function apply(Image $image): bool
public function setParams(array $params): Filter
public function getParams(): array
```

### 4.2. Available Filters

#### ResizeFilter (`Filters/ResizeFilter.php`)
Resize image with aspect ratio preservation:

```php
$filter = new ResizeFilter();
$filter->setParams([
    'width' => 800,
    'height' => 600,
    'dontUpsize' => true
]);
```

#### FitFilter (`Filters/FitFilter.php`)
Fit image within dimensions:

```php
$filter = new FitFilter();
$filter->setParams([
    'width' => 400,
    'height' => 300
]);
```

#### BlurFilter (`Filters/BlurFilter.php`)
Apply blur effect:

```php
$filter = new BlurFilter();
$filter->setParams(['amount' => 5]);
```

#### SharpenFilter (`Filters/SharpenFilter.php`)
Apply sharpening effect:

```php
$filter = new SharpenFilter();
$filter->setParams(['amount' => 10]);
```

#### ContrastFilter (`Filters/ContrastFilter.php`)
Adjust image contrast:

```php
$filter = new ContrastFilter();
$filter->setParams(['level' => 20]);
```

#### GreyscaleFilter (`Filters/GreyscaleFilter.php`)
Convert to greyscale:

```php
$filter = new GreyscaleFilter();
```

#### InsertFilter (`Filters/InsertFilter.php`)
Insert another image:

```php
$filter = new InsertFilter();
$filter->setParams([
    'image' => $otherImage,
    'x' => 10,
    'y' => 10
]);
```

---

## 5. QR Code Generation

### 5.1. QRCode (`QRCode.php`)
**Location:** `QRCode.php`

QR code generation using Endroid QR Code:

```php
// Key methods:
public function __construct(string $string, ?int $size = 400, ?int $margin = 0)
public function setString(string $string): QRCode
public function setSize(?int $size): QRCode
public function setMargin(?int $margin): QRCode
public function getBase64PNG(): string
public function getDataURI(): string
```

**Key Features:**
- Customizable size and margin
- Base64 PNG output
- Data URI generation
- String content support

---

## 6. Usage Patterns

### 6.1. Basic Image Processing
```php
use Katu\Tools\Images\Image;
use Katu\Tools\Images\Version;
use Katu\Tools\Images\Filters\ResizeFilter;

// Create image from file
$image = new Image("path/to/image.jpg");

// Create version with resize filter
$version = new Version("thumbnail", "jpg");
$resizeFilter = new ResizeFilter();
$resizeFilter->setParams(['width' => 200, 'height' => 200]);
$version->addFilter($resizeFilter);

// Get processed version
$imageVersion = $image->getVersion($version);
$url = $imageVersion->getURL();
```

### 6.2. Multiple Image Versions
```php
// Create different versions
$thumbnailVersion = new Version("thumbnail", "jpg");
$thumbnailVersion->addFilter(new ResizeFilter(['width' => 150, 'height' => 150]));

$mediumVersion = new Version("medium", "jpg");
$mediumVersion->addFilter(new ResizeFilter(['width' => 600, 'height' => 400]));

$greyscaleVersion = new Version("greyscale", "jpg");
$greyscaleVersion->addFilter(new ResizeFilter(['width' => 400, 'height' => 300]));
$greyscaleVersion->addFilter(new GreyscaleFilter());

// Apply versions
$image = new Image("path/to/image.jpg");
$thumbnail = $image->getVersion($thumbnailVersion);
$medium = $image->getVersion($mediumVersion);
$greyscale = $image->getVersion($greyscaleVersion);
```

### 6.3. Complex Filter Chain
```php
// Create version with multiple filters
$version = new Version("processed", "jpg");

// Resize first
$resizeFilter = new ResizeFilter();
$resizeFilter->setParams(['width' => 800, 'height' => 600]);
$version->addFilter($resizeFilter);

// Apply contrast
$contrastFilter = new ContrastFilter();
$contrastFilter->setParams(['level' => 15]);
$version->addFilter($contrastFilter);

// Sharpen
$sharpenFilter = new SharpenFilter();
$sharpenFilter->setParams(['amount' => 5]);
$version->addFilter($sharpenFilter);

// Apply to image
$image = new Image("path/to/image.jpg");
$processed = $image->getVersion($version);
```

### 6.4. QR Code Generation
```php
use Katu\Tools\Images\QRCode;

// Create QR code
$qrCode = new QRCode("https://example.com", 300, 10);
$dataUri = $qrCode->getDataURI();

// Use in HTML
echo "<img src='{$dataUri}' alt='QR Code'>";

// Get base64 for storage
$base64 = $qrCode->getBase64PNG();
```

### 6.5. URL-based Images
```php
// Create image from URL
$image = new Image("https://example.com/image.jpg");

// Process and get URL
$version = new Version("web", "jpg");
$version->addFilter(new ResizeFilter(['width' => 500, 'height' => 500]));

$imageVersion = $image->getVersion($version);
$url = $imageVersion->getURL();
```

---

## 7. Advanced Features

### 7.1. Custom Filter Creation
```php
class WatermarkFilter extends Filter
{
    public function apply(Image $image): bool
    {
        $watermarkPath = $this->params['watermark_path'];
        $position = $this->params['position'] ?? 'bottom-right';

        // Apply watermark logic using Intervention Image
        $watermark = \Intervention\Image\ImageManagerStatic::make($watermarkPath);

        switch ($position) {
            case 'bottom-right':
                $image->insert($watermark, 'bottom-right');
                break;
            case 'center':
                $image->insert($watermark, 'center');
                break;
        }

        return true;
    }
}

// Usage
$watermarkFilter = new WatermarkFilter();
$watermarkFilter->setParams([
    'watermark_path' => 'path/to/watermark.png',
    'position' => 'bottom-right'
]);
```

### 7.2. Filter Collection Management
```php
use Katu\Tools\Images\FilterCollection;

$filters = new FilterCollection();
$filters[] = new ResizeFilter(['width' => 400, 'height' => 300]);
$filters[] = new ContrastFilter(['level' => 10]);
$filters[] = new SharpenFilter(['amount' => 3]);

$version = new Version("processed", "jpg", $filters);
```

### 7.3. Image Version Collection
```php
use Katu\Tools\Images\ImageVersionCollection;

$image = new Image("path/to/image.jpg");
$versions = $image->getVersions();

foreach ($versions as $version) {
    echo "Version: " . $version->getVersion()->getTitle();
    echo "URL: " . $version->getURL();
}
```

---

## 8. Storage Integration

### 8.1. File Storage
```php
use Katu\Storage\Entity;

// Create storage entity
$entity = new Entity("images/processed/image.jpg");

// Use with image
$image = new Image($entity);
```

### 8.2. Model Integration
```php
class ImageModel extends Model
{
    public function getImage(): Image
    {
        return new Image($this);
    }

    public function getThumbnail(): ImageVersion
    {
        $version = new Version("thumbnail", "jpg");
        $version->addFilter(new ResizeFilter(['width' => 150, 'height' => 150]));

        return $this->getImage()->getVersion($version);
    }
}
```

---

## 9. Performance Optimization

### 9.1. Caching
```php
// Images are automatically cached based on version configuration
$image = new Image("path/to/image.jpg");
$version = new Version("cached", "jpg");
$version->addFilter(new ResizeFilter(['width' => 200, 'height' => 200]));

// First call processes and caches
$imageVersion1 = $image->getVersion($version);

// Subsequent calls use cache
$imageVersion2 = $image->getVersion($version);
```

### 9.2. Lazy Processing
```php
// Images are processed only when accessed
$image = new Image("path/to/large-image.jpg");
$version = new Version("thumbnail", "jpg");
$version->addFilter(new ResizeFilter(['width' => 100, 'height' => 100]));

// No processing until URL is accessed
$imageVersion = $image->getVersion($version);
$url = $imageVersion->getURL(); // Processing happens here
```

---

## 10. Error Handling

### 10.1. Invalid Images
```php
try {
    $image = new Image("invalid/path/image.jpg");
    $url = $image->getURL();
} catch (Exception $e) {
    // Handle invalid image
    error_log("Image processing failed: " . $e->getMessage());
}
```

### 10.2. Filter Errors
```php
try {
    $filter = new ResizeFilter();
    $filter->setParams(['width' => -100, 'height' => -100]); // Invalid params
    $result = $filter->apply($image);
} catch (Exception $e) {
    // Handle filter error
    error_log("Filter application failed: " . $e->getMessage());
}
```

---

## 11. Best Practices

### 11.1. Version Naming
- Use descriptive version names (thumbnail, medium, large)
- Include dimensions in names when relevant
- Use consistent naming conventions

### 11.2. Filter Order
- Apply resize filters first for performance
- Apply color adjustments before effects
- Apply sharpening last

### 11.3. File Formats
- Use JPEG for photographs
- Use PNG for images with transparency
- Use WebP for modern web optimization

### 11.4. Performance
- Create appropriate image sizes for different use cases
- Use lazy loading for large images
- Implement proper caching strategies

---

## 12. Integration Examples

### 12.1. Controller Integration
```php
class ImageController extends Controller
{
    public function getImageVersion(ServerRequestInterface $request): ResponseInterface
    {
        $imagePackage = $request->getAttribute('imagePackage');
        $versionCode = $request->getAttribute('versionCode');

        $image = Image::createFromPackage(new Package($imagePackage));
        $version = $this->getVersionByCode($versionCode);

        $imageVersion = $image->getVersion($version);
        $url = $imageVersion->getURL();

        return $this->redirectResponse($url);
    }
}
```

### 12.2. Template Integration
```twig
{# In Twig template #}
{% set image = imageModel.getImage() %}
{% set thumbnail = image.getVersion(thumbnailVersion) %}
<img src="{{ thumbnail.getURL() }}" alt="Thumbnail">
```

---

## 13. Common Patterns

### 13.1. Image Upload Pattern
```php
// Complete image upload with processing
$upload = new Upload($_FILES["image"]);
if ($upload->isValid()) {
    $image = new Image($upload->getPath());

    // Create versions
    $image->createVersion("thumbnail", 150, 150);
    $image->createVersion("medium", 500, 500);

    // Store in database
    $imageRecord = new ImageRecord();
    $imageRecord->path = $image->getPath();
    $imageRecord->persist();
}
```

### 13.2. Image Processing Pattern
```php
// Image processing with filters
$image = new Image("uploads/photo.jpg");
$image->applyFilter(new ResizeFilter(800, 600))
      ->applyFilter(new QualityFilter(85))
      ->applyFilter(new WatermarkFilter("watermark.png"));

$processedImage = $image->save("processed/photo.jpg");
```

### 13.3. QR Code Generation Pattern
```php
// QR code generation for different purposes
$qrCode = new QRCode("https://example.com/user/123");
$qrCode->setSize(200)
       ->setMargin(10)
       ->setErrorCorrectionLevel("M");

$image = $qrCode->getImage();
$image->save("qr_codes/user_123.png");
```

---

## 14. Troubleshooting

### 14.1. Common Issues
- **Memory Errors:** Reduce image size or use streaming
- **File Permission Errors:** Check file system permissions
- **Filter Not Applied:** Verify filter parameters and order
- **URL Generation Fails:** Check routing configuration

### 14.2. Debugging
- Enable Intervention Image logging
- Check file paths and permissions
- Verify filter parameters
- Test with simple images first

---

This documentation provides comprehensive coverage of the Image Processing system. For specific implementation details, refer to the source code in `src/Tools/Images/`.
