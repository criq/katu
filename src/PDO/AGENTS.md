# PDO/Database System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the PDO/Database system in the KATU framework. The database system provides advanced connection management, query building, result handling, and database introspection capabilities.

---

## 1. System Overview

### 1.1. Purpose
- **Connection Management:** Advanced database connection handling with pooling
- **Query Building:** Type-safe SQL query construction
- **Result Handling:** Comprehensive result processing and caching
- **Database Introspection:** Table, column, and schema analysis
- **Transaction Support:** ACID transaction management
- **Performance Monitoring:** Query profiling and optimization

### 1.2. Architecture
- **Core Classes:** `Connection`, `Query`, `Result`, `Table`, `Column`
- **Query Building:** Integration with `Sexy\Sexy` query builder
- **Result Processing:** Advanced result handling and caching
- **Schema Introspection:** Database structure analysis
- **Connection Pooling:** Efficient connection management
- **Transaction Management:** ACID compliance

---

## 2. Core PDO Classes

### 2.1. Connection (`Katu\PDO\Connection`)
**Location:** `Connection.php`

Database connection handler with advanced features:

```php
// Key methods:
public static function getInstance(string $title): Connection
public function select(\Sexy\Select $select, array $params = []): Query
public function createQuery($sql, array $params = []): Query
public function transaction($callback)
public function getTables(): TableCollection
public function getTable(string $name): ?Table
public function getViews(): TableCollection
public function getView(string $name): ?Table
public function getProcesses(): Processlist
public function getDumps(): DumpCollection
public function getDumpDay(): DumpDay
public function getDumpWeek(): DumpWeek
public function getDumpDateCollection(): DumpDateCollection
```

**Key Features:**
- Connection pooling and singleton pattern
- **Persistent connections enabled by default** for improved performance
- Configurable PDO options via `DatabaseConnectionConfig`
- Transaction support with automatic rollback
- Table and view introspection
- SQL mode management
- Process monitoring
- Query result caching with pickle system
- Optimized connection pooling for improved performance

**Default PDO Options:**
- `PDO::ATTR_PERSISTENT => true` - Reuse connections across requests
- `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` - Throw exceptions on errors
- `PDO::ATTR_EMULATE_PREPARES => false` - Use native prepared statements
- `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` - Return associative arrays

### 2.2. Query (`Katu\PDO\Query`)
**Location:** `Query.php`

Database query execution and result handling:

```php
// Key methods:
public function __construct(Connection $connection, $sql, array $params = [])
public function execute(): Query
public function getResult(): Result
public function getRows(): array
public function getRow(): ?array
public function getValue()
public function getCount(): int
public function getLastInsertId(): ?string
public function getAffectedRows(): int
public function getExecutionTime(): float
public function getSQL(): string
public function getParams(): array
```

**Key Features:**
- Query execution and result handling
- Row and value extraction
- Performance monitoring
- Parameter binding
- Result caching
- Error handling

### 2.3. Result (`Katu\PDO\Result`)
**Location:** `Result.php`

Query result processing and manipulation:

```php
// Key methods:
public function __construct(Query $query)
public function getRows(): array
public function getRow(): ?array
public function getValue()
public function getCount(): int
public function getColumns(): array
public function getColumnNames(): array
public function getColumnTypes(): array
public function getFirstRow(): ?array
public function getLastRow(): ?array
public function getRowByIndex(int $index): ?array
public function getColumn(string $name): array
public function getColumnByIndex(int $index): array
```

**Key Features:**
- Result data access
- Column information
- Row manipulation
- Data type handling
- Result iteration

### 2.4. Table (`Katu\PDO\Table`)
**Location:** `Table.php`

Database table representation and operations:

```php
// Key methods:
public function __construct(Connection $connection, string $name)
public function getName(): string
public function getColumns(): ColumnCollection
public function getColumn(string $name): ?Column
public function getPrimaryKey(): ?Column
public function getIndexes(): array
public function getForeignKeys(): array
public function getRowCount(): int
public function getSize(): int
public function getEngine(): string
public function getCollation(): string
public function getComment(): string
public function getCreateStatement(): string
```

**Key Features:**
- Table structure analysis
- Column information
- Index and key management
- Table statistics
- Schema information

### 2.5. Column (`Katu\PDO\Column`)
**Location:** `Column.php`

Database column representation:

```php
// Key methods:
public function __construct(Table $table, string $name)
public function getName(): string
public function getType(): string
public function getLength(): ?int
public function getPrecision(): ?int
public function getScale(): ?int
public function getIsNullable(): bool
public function getIsPrimaryKey(): bool
public function getIsUnique(): bool
public function getIsAutoIncrement(): bool
public function getDefaultValue()
public function getComment(): string
public function getCollation(): string
```

**Key Features:**
- Column metadata
- Type information
- Constraint details
- Default values
- Collation settings

---

## 3. Usage Patterns

### 3.1. Basic Database Operations
```php
use Katu\PDO\Connection;

// Get database connection
$connection = Connection::getInstance("app");

// Simple query
$query = $connection->createQuery("SELECT * FROM users WHERE active = ?", [1]);
$result = $query->execute();

// Get all rows
$users = $result->getRows();

// Get single row
$user = $result->getRow();

// Get single value
$count = $result->getValue();
```

### 3.2. Query Building with Sexy ORM
```php
use Sexy\Sexy as SX;
use Katu\PDO\Connection;

$connection = Connection::getInstance("app");

// Build query with Sexy ORM
$sql = SX::select()
    ->from("users")
    ->where(SX::cmpEq("active", 1))
    ->orderBy(SX::orderBy("name"))
    ->limit(10);

// Execute query
$query = $connection->select($sql);
$result = $query->execute();

// Process results
$users = $result->getRows();
foreach ($users as $user) {
    echo "User: " . $user["name"] . "\n";
}
```

### 3.3. Transaction Management
```php
use Katu\PDO\Connection;

$connection = Connection::getInstance("app");

// Execute transaction
$result = $connection->transaction(function() use ($connection) {
    // Insert user
    $userQuery = $connection->createQuery(
        "INSERT INTO users (name, email) VALUES (?, ?)",
        ["John Doe", "john@example.com"]
    );
    $userQuery->execute();
    $userId = $userQuery->getLastInsertId();

    // Insert user profile
    $profileQuery = $connection->createQuery(
        "INSERT INTO user_profiles (user_id, bio) VALUES (?, ?)",
        [$userId, "Software Developer"]
    );
    $profileQuery->execute();

    return $userId;
});

echo "Created user with ID: " . $result;
```

### 3.4. Database Introspection
```php
use Katu\PDO\Connection;

$connection = Connection::getInstance("app");

// Get all tables
$tables = $connection->getTables();
foreach ($tables as $table) {
    echo "Table: " . $table->getName() . "\n";
    echo "Rows: " . $table->getRowCount() . "\n";
    echo "Size: " . $table->getSize() . " bytes\n";
}

// Get specific table
$usersTable = $connection->getTable("users");
if ($usersTable) {
    echo "Table: " . $usersTable->getName() . "\n";
    echo "Engine: " . $usersTable->getEngine() . "\n";
    echo "Collation: " . $usersTable->getCollation() . "\n";

    // Get columns
    $columns = $usersTable->getColumns();
    foreach ($columns as $column) {
        echo "Column: " . $column->getName() . "\n";
        echo "Type: " . $column->getType() . "\n";
        echo "Nullable: " . ($column->getIsNullable() ? "Yes" : "No") . "\n";
        echo "Primary Key: " . ($column->getIsPrimaryKey() ? "Yes" : "No") . "\n";
    }
}
```

### 3.5. Advanced Query Operations
```php
use Katu\PDO\Connection;
use Sexy\Sexy as SX;

$connection = Connection::getInstance("app");

// Complex query with joins
$sql = SX::select([
    "u.id",
    "u.name",
    "u.email",
    "p.bio",
    "p.avatar"
])
->from("users", "u")
->join("user_profiles", "p", SX::cmpEq("u.id", "p.user_id"))
->where(SX::cmpEq("u.active", 1))
->where(SX::cmpIsNotNull("p.bio"))
->orderBy(SX::orderBy("u.name"));

$query = $connection->select($sql);
$result = $query->execute();

// Process results
$users = $result->getRows();
foreach ($users as $user) {
    echo "User: " . $user["name"] . " (" . $user["email"] . ")\n";
    echo "Bio: " . $user["bio"] . "\n";
}

// Get column information
$columns = $result->getColumns();
foreach ($columns as $column) {
    echo "Column: " . $column["name"] . " (" . $column["type"] . ")\n";
}
```

### 3.6. Performance Monitoring
```php
use Katu\PDO\Connection;

$connection = Connection::getInstance("app");

// Execute query with timing
$query = $connection->createQuery("SELECT * FROM users WHERE created_at > ?", [date("Y-m-d", strtotime("-30 days"))]);
$result = $query->execute();

// Get execution time
$executionTime = $query->getExecutionTime();
echo "Query executed in: " . $executionTime . " seconds\n";

// Get affected rows
$affectedRows = $query->getAffectedRows();
echo "Affected rows: " . $affectedRows . "\n";

// Get result count
$rowCount = $result->getCount();
echo "Result rows: " . $rowCount . "\n";
```

---

## 4. Advanced Features

### 4.1. Connection Pooling
```php
class DatabaseManager
{
    private static $connections = [];

    public static function getConnection(string $name): Connection
    {
        if (!isset(self::$connections[$name])) {
            self::$connections[$name] = Connection::getInstance($name);
        }
        return self::$connections[$name];
    }

    public static function closeConnection(string $name): void
    {
        if (isset(self::$connections[$name])) {
            unset(self::$connections[$name]);
        }
    }

    public static function closeAllConnections(): void
    {
        self::$connections = [];
    }
}

// Usage
$appConnection = DatabaseManager::getConnection("app");
$deliConnection = DatabaseManager::getConnection("deli");
```

### 4.2. Query Caching
```php
class QueryCache
{
    private $cache = [];
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function executeCached(string $sql, array $params = [], int $ttl = 3600)
    {
        $cacheKey = md5($sql . serialize($params));

        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() - $cached["timestamp"] < $ttl) {
                return $cached["result"];
            }
        }

        $query = $this->connection->createQuery($sql, $params);
        $result = $query->execute();

        $this->cache[$cacheKey] = [
            "result" => $result,
            "timestamp" => time()
        ];

        return $result;
    }
}
```

### 4.3. Database Monitoring
```php
class DatabaseMonitor
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getDatabaseStats(): array
    {
        $tables = $this->connection->getTables();
        $totalRows = 0;
        $totalSize = 0;

        foreach ($tables as $table) {
            $totalRows += $table->getRowCount();
            $totalSize += $table->getSize();
        }

        return [
            "tables" => count($tables),
            "total_rows" => $totalRows,
            "total_size" => $totalSize,
            "average_rows_per_table" => $totalRows / count($tables),
            "average_size_per_table" => $totalSize / count($tables)
        ];
    }

    public function getSlowQueries(): array
    {
        $query = $this->connection->createQuery("
            SELECT * FROM mysql.slow_log
            WHERE start_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY start_time DESC
        ");
        $result = $query->execute();
        return $result->getRows();
    }

    public function getActiveConnections(): array
    {
        $processes = $this->connection->getProcesses();
        return $processes->getProcesses();
    }
}
```

### 4.4. Database Backup
```php
class DatabaseBackup
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function createBackup(string $outputPath): bool
    {
        $tables = $this->connection->getTables();
        $backup = [];

        foreach ($tables as $table) {
            $backup[$table->getName()] = [
                "structure" => $table->getCreateStatement(),
                "data" => $this->exportTableData($table)
            ];
        }

        return file_put_contents($outputPath, serialize($backup)) !== false;
    }

    private function exportTableData(Table $table): array
    {
        $query = $this->connection->createQuery("SELECT * FROM " . $table->getName());
        $result = $query->execute();
        return $result->getRows();
    }
}
```

---

## 5. Error Handling

### 5.1. Database Exceptions
```php
use Katu\PDO\Exception as PDOException;

try {
    $connection = Connection::getInstance("app");
    $query = $connection->createQuery("SELECT * FROM non_existent_table");
    $result = $query->execute();
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    echo "Error code: " . $e->getCode();
    echo "SQL state: " . $e->getSqlState();
}
```

### 5.2. Connection Error Handling
```php
class DatabaseConnectionManager
{
    public static function getConnectionWithRetry(string $name, int $maxRetries = 3): Connection
    {
        $retries = 0;

        while ($retries < $maxRetries) {
            try {
                return Connection::getInstance($name);
            } catch (PDOException $e) {
                $retries++;
                if ($retries >= $maxRetries) {
                    throw new Exception("Failed to connect to database after {$maxRetries} attempts");
                }
                sleep(1); // Wait 1 second before retry
            }
        }
    }
}
```

---

## 6. Best Practices

### 6.1. Connection Management
- Use connection pooling for performance
- Close connections when not needed
- Monitor connection usage
- Implement connection retry logic

### 6.2. Query Optimization
- Use prepared statements
- Optimize query performance
- Monitor slow queries
- Use appropriate indexes

### 6.3. Transaction Management
- Keep transactions short
- Handle rollback scenarios
- Use appropriate isolation levels
- Monitor transaction performance

### 6.4. Security
- Use parameterized queries
- Validate input data
- Implement proper access controls
- Monitor database access

---

## 7. Integration Examples

### 7.1. Model Integration
```php
class User extends Model
{
    public static function getConnection(): Connection
    {
        return Connection::getInstance("app");
    }

    public static function getTable(): Table
    {
        return static::getConnection()->getTable("users");
    }

    public static function getColumn(string $name): Column
    {
        return static::getTable()->getColumn($name);
    }

    public static function getActiveUsers(): array
    {
        $connection = static::getConnection();
        $query = $connection->createQuery("SELECT * FROM users WHERE active = 1");
        $result = $query->execute();
        return $result->getRows();
    }
}
```

### 7.2. Controller Integration
```php
class UserController extends Controller
{
    public function getUsers(ServerRequestInterface $request): ResponseInterface
    {
        $connection = Connection::getInstance("app");

        $sql = "SELECT u.*, p.bio FROM users u
                LEFT JOIN user_profiles p ON u.id = p.user_id
                WHERE u.active = 1";

        $query = $connection->createQuery($sql);
        $result = $query->execute();
        $users = $result->getRows();

        return $this->jsonResponse([
            "users" => $users,
            "count" => count($users)
        ]);
    }
}
```

---

## 8. Common Patterns

### 8.1. Database Connection Pattern
```php
// Standard database connection usage
$connection = Connection::getInstance("app");
$users = User::getBy(["active" => true]);

// With custom connection
$connection = Connection::getInstance("custom");
$connection->setDatabase("custom_db");
```

### 8.2. Query Building Pattern
```php
// Using Sexy ORM for complex queries
$sql = SX::select()
    ->from(User::getTable())
    ->where(SX::eq(User::getColumn("active"), true))
    ->orderBy(SX::orderBy(User::getColumn("name")));

$users = User::getBySQL($sql);
```

### 8.3. Transaction Pattern
```php
// Database transaction handling
$connection->transaction(function() use ($user, $profile) {
    $user->persist();
    $profile->persist();

    // Both operations succeed or both fail
});
```

---

## 9. Troubleshooting

### 9.1. Common Issues
- **Connection Failures:** Check database configuration and connectivity
- **Query Errors:** Verify SQL syntax and table/column names
- **Performance Issues:** Monitor query execution times and optimize
- **Memory Issues:** Check result set sizes and implement pagination

### 9.2. Debugging
- Enable query logging
- Monitor connection status
- Check database processes
- Analyze slow queries

---

This documentation provides comprehensive coverage of the PDO/Database system. For specific implementation details, refer to the source code in `src/PDO/`.
