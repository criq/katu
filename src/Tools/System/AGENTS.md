# System Monitoring - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the System Monitoring system in the KATU framework. The system monitoring provides CPU load monitoring, memory management, disk space monitoring, and system resource tracking for application health and performance.

---

## 1. System Overview

### 1.1. Purpose
- **System Resource Monitoring:** CPU, memory, and disk space monitoring
- **Load Average Tracking:** System load monitoring and threshold management
- **Memory Management:** Memory usage tracking and limit management
- **Disk Space Monitoring:** Disk usage monitoring with alerting
- **Performance Monitoring:** System performance metrics and thresholds
- **Health Checks:** System health validation and alerting

### 1.2. Architecture
- **Core Classes:** `System`, `Memory`, `DiskSpace`, `DiskSpaceMonitor`
- **Monitoring Classes:** `DiskSpaceCollection`, `DiskSpaceMonitorCollection`
- **Threshold Management:** Load average and disk space thresholds
- **Alerting System:** Disk space and system load alerting
- **Caching Integration:** System metrics caching for performance
- **Cross-Platform Support:** Linux, Windows, and macOS compatibility

---

## 2. Core System Classes

### 2.1. System (`Katu\Tools\System\System`)
**Location:** `System.php`

Main system monitoring class:

```php
// Key methods:
public static function getNumberOfCpus(): int
public static function getLoadAverage(): ?array
public static function getLoadAveragePerCpu(): ?array
public static function assertMaxLoadAverage(float $loadAverage): bool
```

**Key Features:**
- CPU count detection
- Load average monitoring
- Per-CPU load calculation
- Load threshold validation
- Cross-platform compatibility
- Caching for performance

### 2.2. Memory (`Katu\Tools\System\Memory`)
**Location:** `Memory.php`

Memory usage monitoring and management:

```php
// Key methods:
public static function setLimit(TFileSize $limit): bool
public static function getLimit(): TFileSize
public static function getUsage(): TFileSize
public static function getUsedRatio(): float
public static function getFree(): TFileSize
public static function isCritical(): bool
```

**Key Features:**
- Memory limit management
- Memory usage tracking
- Free memory calculation
- Critical memory detection
- Memory ratio monitoring
- Threshold-based alerting

### 2.3. DiskSpace (`Katu\Tools\System\DiskSpace`)
**Location:** `DiskSpace.php`

Disk space information and monitoring:

```php
// Key methods:
public function __construct(string $filesystem, string $mount, TFileSize $capacity, TFileSize $used)
public function setFilesystem(string $filesystem): DiskSpace
public function setMount(string $mount): DiskSpace
public function getMount(): string
public function setCapacity(TFileSize $capacity): DiskSpace
public function getCapacity(): TFileSize
public function setUsed(TFileSize $used): DiskSpace
public function getUsed(): TFileSize
public function getUsedPercentage(): float
public function getAvailable(): TFileSize
public function getAvailablePercentage(): float
```

**Key Features:**
- Disk space information
- Usage percentage calculation
- Available space calculation
- Mount point management
- Filesystem information

### 2.4. DiskSpaceMonitor (`Katu\Tools\System\DiskSpaceMonitor`)
**Location:** `DiskSpaceMonitor.php`

Abstract base class for disk space monitoring:

```php
// Key methods:
abstract public function getLimit()
abstract public function getIsPassed(): ?bool
public function setMount(string $mount): DiskSpaceMonitor
public function getMount(): string
public function getDiskSpace(): ?DiskSpace
public function getMessage(): string
public function setCooldown(?Timeout $cooldown): DiskSpaceMonitor
public function getCooldown(): Timeout
public function getIsWithinCooldown(): bool
public function getIsAlerting(): bool
public function alert(?callable $callback = null): DiskSpaceMonitor
```

**Key Features:**
- Abstract monitoring base
- Cooldown management
- Alerting system
- Message formatting
- Threshold monitoring

---

## 3. System Load Monitoring

### 3.1. CPU Load Monitoring
```php
// Get system load average
$loadAverage = System::getLoadAverage();
// Returns: [1.5, 1.2, 1.0] (1min, 5min, 15min)

// Get per-CPU load average
$loadPerCpu = System::getLoadAveragePerCpu();
// Returns: [0.75, 0.6, 0.5] (normalized per CPU)

// Get CPU count
$cpuCount = System::getNumberOfCpus();
// Returns: 4

// Check load threshold
try {
    System::assertMaxLoadAverage(2.0);
    // System load is acceptable
} catch (\Katu\Exceptions\LoadAverageExceededException $e) {
    // System load is too high
}
```

### 3.2. Load Average Integration
```php
// Job system integration
class DataProcessingJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            // Check system load before processing
            if (System::getLoadAveragePerCpu()[0] > 1.5) {
                $this->outputLine("System load too high, skipping job");
                return;
            }

            $this->processData();
        };
    }
}
```

### 3.3. Load Monitoring with Caching
```php
// System automatically caches CPU count for 1 day
$cpuCount = System::getNumberOfCpus();

// Load average is not cached (real-time)
$currentLoad = System::getLoadAverage();
```

---

## 4. Memory Management

### 4.1. Memory Usage Monitoring
```php
// Get current memory usage
$memoryUsage = Memory::getUsage();
// Returns: TFileSize object

// Get memory limit
$memoryLimit = Memory::getLimit();
// Returns: TFileSize object

// Get free memory
$freeMemory = Memory::getFree();
// Returns: TFileSize object

// Get memory usage ratio
$usageRatio = Memory::getUsedRatio();
// Returns: 0.75 (75% used)

// Check if memory is critical
if (Memory::isCritical()) {
    // Memory usage is above 90%
    $this->handleCriticalMemory();
}
```

### 4.2. Memory Limit Management
```php
// Set memory limit
$newLimit = new \Katu\Types\TFileSize("512M");
$success = Memory::setLimit($newLimit);

// Get current limit
$currentLimit = Memory::getLimit();

// Check memory status
$usage = Memory::getUsage();
$limit = Memory::getLimit();
$free = Memory::getFree();

echo "Memory Usage: {$usage->getReadable()}\n";
echo "Memory Limit: {$limit->getReadable()}\n";
echo "Free Memory: {$free->getReadable()}\n";
echo "Usage Ratio: " . round(Memory::getUsedRatio() * 100, 2) . "%\n";
```

### 4.3. Memory Monitoring in Applications
```php
class MemoryAwareController extends \Katu\Controllers\Controller
{
    public function processLargeData(ServerRequestInterface $request): ResponseInterface
    {
        // Check memory before processing
        if (Memory::isCritical()) {
            return $this->getErrorResponse("Insufficient memory");
        }

        // Process data in chunks
        $this->processDataInChunks();

        // Monitor memory during processing
        if (Memory::getUsedRatio() > 0.8) {
            $this->cleanupMemory();
        }

        return $this->getSuccessResponse();
    }
}
```

---

## 5. Disk Space Monitoring

### 5.1. Basic Disk Space Information
```php
// Get disk space collection
$diskSpaces = DiskSpaceCollection::createDefault();

// Get specific mount point
$rootDisk = $diskSpaces->getByMount("/");

// Get disk information
$capacity = $rootDisk->getCapacity();
$used = $rootDisk->getUsed();
$available = $rootDisk->getAvailable();
$usedPercentage = $rootDisk->getUsedPercentage();
$availablePercentage = $rootDisk->getAvailablePercentage();

echo "Capacity: {$capacity->getReadable()}\n";
echo "Used: {$used->getReadable()} ({$usedPercentage}%)\n";
echo "Available: {$available->getReadable()} ({$availablePercentage}%)\n";
```

### 5.2. Disk Space Monitoring
```php
// Create disk space monitor
class DiskSpaceAlertMonitor extends DiskSpaceMonitor
{
    public function getLimit()
    {
        return 0.85; // 85% threshold
    }

    public function getIsPassed(): ?bool
    {
        $diskSpace = $this->getDiskSpace();
        if (!$diskSpace) {
            return null;
        }

        return $diskSpace->getUsedPercentage() < $this->getLimit();
    }
}

// Use monitor
$monitor = new DiskSpaceAlertMonitor();
$monitor->setMount("/");

// Check if alerting is needed
if ($monitor->getIsAlerting()) {
    $monitor->alert(function($monitor) {
        $this->sendDiskSpaceAlert($monitor->getMessage());
    });
}
```

### 5.3. Disk Space Collection Management
```php
// Create disk space collection
$diskSpaces = new DiskSpaceCollection();

// Add disk spaces
$diskSpaces->addDiskSpace(new DiskSpace("ext4", "/", new TFileSize("100G"), new TFileSize("75G")));
$diskSpaces->addDiskSpace(new DiskSpace("ext4", "/var", new TFileSize("50G"), new TFileSize("30G")));

// Get by mount point
$rootDisk = $diskSpaces->getByMount("/");
$varDisk = $diskSpaces->getByMount("/var");

// Filter by usage threshold
$criticalDisks = $diskSpaces->filterByUsedPercentage(0.8);
```

---

## 6. System Health Monitoring

### 6.1. Comprehensive Health Check
```php
class SystemHealthChecker
{
    public function checkSystemHealth(): array
    {
        $health = [
            "cpu" => $this->checkCpuHealth(),
            "memory" => $this->checkMemoryHealth(),
            "disk" => $this->checkDiskHealth(),
            "overall" => "healthy"
        ];

        // Determine overall health
        if ($health["cpu"]["status"] === "critical" ||
            $health["memory"]["status"] === "critical" ||
            $health["disk"]["status"] === "critical") {
            $health["overall"] = "critical";
        } elseif ($health["cpu"]["status"] === "warning" ||
                 $health["memory"]["status"] === "warning" ||
                 $health["disk"]["status"] === "warning") {
            $health["overall"] = "warning";
        }

        return $health;
    }

    private function checkCpuHealth(): array
    {
        $loadPerCpu = System::getLoadAveragePerCpu();
        $currentLoad = $loadPerCpu[0];

        if ($currentLoad > 2.0) {
            return ["status" => "critical", "load" => $currentLoad];
        } elseif ($currentLoad > 1.0) {
            return ["status" => "warning", "load" => $currentLoad];
        }

        return ["status" => "healthy", "load" => $currentLoad];
    }

    private function checkMemoryHealth(): array
    {
        $usageRatio = Memory::getUsedRatio();

        if ($usageRatio > 0.9) {
            return ["status" => "critical", "usage" => $usageRatio];
        } elseif ($usageRatio > 0.8) {
            return ["status" => "warning", "usage" => $usageRatio];
        }

        return ["status" => "healthy", "usage" => $usageRatio];
    }

    private function checkDiskHealth(): array
    {
        $diskSpaces = DiskSpaceCollection::createDefault();
        $criticalDisks = $diskSpaces->filterByUsedPercentage(0.9);

        if (count($criticalDisks) > 0) {
            return ["status" => "critical", "disks" => $criticalDisks];
        }

        $warningDisks = $diskSpaces->filterByUsedPercentage(0.8);
        if (count($warningDisks) > 0) {
            return ["status" => "warning", "disks" => $warningDisks];
        }

        return ["status" => "healthy", "disks" => $diskSpaces];
    }
}
```

### 6.2. Automated Monitoring
```php
class SystemMonitorJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            $healthChecker = new SystemHealthChecker();
            $health = $healthChecker->checkSystemHealth();

            if ($health["overall"] === "critical") {
                $this->sendCriticalAlert($health);
            } elseif ($health["overall"] === "warning") {
                $this->sendWarningAlert($health);
            }

            $this->logHealthStatus($health);
        };
    }

    private function sendCriticalAlert(array $health): void
    {
        // Send critical alert to administrators
        $this->sendEmail("System Critical", $this->formatHealthMessage($health));
    }

    private function sendWarningAlert(array $health): void
    {
        // Send warning alert
        $this->sendEmail("System Warning", $this->formatHealthMessage($health));
    }
}
```

---

## 7. Performance Monitoring

### 7.1. System Performance Metrics
```php
class PerformanceMonitor
{
    public function getSystemMetrics(): array
    {
        return [
            "timestamp" => time(),
            "cpu" => [
                "count" => System::getNumberOfCpus(),
                "load" => System::getLoadAverage(),
                "load_per_cpu" => System::getLoadAveragePerCpu()
            ],
            "memory" => [
                "limit" => Memory::getLimit()->getInB()->getAmount(),
                "usage" => Memory::getUsage()->getInB()->getAmount(),
                "free" => Memory::getFree()->getInB()->getAmount(),
                "ratio" => Memory::getUsedRatio(),
                "critical" => Memory::isCritical()
            ],
            "disk" => $this->getDiskMetrics()
        ];
    }

    private function getDiskMetrics(): array
    {
        $diskSpaces = DiskSpaceCollection::createDefault();
        $metrics = [];

        foreach ($diskSpaces as $disk) {
            $metrics[] = [
                "mount" => $disk->getMount(),
                "capacity" => $disk->getCapacity()->getInB()->getAmount(),
                "used" => $disk->getUsed()->getInB()->getAmount(),
                "available" => $disk->getAvailable()->getInB()->getAmount(),
                "used_percentage" => $disk->getUsedPercentage(),
                "available_percentage" => $disk->getAvailablePercentage()
            ];
        }

        return $metrics;
    }
}
```

### 7.2. Performance Alerting
```php
class PerformanceAlertManager
{
    public function checkPerformanceThresholds(): void
    {
        // Check CPU load
        $loadPerCpu = System::getLoadAveragePerCpu();
        if ($loadPerCpu[0] > 1.5) {
            $this->alert("High CPU load: {$loadPerCpu[0]}");
        }

        // Check memory usage
        if (Memory::isCritical()) {
            $this->alert("Critical memory usage: " . Memory::getUsedRatio());
        }

        // Check disk space
        $diskSpaces = DiskSpaceCollection::createDefault();
        foreach ($diskSpaces as $disk) {
            if ($disk->getUsedPercentage() > 0.9) {
                $this->alert("Critical disk space on {$disk->getMount()}: {$disk->getUsedPercentage()}");
            }
        }
    }

    private function alert(string $message): void
    {
        \App\App::getLogger()->warning("Performance Alert", [
            "message" => $message,
            "timestamp" => time()
        ]);
    }
}
```

---

## 8. Best Practices

### 8.1. Monitoring Strategy
- Monitor key system metrics regularly
- Set appropriate thresholds for alerts
- Implement cooldown periods for alerts
- Use caching for expensive operations
- Monitor trends over time

### 8.2. Performance Considerations
- Cache system information when appropriate
- Use efficient monitoring intervals
- Implement graceful degradation
- Monitor monitoring system performance
- Use appropriate alerting mechanisms

### 8.3. Alert Management
- Implement alert cooldowns
- Use different alert levels
- Provide actionable alert messages
- Monitor alert effectiveness
- Implement alert escalation

### 8.4. System Integration
- Integrate with job system for monitoring
- Use event system for alerting
- Implement health check endpoints
- Monitor application-specific metrics
- Use logging for monitoring data

---

## 9. Common Patterns

### 9.1. System Health Endpoint
```php
class HealthController extends \Katu\Controllers\Controller
{
    public function getHealth(ServerRequestInterface $request): ResponseInterface
    {
        $healthChecker = new SystemHealthChecker();
        $health = $healthChecker->checkSystemHealth();

        $statusCode = $health["overall"] === "healthy" ? 200 : 503;

        return $response
            ->withStatus($statusCode)
            ->withHeader("Content-Type", "application/json")
            ->withBody(new RestResponse($health)->getStream());
    }
}
```

### 9.2. Monitoring Dashboard
```php
class MonitoringDashboard
{
    public function getDashboardData(): array
    {
        return [
            "system" => [
                "cpu_count" => System::getNumberOfCpus(),
                "load_average" => System::getLoadAverage(),
                "load_per_cpu" => System::getLoadAveragePerCpu()
            ],
            "memory" => [
                "limit" => Memory::getLimit()->getReadable(),
                "usage" => Memory::getUsage()->getReadable(),
                "free" => Memory::getFree()->getReadable(),
                "ratio" => Memory::getUsedRatio(),
                "critical" => Memory::isCritical()
            ],
            "disk" => $this->getDiskSpaceData()
        ];
    }
}
```

### 9.3. Automated Cleanup
```php
class SystemCleanupJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            // Check disk space
            $diskSpaces = DiskSpaceCollection::createDefault();
            foreach ($diskSpaces as $disk) {
                if ($disk->getUsedPercentage() > 0.8) {
                    $this->cleanupDiskSpace($disk);
                }
            }

            // Check memory
            if (Memory::getUsedRatio() > 0.8) {
                $this->cleanupMemory();
            }
        };
    }
}
```

---

## 10. Troubleshooting

### 10.1. Common Issues
- **Load Average Issues:** Check system load and CPU count
- **Memory Issues:** Verify memory limits and usage
- **Disk Space Issues:** Check disk space and mount points
- **Monitoring Issues:** Verify monitoring configuration

### 10.2. Debugging
- Use system metrics for debugging
- Check monitoring logs
- Verify threshold configurations
- Test alerting mechanisms

### 10.3. Performance Issues
- Monitor monitoring system performance
- Optimize monitoring intervals
- Use appropriate caching strategies
- Implement efficient alerting

---

This documentation provides comprehensive coverage of the KATU System Monitoring system. For specific implementation details, refer to the system classes in `src/Tools/System/` and the integration with other framework components.
