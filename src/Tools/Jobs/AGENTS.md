# Job System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Job system in the KATU framework. The job system provides background job processing, scheduling, lock management, and console integration for automated tasks.

---

## 1. System Overview

### 1.1. Purpose
- **Background Processing:** Asynchronous job execution and management
- **Job Scheduling:** Time-based job scheduling with cron-like patterns
- **Lock Management:** Prevent concurrent job execution with lock system
- **Progress Tracking:** Job progress monitoring and status reporting
- **Console Integration:** Command-line job execution and monitoring
- **Load Balancing:** System load monitoring and job throttling
- **Package Serialization:** Job serialization for queue systems

### 1.2. Architecture
- **Core Classes:** `Job`, `JobCollection`, `Schedule`, `ScheduleCollection`
- **Lock System:** Integration with lock procedures for concurrency control
- **Scheduling:** Time-based scheduling with regex pattern matching
- **Console Integration:** Symfony Console integration for CLI execution
- **Package System:** Serialization support for job transmission
- **Progress System:** Job progress tracking and reporting

---

## 2. Core Job Classes

### 2.1. Job (`Katu\Tools\Jobs\Job`)
**Location:** `Job.php`

Abstract base class for background jobs:

```php
// Key methods:
abstract public function getCallback(): callable
public function __construct(array $args = [])
public function getPackage(): Package
public static function createFromPackage(Package $package): ?Job
public function getTitle(): string
public function getClass(): TClass
public function setArgs(array $args = []): Job
public function getArgs(): array
public function getInterval(): Timeout
public function setTimeout(?Timeout $timeout): Job
public function getTimeout(): Timeout
public function isExpired(): bool
public function isRunning(): bool
public function run(): bool
public function getProgress(): ?float
public function canProcess(): bool
```

**Key Features:**
- Abstract job definition
- Configurable intervals and timeouts
- Lock checking and management
- Console integration
- Package serialization
- Default intervals and timeouts

### 2.2. JobCollection (`Katu\Tools\Jobs\JobCollection`)
**Location:** `JobCollection.php`

Collection for managing multiple jobs:

```php
// Key methods:
public function addJob(Job $job): JobCollection
public function addJobs(JobCollection $jobs): JobCollection
public function setCycles(int $cycles): JobCollection
public function getCycles(): int
public function setCyclePause(?Seconds $cyclePause): JobCollection
public function getCyclePause(): Seconds
public function setLockTimeout(Timeout $lockTimeout): JobCollection
public function getLockTimeout(): Timeout
public function filterExpired(): JobCollection
public function filterScheduled(Time $time): JobCollection
public function getExecutable(): JobCollection
public function getCallback(): callable
public function run()
```

**Key Features:**
- Job collection management
- Scheduling and filtering
- Batch execution
- Cycle management
- Load balancing
- Lock timeout configuration

### 2.3. Schedule (`Katu\Tools\Jobs\Schedule`)
**Location:** `Schedule.php`

Time-based job scheduling:

```php
// Key methods:
public function __construct(?array $minutes = null, ?array $hours = null, ?array $days = null, ?array $months = null, ?array $years = null)
public function getMinutes(): array
public function getHours(): array
public function getDays(): array
public function getMonths(): array
public function getYears(): array
public static function getRangeRegexp(array $range): string
public function getRegexp(): string
```

**Key Features:**
- Cron-like scheduling
- Time range specification
- Regex pattern generation
- Flexible time configuration

### 2.4. ScheduleCollection (`Katu\Tools\Jobs\ScheduleCollection`)
**Location:** `ScheduleCollection.php`

Collection for managing multiple schedules.

---

## 3. Job Implementation

### 3.1. Basic Job Implementation
```php
class ExampleJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            // Job logic here
            $this->processData();
        };
    }

    private function processData()
    {
        // Process data
        $this->incrementProcessed();
    }
}
```

### 3.2. Job with Custom Configuration
```php
class DataProcessingJob extends \Katu\Tools\Jobs\Job
{
    public function __construct(array $args = [])
    {
        parent::__construct($args);

        // Set custom interval
        $this->setInterval(new \Katu\Tools\Calendar\Timeout("2 hours"));

        // Set custom timeout
        $this->setTimeout(new \Katu\Tools\Calendar\Timeout("30 minutes"));

        // Set load average limit
        $this->setMaxLoadAverage(1.0);
    }

    public function getCallback(): callable
    {
        return function() {
            $this->processBatch();
        };
    }

    private function processBatch()
    {
        $limit = $this->getResolvedLimit() ?: 100;
        $processed = 0;

        while ($processed < $limit && $this->canProcess()) {
            $this->processItem();
            $this->incrementProcessed();
            $processed++;
        }
    }
}
```

### 3.3. Job with Scheduling
```php
class ScheduledJob extends \Katu\Tools\Jobs\Job
{
    public function getDefaultSchedules(): ?ScheduleCollection
    {
        return new ScheduleCollection([
            // Run every hour at minute 0
            new Schedule([0], range(0, 23)),
            // Run every day at 2 AM
            new Schedule([0], [2], range(1, 31)),
        ]);
    }

    public function getCallback(): callable
    {
        return function() {
            $this->performScheduledTask();
        };
    }
}
```

---

## 4. Job Execution

### 4.1. Single Job Execution
```php
// Create job
$job = new ExampleJob(["param1" => "value1"]);

// Run job
$success = $job->run();

if ($success) {
    echo "Job completed successfully";
} else {
    echo "Job failed or was locked";
}
```

### 4.2. Job Collection Execution
```php
// Create job collection
$jobs = new JobCollection();
$jobs->addJob(new DataProcessingJob());
$jobs->addJob(new CleanupJob());

// Configure collection
$jobs->setCycles(3);
$jobs->setCyclePause(new Seconds(30));
$jobs->setMaxLoadAverage(1.5);

// Run all jobs
$jobs->run();
```

### 4.3. Console Integration
```php
// Console command integration
class JobCommand extends \Symfony\Component\Console\Command\Command
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $job = new ExampleJob();
        $job->setConsoleOutput($output);

        $success = $job->run();

        return $success ? 0 : 1;
    }
}
```

---

## 5. Job Scheduling

### 5.1. Basic Scheduling
```php
// Create schedule for every hour
$schedule = new Schedule([0], range(0, 23));

// Create schedule for specific times
$schedule = new Schedule(
    [0, 30],           // Every 30 minutes
    [9, 17],           // 9 AM to 5 PM
    range(1, 31),      // Every day
    range(1, 12),      // Every month
    [2024, 2025]       // Specific years
);
```

### 5.2. Job with Multiple Schedules
```php
class FlexibleJob extends \Katu\Tools\Jobs\Job
{
    public function getDefaultSchedules(): ?ScheduleCollection
    {
        return new ScheduleCollection([
            // Run every 15 minutes during business hours
            new Schedule([0, 15, 30, 45], range(9, 17)),
            // Run once daily at midnight
            new Schedule([0], [0], range(1, 31)),
        ]);
    }
}
```

### 5.3. Schedule Pattern Matching
```php
// Check if job should run at specific time
$time = new \Katu\Tools\Calendar\Time("2024-01-15 14:30:00");
$shouldRun = $job->isScheduled($time);

// Get executable jobs for current time
$executableJobs = $jobCollection->filterScheduled(new Time());
```

---

## 6. Progress Tracking

### 6.1. Job Progress Management
```php
class ProgressJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            $total = $this->getTotal() ?: $this->calculateTotal();
            $this->setTotal($total);

            while ($this->canProcess()) {
                $this->processItem();
                $this->incrementProcessed();

                // Log progress
                $progress = $this->getProgress();
                $this->outputLine("Progress: " . round($progress * 100, 2) . "%");
            }
        };
    }

    private function calculateTotal(): int
    {
        // Calculate total items to process
        return 1000;
    }
}
```

### 6.2. Progress Persistence
```php
// Set total for job
$job->setTotal(1000);

// Get progress percentage
$progress = $job->getProgress(); // Returns 0.0 to 1.0

// Check remaining items
$remaining = $job->getRemaining();

// Check if job can continue processing
$canProcess = $job->canProcess();
```

---

## 7. Lock Management

### 7.1. Job Locking
```php
// Enable/disable lock checking
$job->setIsLockChecked(true);

// Check if job is locked
$isExecutable = $job->getProcedure()->getIsExecutable();

// Get lock procedure
$procedure = $job->getProcedure();
```

### 7.2. Collection Locking
```php
// Set lock timeout for collection
$jobs->setLockTimeout(new Timeout("2 hours"));

// Run with lock management
$jobs->run();
```

---

## 8. Load Balancing

### 8.1. System Load Monitoring
```php
// Set maximum load average
$job->setMaxLoadAverage(1.5);

// Check system load
$loadAverage = \Katu\Tools\System\System::getLoadAveragePerCpu()[0];

// Job will not run if load is too high
if ($loadAverage >= $job->getMaxLoadAverage()) {
    // Job skipped due to high load
}
```

### 8.2. Collection Load Balancing
```php
// Set collection load limit
$jobs->setMaxLoadAverage(2.0);

// Jobs will be throttled based on system load
$jobs->run();
```

---

## 9. Package Serialization

### 9.1. Job Serialization
```php
// Create job package
$job = new ExampleJob(["param" => "value"]);
$package = $job->getPackage();

// Serialize to string
$serialized = $package->getPayload();
```

### 9.2. Job Deserialization
```php
// Create job from package
$restoredJob = Job::createFromPackage($package);

// Run restored job
$success = $restoredJob->run();
```

---

## 10. Best Practices

### 10.1. Job Design
- Keep jobs focused on single tasks
- Implement proper error handling
- Use appropriate timeouts and intervals
- Monitor system load
- Implement progress tracking

### 10.2. Scheduling
- Use appropriate schedule patterns
- Consider system load when scheduling
- Implement fallback schedules
- Monitor job execution times

### 10.3. Error Handling
- Implement proper exception handling
- Log job failures
- Implement retry mechanisms
- Monitor job health

### 10.4. Performance
- Use appropriate batch sizes
- Implement progress tracking
- Monitor system resources
- Optimize job execution

---

## 11. Common Patterns

### 11.1. Data Processing Job
```php
class DataProcessingJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            $limit = $this->getResolvedLimit() ?: 100;
            $processed = 0;

            while ($processed < $limit && $this->canProcess()) {
                $item = $this->getNextItem();
                if ($item) {
                    $this->processItem($item);
                    $this->incrementProcessed();
                    $processed++;
                } else {
                    break;
                }
            }
        };
    }

    private function getNextItem()
    {
        // Get next item to process
        return $this->getDataItem();
    }

    private function processItem($item)
    {
        // Process individual item
        $this->transformData($item);
    }
}
```

### 11.2. Cleanup Job
```php
class CleanupJob extends \Katu\Tools\Jobs\Job
{
    public function getDefaultSchedules(): ?ScheduleCollection
    {
        return new ScheduleCollection([
            // Run daily at 2 AM
            new Schedule([0], [2], range(1, 31)),
        ]);
    }

    public function getCallback(): callable
    {
        return function() {
            $this->cleanupExpiredData();
            $this->cleanupTempFiles();
            $this->cleanupLogs();
        };
    }
}
```

### 11.3. Notification Job
```php
class NotificationJob extends \Katu\Tools\Jobs\Job
{
    public function getCallback(): callable
    {
        return function() {
            $notifications = $this->getPendingNotifications();

            foreach ($notifications as $notification) {
                $this->sendNotification($notification);
                $this->incrementProcessed();
            }
        };
    }
}
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Job Not Running:** Check scheduling and lock status
- **High Load:** Adjust load average limits
- **Timeout Issues:** Increase job timeout
- **Lock Issues:** Check lock configuration

### 12.2. Debugging
- Use console output for job monitoring
- Check job status and progress
- Monitor system load
- Review job logs

### 12.3. Performance Issues
- Optimize job processing logic
- Adjust batch sizes
- Monitor system resources
- Implement proper scheduling

---

This documentation provides comprehensive coverage of the KATU Job system. For specific implementation details, refer to the job classes in `src/Tools/Jobs/` and the integration with the lock and console systems.
