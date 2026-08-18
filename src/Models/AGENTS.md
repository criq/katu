# Model System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Model system in the KATU framework. The model system provides a complete ORM (Object-Relational Mapping) solution with Active Record pattern, database abstraction, and extensive model functionality.

---

## 1. System Overview

### 1.1. Purpose
- **Active Record ORM:** Complete object-relational mapping with Active Record pattern
- **Database Abstraction:** Database-agnostic model operations
- **CRUD Operations:** Create, Read, Update, Delete operations with callbacks
- **Query Building:** Advanced query building with Sexy ORM integration
- **Model Relationships:** File attachments, user relationships, and associations
- **Caching Support:** Runtime caching and view materialization
- **Transaction Support:** Database transaction management
- **Validation:** Built-in model validation and error handling

### 1.2. Architecture
- **Base Classes:** `Base`, `Model`, `View` - Core model hierarchy
- **Preset Models:** Pre-built model classes for common entities
- **Column Management:** Column value handling and SQL generation
- **Query System:** Sexy ORM integration for type-safe queries
- **Caching System:** Runtime caching and view materialization
- **File System:** File attachment management and relationships

---

## 2. Core Model Classes

### 2.1. Base (`Katu\Models\Base`)
**Location:** `Base.php`

Abstract base class for all models:

**Key Features:**
- Database connection management
- Table and column introspection
- Query building with Sexy ORM
- Result factory integration
- Transaction support
- Array-to-object conversion
- PHP 8.2+ undeclared columns: `__set` / `__get` on `Base` store properties that are not declared on the subclass (PDO hydration via `ClassFactory`). Declared public properties still take precedence. `getColumnValues()` reads `$this->{$column}` so undeclared columns persist.

```php
// Key methods:
public function __set(string $name, mixed $value): void
public function __get(string $name): mixed
public static function createFromArray(array $array): Base
public static function getConnection(): \Katu\PDO\Connection
public static function getTable(): \Katu\PDO\Table
public static function getColumn(string $name): \Katu\PDO\Column
public static function getBy(?array $where = [], $orderBy = null, $limitOrPage = null)
public static function getBySQL(\Sexy\Select $sql)
public static function getOneBy(?array $where = [], $orderBy = null)
public static function getAll($orderBy = null)
public static function transaction()
```

### 2.2. Model (`Katu\Models\Model`)
**Location:** `Model.php`

Main model class with full ORM functionality:

```php
// Key methods:
public function persist(): Model
public function persistInsert(): Model
public function persistUpdate(): Model
public function persistWithoutCallbacks(): Model
public function delete(): bool
public static function get(?string $id)
public static function getFromRuntime(?string $id)
public function exists(): bool
public function setUniqueColumnValue(Column $column, ?string $chars = null, ?int $length = null)
public function setUniqueColumnSlug(Column $column, array $source, bool $force = false, array $constraints = [])
```

**Key Features:**
- Active Record pattern implementation
- Automatic CRUD operations
- Callback system (before/after persist/delete)
- Unique column value generation
- Slug generation with collision handling
- File attachment management
- Transaction support
- Runtime caching

### 2.3. View (`Katu\Models\View`)
**Location:** `View.php`

Advanced view model with caching and materialization:

```php
// Key methods:
public static function getTable(): \Katu\PDO\Table
public static function getView(): \Katu\PDO\View
public static function cache(): bool
public static function materialize(): bool
public static function isCached(): bool
public static function isMaterialized(): bool
public static function cacheIfExpired(): bool
public static function materializeIfExpired()
```

**Key Features:**
- View caching system
- Materialized view support
- Automatic cache expiration
- Performance optimization
- Source table monitoring
- Lock-based operations

---

## 3. Column Management

### 3.1. ColumnValue (`Katu\Models\ColumnValue`)
**Location:** `ColumnValue.php`

Individual column value representation:

```php
// Key methods:
public function __construct(Column $column, $value)
public function getColumn(): Column
public function getStatementKey(): string
public function getStatementValue(): ?string
```

**Key Features:**
- Column-value pairing
- SQL statement generation
- Parameter binding support
- Type-safe value handling

### 3.2. ColumnValueCollection (`Katu\Models\ColumnValueCollection`)
**Location:** `ColumnValueCollection.php`

Collection for managing multiple column values:

```php
// Key methods:
public function getColumnsString(): string
public function getParamsString(): string
public function getSetString(): string
public function getStatementParams(): array
```

**Key Features:**
- SQL string generation
- Parameter collection
- Bulk operations support
- Statement parameter binding

---

## 4. Preset Models

### 4.1. User (`Katu\Models\Presets\User`)
**Location:** `Presets/User.php`

User model with authentication support:

```php
// Key methods:
public static function getOrCreateWithEmailAddress(\Katu\Models\Presets\EmailAddress $emailAddress): User
public static function createWithEmailAddress(\Katu\Models\Presets\EmailAddress $emailAddress): User
public static function getFromRequest(?ServerRequestInterface $request): ?User
```

**Key Features:**
- User creation and management
- Email address integration
- Request-based user retrieval
- Authentication support

### 4.2. AccessToken (`Katu\Models\Presets\AccessToken`)
**Location:** `Presets/AccessToken.php`

Access token management for API authentication.

### 4.3. EmailAddress (`Katu\Models\Presets\EmailAddress`)
**Location:** `Presets/EmailAddress.php`

Email address model with validation.

### 4.4. File (`Katu\Models\Presets\File`)
**Location:** `Presets/File.php`

File model for file management.

### 4.5. FileAttachment (`Katu\Models\Presets\FileAttachment`)
**Location:** `Presets/FileAttachment.php`

> **Deprecated:** This class is deprecated. Use application-specific file attachment models instead.

File attachment model for model-file relationships.

### 4.6. Role (`Katu\Models\Presets\Role`)
**Location:** `Presets/Role.php`

User role management.

### 4.7. RolePermission (`Katu\Models\Presets\RolePermission`)
**Location:** `Presets/RolePermission.php`

Role-based permission management.

### 4.8. Setting (`Katu\Models\Presets\Setting`)
**Location:** `Presets/Setting.php`

Application settings management.

### 4.9. UserPermission (`Katu\Models\Presets\UserPermission`)
**Location:** `Presets/UserPermission.php`

User-specific permission management.

### 4.10. UserRole (`Katu\Models\Presets\UserRole`)
**Location:** `Presets/UserRole.php`

User-role relationship management.

### 4.11. UserSetting (`Katu\Models\Presets\UserSetting`)
**Location:** `Presets/UserSetting.php`

User-specific settings management.

### 4.12. Geocode (`Katu\Models\Presets\Geocode`)
**Location:** `Presets/Geocode.php`

Geographic location data management.

---

## 5. Model Callbacks

### 5.1. Persist Callbacks
```php
public function beforePersistCallback(): Model
public function afterPersistCallback(): Model
```

### 5.2. Delete Callbacks
```php
public function beforeDeleteCallback(): Model
public function afterDeleteCallback(): Model
```

### 5.3. General Callbacks
```php
public function beforeAnyCallback(): Model
public function afterAnyCallback(): Model
```

**Key Features:**
- Lifecycle hooks
- Business logic integration
- Data validation
- Audit trail support

---

## 6. Query System

### 6.1. Basic Queries
```php
// Get by ID
$user = User::get($id);

// Get by conditions
$users = User::getBy(["active" => true]);

// Get one by conditions
$user = User::getOneBy(["email" => "user@example.com"]);

// Get all
$users = User::getAll();
```

### 6.2. Advanced Queries with Sexy ORM
```php
use Sexy\Sexy as SX;

$sql = SX::select()
    ->from(User::getTable())
    ->where(SX::eq(User::getColumn("active"), true))
    ->orderBy(SX::orderBy(User::getColumn("name")))
    ->setLimit(SX::limit(10));

$users = User::getBySQL($sql);
```

### 6.3. Complex Queries
```php
$sql = SX::select()
    ->from(User::getTable())
    ->join(UserRole::getTable(), SX::eq(User::getIdColumn(), UserRole::getColumn("userId")))
    ->join(Role::getTable(), SX::eq(UserRole::getColumn("roleId"), Role::getIdColumn()))
    ->where(SX::eq(Role::getColumn("name"), "admin"));

$adminUsers = User::getBySQL($sql);
```

---

## 7. Model Persistence

### 7.1. Creating Models
```php
$user = new User();
$user->name = "John Doe";
$user->email = "john@example.com";
$user->persist();
```

### 7.2. Updating Models
```php
$user = User::get($id);
$user->name = "Jane Doe";
$user->persist();
```

### 7.3. Deleting Models
```php
$user = User::get($id);
$user->delete();
```

### 7.4. Batch Operations
```php
// Insert multiple records
$users = User::insert([
    "name" => "User 1",
    "email" => "user1@example.com"
]);

// Upsert operations
$user = User::upsert(
    ["email" => "user@example.com"],
    ["name" => "New User"],
    ["lastLogin" => new Time()]
);
```

---

## 8. File Attachments

### 8.1. File Attachment Management
```php
// Get file attachments
$attachments = $model->getFileAttachments();

// Get image attachments only
$images = $model->getImageFileAttachments();

// Get single image
$image = $model->getImageFile();

// Refresh attachments
$model->refreshFileAttachmentsFromFileIds($user, $fileIds);
```

### 8.2. File Relationship Queries
```php
$sql = SX::select()
    ->from(FileAttachment::getTable())
    ->where(SX::eq(FileAttachment::getColumn("objectModel"), static::getClass()->getName()))
    ->where(SX::eq(FileAttachment::getColumn("objectId"), $this->getId()));
```

---

## 9. Caching and Performance

### 9.1. Runtime Caching
```php
// Get from cache
$user = User::getFromRuntime($id);

// Clear runtime cache
\Katu\Cache\Runtime::clear();
```

### 9.2. View Caching
```php
// Check if cached
if (MyView::isCached()) {
    // Use cached data
}

// Cache if expired
MyView::cacheIfExpired();

// Force cache
MyView::cache();
```

### 9.3. Materialized Views
```php
// Check if materialized
if (MyView::isMaterialized()) {
    // Use materialized data
}

// Materialize if expired
MyView::materializeIfExpired();
```

---

## 10. Unique Value Generation

### 10.1. Unique Column Values
```php
// Generate unique value
$code = $model->setUniqueColumnValue($column, "ABCDEFGHJKLMNPQRSTUVWXYZ23456789", 8);

// Check uniqueness
$isUnique = Model::checkUniqueColumnValue($whereExpressions, $excludeObject);
```

### 10.2. Unique Slugs
```php
// Generate unique slug
$model->setUniqueColumnSlug($column, ["title" => "My Title"], false, $constraints);
```

---

## 11. Transaction Support

### 11.1. Model Transactions
```php
User::transaction(function() {
    $user = new User();
    $user->name = "John Doe";
    $user->persist();

    $role = new UserRole();
    $role->userId = $user->getId();
    $role->roleId = $adminRole->getId();
    $role->persist();
});
```

### 11.2. Connection Transactions
```php
$connection = User::getConnection();
$connection->transaction(function() use ($connection) {
    // Transaction operations
});
```

---

## 12. Best Practices

### 12.1. Model Development
- Always extend `\Katu\Models\Model` for application models
- Use `const DATABASE` and `const TABLE` constants
- Implement `getIdColumn()` for custom primary keys
- Use callbacks for business logic hooks

### 12.2. Query Optimization
- Use specific column selection when possible
- Implement proper indexing
- Use view caching for complex queries
- Monitor query performance

### 12.3. Data Integrity
- Use transactions for related operations
- Implement proper validation in callbacks
- Use unique constraints for data integrity
- Handle foreign key relationships properly

### 12.4. Performance Considerations
- Use runtime caching for frequently accessed data
- Implement view materialization for complex queries
- Monitor database performance
- Use appropriate query limits

---

## 13. Common Patterns

### 13.1. Model Creation
```php
class ExampleModel extends \Katu\Models\Model
{
    const DATABASE = "app";
    const TABLE = "examples";

    public $id;
    public $name;
    public $timeCreated;

    public static function getIdColumn(): Column
    {
        return static::getColumn("id");
    }
}
```

### 13.2. Model Relationships
```php
// One-to-many relationship
public function getChildren(): \Katu\PDO\Result
{
    return ChildModel::getBy(["parentId" => $this->getId()]);
}

// Many-to-many relationship
public function getRoles(): \Katu\PDO\Result
{
    $sql = SX::select()
        ->from(Role::getTable())
        ->join(UserRole::getTable(), SX::eq(Role::getIdColumn(), UserRole::getColumn("roleId")))
        ->where(SX::eq(UserRole::getColumn("userId"), $this->getId()));

    return Role::getBySQL($sql);
}
```

### 13.3. Model Validation
```php
public function beforePersistCallback(): Model
{
    if (empty($this->name)) {
        throw new \Katu\Exceptions\InputErrorException("Name is required");
    }

    return $this;
}
```

---

## 14. Troubleshooting

### 14.1. Common Issues
- **Database Connection Errors:** Check `DATABASE` constant and connection configuration
- **Table Not Found:** Verify `TABLE` constant and database schema
- **Column Not Found:** Check column names and table structure
- **Callback Errors:** Ensure callback methods return the model instance

### 14.2. Debugging
- Use `getConnection()` to check database connectivity
- Use `getTable()` to inspect table structure
- Use `getColumn()` to verify column existence
- Enable query logging for SQL debugging

### 14.3. Performance Issues
- Monitor query execution times
- Use view caching for complex queries
- Implement proper database indexing
- Use connection pooling for high-traffic applications

---

This documentation provides comprehensive coverage of the KATU Model system. For specific implementation details, refer to the individual model classes in `src/Models/` and the application-specific model implementations.
