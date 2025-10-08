# Type System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Type system in the KATU framework. The type system provides specialized type classes for data validation, manipulation, and conversion with strong typing and safety features.

---

## 1. System Overview

### 1.1. Purpose
- **Type Safety:** Strong typing for data validation and manipulation
- **Data Conversion:** Automatic type conversion and validation
- **Specialized Types:** Domain-specific type classes
- **Collection Support:** Type-safe collections and arrays
- **Validation:** Built-in validation and error handling
- **Serialization:** Type-aware serialization and deserialization

### 1.2. Architecture
- **Core Types:** `TString`, `TURL`, `TEmailAddress`, `TJSON`, `TPayload`
- **Collection Types:** `TArray`, `TEmailAddressCollection`, `TURLCollection`, `TPayloadCollection`
- **Specialized Types:** `TColor`, `TCoordsRectangle`, `TFileSize`, `TIdentifier`, `TImageSize`, `TInterval`, `TPagination`
- **Encryption Types:** `TEncryptionKey`, `TEncryptedData`, `THash`
- **Geo Types:** `TCoordinates`, `TAddress`, `TLocation`

---

## 2. Core Type Classes

### 2.1. TString (`Katu\Types\TString`)
**Location:** `TString.php`

Enhanced string handling with additional functionality:

```php
// Key methods:
public function __construct(?string $value = null)
public function __toString(): string
public function getForURL(?OptionCollection $options = null): TString
public function getSearchable(): TString
public function getWithAccentsRemoved(): TString
public function getAsFloat(): float
public function getAsInt(): int
public function getNumberOfWords(): int
public function getLength(): int
public function getIsEmpty(): bool
public function getIsNotEmpty(): bool
public function getTrimmed(): TString
public function getLowercased(): TString
public function getUppercased(): TString
public function getCapitalized(): TString
```

**Key Features:**
- URL-friendly string generation
- Search optimization
- Accent removal
- Numeric conversion
- Word counting
- String manipulation
- Validation methods

### 2.2. TURL (`Katu\Types\TURL`)
**Location:** `TURL.php`

URL handling and validation:

```php
// Key methods:
public function __construct(?string $url = null)
public function __toString(): string
public function getScheme(): ?string
public function getHost(): ?string
public function getPort(): ?int
public function getPath(): ?string
public function getQuery(): ?string
public function getFragment(): ?string
public function getIsValid(): bool
public function getIsAbsolute(): bool
public function getIsRelative(): bool
public function getDomain(): ?string
public function getSubdomain(): ?string
public function getTLD(): ?string
```

**Key Features:**
- URL parsing and validation
- Component extraction
- Domain analysis
- Absolute/relative detection
- URL manipulation

### 2.3. TEmailAddress (`Katu\Types\TEmailAddress`)
**Location:** `TEmailAddress.php`

Email address handling and validation:

```php
// Key methods:
public function __construct(?string $email = null)
public function __toString(): string
public function getLocalPart(): ?string
public function getDomain(): ?string
public function getIsValid(): bool
public function getIsDisposable(): bool
public function getIsCorporate(): bool
public function getDisplayName(): ?string
public function getFullAddress(): string
```

**Key Features:**
- Email validation
- Component extraction
- Disposable email detection
- Corporate email detection
- Display name handling

### 2.4. TJSON (`Katu\Types\TJSON`)
**Location:** `TJSON.php`

JSON data handling and validation:

```php
// Key methods:
public function __construct($data = null)
public function __toString(): string
public function getData()
public function getIsValid(): bool
public function getIsArray(): bool
public function getIsObject(): bool
public function getKeys(): array
public function getValue(string $key, $default = null)
public function setValue(string $key, $value): TJSON
public function getArray(): array
public function getObject(): object
```

**Key Features:**
- JSON validation
- Data type detection
- Key-value access
- Array/object conversion
- JSON manipulation

### 2.5. TPayload (`Katu\Types\TPayload`)
**Location:** `TPayload.php`

Generic payload data handling:

```php
// Key methods:
public function __construct($data = null)
public function __toString(): string
public function getData()
public function getIsValid(): bool
public function getKeys(): array
public function getValue(string $key, $default = null)
public function setValue(string $key, $value): TPayload
public function hasKey(string $key): bool
public function removeKey(string $key): TPayload
```

**Key Features:**
- Generic data handling
- Key-value operations
- Data validation
- Payload manipulation

---

## 3. Collection Types

### 3.1. TArray (`Katu\Types\TArray`)
**Location:** `TArray.php`

Array handling with type safety:

```php
// Key methods:
public function __construct(?array $data = null)
public function getData(): array
public function getCount(): int
public function getIsEmpty(): bool
public function getIsNotEmpty(): bool
public function add($value): TArray
public function remove($value): TArray
public function has($value): bool
public function getFirst()
public function getLast()
public function getKeys(): array
public function getValues(): array
```

### 3.2. TEmailAddressCollection (`Katu\Types\TEmailAddressCollection`)
**Location:** `TEmailAddressCollection.php`

Collection of email addresses:

```php
// Key methods:
public function addEmail(TEmailAddress $email): TEmailAddressCollection
public function getEmails(): array
public function getValidEmails(): TEmailAddressCollection
public function getInvalidEmails(): TEmailAddressCollection
public function getDomains(): array
public function getUniqueDomains(): array
```

### 3.3. TURLCollection (`Katu\Types\TURLCollection`)
**Location:** `TURLCollection.php`

Collection of URLs:

```php
// Key methods:
public function addURL(TURL $url): TURLCollection
public function getURLs(): array
public function getValidURLs(): TURLCollection
public function getInvalidURLs(): TURLCollection
public function getDomains(): array
public function getUniqueDomains(): array
```

### 3.4. TPayloadCollection (`Katu\Types\TPayloadCollection`)
**Location:** `TPayloadCollection.php`

Collection of payload objects:

```php
// Key methods:
public function addPayload(TPayload $payload): TPayloadCollection
public function getPayloads(): array
public function getByKey(string $key): TPayloadCollection
public function getByValue($value): TPayloadCollection
public function getUnique(): TPayloadCollection
```

---

## 4. Specialized Types

### 4.1. TColor (`Katu\Types\TColor`)
**Location:** `TColor.php`

Color handling and manipulation:

```php
// Key methods:
public function __construct(?string $color = null)
public function __toString(): string
public function getHex(): string
public function getRGB(): array
public function getHSL(): array
public function getIsValid(): bool
public function getBrightness(): float
public function getContrast(TColor $other): float
public function getComplementary(): TColor
public function getLighter(float $amount): TColor
public function getDarker(float $amount): TColor
```

### 4.2. TCoordsRectangle (`Katu\Types\TCoordsRectangle`)
**Location:** `TCoordsRectangle.php`

Rectangle coordinate handling:

```php
// Key methods:
public function __construct(?array $coords = null)
public function getX(): float
public function getY(): float
public function getWidth(): float
public function getHeight(): float
public function getArea(): float
public function getPerimeter(): float
public function getCenter(): array
public function contains(array $point): bool
public function intersects(TCoordsRectangle $other): bool
```

### 4.3. TFileSize (`Katu\Types\TFileSize`)
**Location:** `TFileSize.php`

File size representation:

```php
// Key methods:
public function __construct(int $bytes = 0)
public function __toString(): string
public function getBytes(): int
public function getKB(): float
public function getMB(): float
public function getGB(): float
public function getTB(): float
public function getFormatted(): string
public function getHumanReadable(): string
```

### 4.4. TIdentifier (`Katu\Types\TIdentifier`)
**Location:** `TIdentifier.php`

Unique identifier generation:

```php
// Key methods:
public function __construct(?string $identifier = null)
public function __toString(): string
public function getValue(): string
public function getIsValid(): bool
public function getLength(): int
public function getPrefix(): ?string
public function getSuffix(): ?string
public static function generate(int $length = 32): TIdentifier
public static function generateUUID(): TIdentifier
```

### 4.5. TImageSize (`Katu\Types\TImageSize`)
**Location:** `TImageSize.php`

Image dimension handling:

```php
// Key methods:
public function __construct(?array $size = null)
public function getWidth(): int
public function getHeight(): int
public function getAspectRatio(): float
public function getIsLandscape(): bool
public function getIsPortrait(): bool
public function getIsSquare(): bool
public function getArea(): int
public function getPixels(): int
```

### 4.6. TInterval (`Katu\Types\TInterval`)
**Location:** `TInterval.php`

Time interval representation:

```php
// Key methods:
public function __construct($interval = null)
public function __toString(): string
public function getSeconds(): int
public function getMinutes(): float
public function getHours(): float
public function getDays(): float
public function getWeeks(): float
public function getMonths(): float
public function getYears(): float
public function getIsPositive(): bool
public function getIsNegative(): bool
```

### 4.7. TPagination (`Katu\Types\TPagination`)
**Location:** `TPagination.php`

Pagination data handling:

```php
// Key methods:
public function __construct(?array $pagination = null)
public function getPage(): int
public function getPerPage(): int
public function getTotal(): int
public function getTotalPages(): int
public function getOffset(): int
public function getLimit(): int
public function getHasNext(): bool
public function getHasPrevious(): bool
public function getNextPage(): ?int
public function getPreviousPage(): ?int
```

---

## 5. Usage Patterns

### 5.1. Basic Type Usage
```php
use Katu\Types\TString;
use Katu\Types\TURL;
use Katu\Types\TEmailAddress;

// String handling
$string = new TString("Hello World");
echo $string->getUppercased(); // "HELLO WORLD"
echo $string->getNumberOfWords(); // 2
echo $string->getForURL(); // "hello-world"

// URL handling
$url = new TURL("https://example.com/path?query=value");
echo $url->getHost(); // "example.com"
echo $url->getPath(); // "/path"
echo $url->getIsValid(); // true

// Email handling
$email = new TEmailAddress("user@example.com");
echo $email->getLocalPart(); // "user"
echo $email->getDomain(); // "example.com"
echo $email->getIsValid(); // true
```

### 5.2. JSON Data Handling
```php
use Katu\Types\TJSON;

// Create JSON from data
$data = ["name" => "John", "age" => 30, "city" => "New York"];
$json = new TJSON($data);

// Access data
echo $json->getValue("name"); // "John"
echo $json->getValue("age"); // 30

// Modify data
$json->setValue("age", 31);
$json->setValue("country", "USA");

// Convert to array
$array = $json->getArray();

// Validate JSON
if ($json->getIsValid()) {
    echo "Valid JSON data";
}
```

### 5.3. Collection Operations
```php
use Katu\Types\TEmailAddressCollection;
use Katu\Types\TEmailAddress;

// Create email collection
$emails = new TEmailAddressCollection();
$emails->addEmail(new TEmailAddress("user1@example.com"));
$emails->addEmail(new TEmailAddress("user2@example.com"));
$emails->addEmail(new TEmailAddress("invalid-email"));

// Get valid emails
$validEmails = $emails->getValidEmails();
$invalidEmails = $emails->getInvalidEmails();

// Get unique domains
$domains = $emails->getUniqueDomains();
```

### 5.4. Color Manipulation
```php
use Katu\Types\TColor;

// Create color
$color = new TColor("#FF5733");

// Get color information
echo $color->getHex(); // "#FF5733"
$rgb = $color->getRGB(); // [255, 87, 51]
$hsl = $color->getHSL(); // [12, 100, 60]

// Color manipulation
$lighter = $color->getLighter(0.2);
$darker = $color->getDarker(0.2);
$complementary = $color->getComplementary();

// Color analysis
echo $color->getBrightness(); // 0.6
echo $color->getContrast(new TColor("#FFFFFF")); // 4.5
```

### 5.5. File Size Handling
```php
use Katu\Types\TFileSize;

// Create file size
$fileSize = new TFileSize(1048576); // 1MB in bytes

// Get different units
echo $fileSize->getKB(); // 1024
echo $fileSize->getMB(); // 1
echo $fileSize->getGB(); // 0.001

// Get formatted string
echo $fileSize->getFormatted(); // "1.00 MB"
echo $fileSize->getHumanReadable(); // "1 MB"
```

### 5.6. Pagination Handling
```php
use Katu\Types\TPagination;

// Create pagination
$pagination = new TPagination([
    "page" => 2,
    "per_page" => 10,
    "total" => 95
]);

// Get pagination info
echo $pagination->getPage(); // 2
echo $pagination->getPerPage(); // 10
echo $pagination->getTotal(); // 95
echo $pagination->getTotalPages(); // 10
echo $pagination->getOffset(); // 10
echo $pagination->getLimit(); // 10

// Navigation
echo $pagination->getHasNext(); // true
echo $pagination->getHasPrevious(); // true
echo $pagination->getNextPage(); // 3
echo $pagination->getPreviousPage(); // 1
```

---

## 6. Advanced Features

### 6.1. Type Validation
```php
class TypeValidator
{
    public function validateString(TString $string): bool
    {
        return $string->getIsNotEmpty() && $string->getLength() > 0;
    }

    public function validateEmail(TEmailAddress $email): bool
    {
        return $email->getIsValid() && !$email->getIsDisposable();
    }

    public function validateURL(TURL $url): bool
    {
        return $url->getIsValid() && $url->getIsAbsolute();
    }

    public function validateJSON(TJSON $json): bool
    {
        return $json->getIsValid() && $json->getIsObject();
    }
}
```

### 6.2. Type Conversion
```php
class TypeConverter
{
    public function convertToString($value): TString
    {
        if ($value instanceof TString) {
            return $value;
        }
        return new TString((string)$value);
    }

    public function convertToURL($value): TURL
    {
        if ($value instanceof TURL) {
            return $value;
        }
        return new TURL((string)$value);
    }

    public function convertToEmail($value): TEmailAddress
    {
        if ($value instanceof TEmailAddress) {
            return $value;
        }
        return new TEmailAddress((string)$value);
    }
}
```

### 6.3. Type Collections
```php
class TypeCollection
{
    private $types = [];

    public function addType(string $name, $type): void
    {
        $this->types[$name] = $type;
    }

    public function getType(string $name)
    {
        return $this->types[$name] ?? null;
    }

    public function validateAll(): array
    {
        $results = [];
        foreach ($this->types as $name => $type) {
            $results[$name] = $this->validateType($type);
        }
        return $results;
    }

    private function validateType($type): bool
    {
        if ($type instanceof TString) {
            return $type->getIsNotEmpty();
        } elseif ($type instanceof TEmailAddress) {
            return $type->getIsValid();
        } elseif ($type instanceof TURL) {
            return $type->getIsValid();
        }
        return true;
    }
}
```

---

## 7. Best Practices

### 7.1. Type Safety
- Use appropriate type classes for data
- Validate types before operations
- Handle type conversion errors
- Use type hints in method signatures

### 7.2. Performance
- Cache type instances when possible
- Use lightweight types for simple data
- Avoid unnecessary type conversions
- Monitor memory usage

### 7.3. Error Handling
- Validate type data before use
- Handle type conversion failures
- Provide meaningful error messages
- Use try-catch for type operations

### 7.4. Data Integrity
- Ensure type consistency
- Validate data before type creation
- Handle edge cases properly
- Maintain type invariants

---

## 8. Integration Examples

### 8.1. Model Integration
```php
class User extends Model
{
    public function getEmailAddress(): TEmailAddress
    {
        return new TEmailAddress($this->email);
    }

    public function getWebsiteURL(): ?TURL
    {
        if (!$this->website) {
            return null;
        }
        return new TURL($this->website);
    }

    public function getDisplayName(): TString
    {
        return new TString($this->firstName . " " . $this->lastName);
    }
}
```

### 8.2. Controller Integration
```php
class UserController extends Controller
{
    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();

        // Validate email
        $email = new TEmailAddress($data["email"]);
        if (!$email->getIsValid()) {
            return $this->errorResponse("Invalid email address");
        }

        // Validate website URL
        if (isset($data["website"])) {
            $website = new TURL($data["website"]);
            if (!$website->getIsValid()) {
                return $this->errorResponse("Invalid website URL");
            }
        }

        // Create user with validated data
        $user = new User();
        $user->setEmail($email->__toString());
        $user->setWebsite($website->__toString() ?? null);
        $user->persist();

        return $this->jsonResponse([
            "success" => true,
            "user" => $user->getRestResponse()
        ]);
    }
}
```

---

## 9. Troubleshooting

### 9.1. Common Issues
- **Type Validation Failures:** Check input data format
- **Conversion Errors:** Verify source data compatibility
- **Memory Issues:** Monitor type object creation
- **Performance Issues:** Optimize type operations

### 9.2. Debugging
- Use type validation methods
- Check type properties
- Monitor type conversions
- Validate type data

---

This documentation provides comprehensive coverage of the Type system. For specific implementation details, refer to the source code in `src/Types/`.
