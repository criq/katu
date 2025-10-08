# Calendar System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Calendar system in the KATU framework. The calendar system provides advanced date and time manipulation, period calculations, and time interval management capabilities.

---

## 1. System Overview

### 1.1. Purpose
- **Date/Time Manipulation:** Advanced DateTime operations with timezone support
- **Period Management:** Day, Week, Month, Year period calculations
- **Interval Handling:** Time interval calculations and validation
- **Duration Management:** Seconds, timeouts, and duration calculations
- **Collection Support:** Collections for managing multiple time periods
- **Validation:** Input validation for date/time parameters

### 1.2. Architecture
- **Core Classes:** `Time`, `Day`, `Week`, `Month`, `Year`
- **Duration Classes:** `Seconds`, `Interval`, `Timeout`
- **Collection Classes:** `TimeCollection`, `DayCollection`, `WeekCollection`, `MonthCollection`, `IntervalCollection`
- **Interface:** `TimeUnitInterface` for consistent time unit behavior
- **Validation:** Built-in validation for time intervals

---

## 2. Core Calendar Classes

### 2.1. Time (`Katu\Tools\Calendar\Time`)
**Location:** `Time.php`

Extended DateTime class with additional functionality:

```php
// Key methods:
public function __construct($time = null, ?\DateTimeZone $timezone = null)
public function __toString(): string
public static function createFromTimestamp($timestamp): \DateTime
public static function createFromDateTime(\DateTime $dateTime): Time
public static function createFromString(?string $string, bool $timeRequired): ?Time
public function getDbDateTimeFormat(): string
public function isToday(): bool
public function isInFuture(): bool
public function getAge(): Seconds
public function change($value): Time
public function getThisWeekday(string $weekday): Time
public function setDay(int $day): Time
public function setMonth(int $month): Time
public function setYear(int $year): Time
```

**Key Features:**
- Multiple date format parsing
- Database format conversion
- Timezone handling
- Relative time calculations
- Time manipulation methods
- Age calculation
- Weekday operations

### 2.2. Day (`Katu\Tools\Calendar\Day`)
**Location:** `Day.php`

Day period implementation:

```php
// Key methods:
public function __toString(): string
public function getTime(): Time
public function getStart(): Time
public function getEnd(): Time
public function getInterval(): Interval
public function getDays(): DayCollection
public function getIsToday(): bool
public function getIsInPast(): bool
public function getIsInFuture(): bool
```

**Key Features:**
- Day start/end calculations
- Day interval management
- Past/future day detection
- Day collection generation

### 2.3. Week (`Katu\Tools\Calendar\Week`)
**Location:** `Week.php`

Week period implementation:

```php
// Key methods:
public function __toString(): string
public function getTime(): Time
public function getStartDay(): Day
public function getStart(): Time
public function getEndDay(): Day
public function getEnd(): Time
public function getInterval(): Interval
public function getDays(): DayCollection
public function getWeeks(): WeekCollection
```

**Key Features:**
- Week start/end day calculations
- Week interval management
- Day collection within week
- Week collection generation

### 2.4. Month (`Katu\Tools\Calendar\Month`)
**Location:** `Month.php`

Month period implementation:

```php
// Key methods:
public function __toString(): string
public function getTime(): Time
public function getStartDay(): Day
public function getStart(): Time
public function getEndDay(): Day
public function getEnd(): Time
public function getInterval(): Interval
public function getDays(): DayCollection
public function getWeeks(): WeekCollection
public function getMonths(): MonthCollection
```

**Key Features:**
- Month start/end day calculations
- Month interval management
- Day and week collections within month
- Month collection generation

### 2.5. Year (`Katu\Tools\Calendar\Year`)
**Location:** `Year.php`

Year period implementation:

```php
// Key methods:
public function __toString(): string
public function getTime(): Time
public function getStartDay(): Day
public function getStart(): Time
public function getEndDay(): Day
public function getEnd(): Time
public function getInterval(): Interval
public function getDays(): DayCollection
public function getWeeks(): WeekCollection
public function getMonths(): MonthCollection
```

**Key Features:**
- Year start/end day calculations
- Year interval management
- All period collections within year
- Year collection generation

---

## 3. Duration Classes

### 3.1. Seconds (`Katu\Tools\Calendar\Seconds`)
**Location:** `Seconds.php`

Precise time measurement in seconds:

```php
// Key methods:
public function __construct(float $value)
public function __toString(): string
public static function createFromString(string $string): Seconds
public function getValue(): float
public function getTime(): Time
public function getMinutes(): float
public function getHours(): float
public function getDays(): float
```

**Key Features:**
- Precise second calculations
- Time unit conversions
- String parsing
- Time object generation

### 3.2. Interval (`Katu\Tools\Calendar\Interval`)
**Location:** `Interval.php`

Time interval between two points:

```php
// Key methods:
public function __construct(Time $start, Time $end)
public function __toString(): string
public static function validate(Param $startParam, Param $endParam): Validation
public function getStart(): Time
public function getEnd(): Time
public function getSeconds(): Seconds
public function getDays(): float
public function getIsInPast(): bool
public function getIsInFuture(): bool
public function getIsActive(): bool
public function contains(Time $time): bool
public function overlaps(Interval $interval): bool
```

**Key Features:**
- Start/end time management
- Duration calculations
- Interval validation
- Overlap detection
- Active interval checking

### 3.3. Timeout (`Katu\Tools\Calendar\Timeout`)
**Location:** `Timeout.php`

Timeout management with reference time:

```php
// Key methods:
public function __construct($timeout, ?Time $referenceTime = null)
public function setTimeout(string $value): Timeout
public function getTimeout(): string
public function setReferenceTime(?Time $referenceTime): Timeout
public function getReferenceTime(): ?Time
public function getIsExpired(): bool
public function getRemainingSeconds(): Seconds
```

**Key Features:**
- Timeout string parsing
- Reference time management
- Expiration checking
- Remaining time calculation

---

## 4. Collection Classes

### 4.1. TimeCollection (`TimeCollection.php`)
**Location:** `TimeCollection.php`

Collection for managing multiple Time objects:

```php
// Key methods:
public function addTime(Time $time): TimeCollection
public function getTimes(): array
public function getFirst(): ?Time
public function getLast(): ?Time
public function getSorted(): TimeCollection
```

### 4.2. DayCollection (`DayCollection.php`)
**Location:** `DayCollection.php`

Collection for managing multiple Day objects:

```php
// Key methods:
public function addDay(Day $day): DayCollection
public function getDays(): array
public function getFirst(): ?Day
public function getLast(): ?Day
public function getSorted(): DayCollection
```

### 4.3. WeekCollection (`WeekCollection.php`)
**Location:** `WeekCollection.php`

Collection for managing multiple Week objects:

```php
// Key methods:
public function addWeek(Week $week): WeekCollection
public function getWeeks(): array
public function getFirst(): ?Week
public function getLast(): ?Week
public function getSorted(): WeekCollection
```

### 4.4. MonthCollection (`MonthCollection.php`)
**Location:** `MonthCollection.php`

Collection for managing multiple Month objects:

```php
// Key methods:
public function addMonth(Month $month): MonthCollection
public function getMonths(): array
public function getFirst(): ?Month
public function getLast(): ?Month
public function getSorted(): MonthCollection
```

### 4.5. IntervalCollection (`IntervalCollection.php`)
**Location:** `IntervalCollection.php`

Collection for managing multiple Interval objects:

```php
// Key methods:
public function addInterval(Interval $interval): IntervalCollection
public function getIntervals(): array
public function getFirst(): ?Interval
public function getLast(): ?Interval
public function getSorted(): IntervalCollection
```

---

## 5. Usage Patterns

### 5.1. Basic Time Operations
```php
use Katu\Tools\Calendar\Time;

// Create time objects
$now = new Time();
$specificTime = new Time("2024-01-15 14:30:00");
$fromTimestamp = Time::createFromTimestamp(1705320600);

// String conversion
echo $now; // Outputs: 2024-01-15 14:30:00

// Database format
$dbFormat = $now->getDbDateTimeFormat(); // Y-m-d H:i:s format

// Time checks
if ($specificTime->isToday()) {
    echo "This is today";
}

if ($specificTime->isInFuture()) {
    echo "This is in the future";
}

// Age calculation
$age = $specificTime->getAge();
echo "Age: " . $age->getDays() . " days";
```

### 5.2. Day Operations
```php
use Katu\Tools\Calendar\Day;

// Create day
$today = new Day();
$specificDay = new Day("2024-01-15");

// Day boundaries
$start = $today->getStart(); // 00:00:00
$end = $today->getEnd();     // 23:59:59.999999

// Day interval
$interval = $today->getInterval();
echo "Day duration: " . $interval->getSeconds() . " seconds";

// Day checks
if ($today->getIsToday()) {
    echo "This is today";
}

if ($specificDay->getIsInPast()) {
    echo "This day is in the past";
}

// Get all days in a range
$days = $today->getDays();
foreach ($days as $day) {
    echo "Day: " . $day;
}
```

### 5.3. Week Operations
```php
use Katu\Tools\Calendar\Week;

// Create week
$currentWeek = new Week();
$specificWeek = new Week("2024-01-15");

// Week boundaries
$startDay = $currentWeek->getStartDay(); // Monday
$endDay = $currentWeek->getEndDay();     // Sunday

// Week interval
$interval = $currentWeek->getInterval();
echo "Week duration: " . $interval->getDays() . " days";

// Get all days in week
$days = $currentWeek->getDays();
foreach ($days as $day) {
    echo "Day in week: " . $day;
}

// Get all weeks in a range
$weeks = $currentWeek->getWeeks();
foreach ($weeks as $week) {
    echo "Week: " . $week;
}
```

### 5.4. Month Operations
```php
use Katu\Tools\Calendar\Month;

// Create month
$currentMonth = new Month();
$specificMonth = new Month("2024-01");

// Month boundaries
$startDay = $currentMonth->getStartDay(); // First day of month
$endDay = $currentMonth->getEndDay();     // Last day of month

// Month interval
$interval = $currentMonth->getInterval();
echo "Month duration: " . $interval->getDays() . " days";

// Get all days in month
$days = $currentMonth->getDays();
foreach ($days as $day) {
    echo "Day in month: " . $day;
}

// Get all weeks in month
$weeks = $currentMonth->getWeeks();
foreach ($weeks as $week) {
    echo "Week in month: " . $week;
}
```

### 5.5. Year Operations
```php
use Katu\Tools\Calendar\Year;

// Create year
$currentYear = new Year();
$specificYear = new Year("2024");

// Year boundaries
$startDay = $currentYear->getStartDay(); // January 1st
$endDay = $currentYear->getEndDay();     // December 31st

// Year interval
$interval = $currentYear->getInterval();
echo "Year duration: " . $interval->getDays() . " days";

// Get all periods in year
$days = $currentYear->getDays();
$weeks = $currentYear->getWeeks();
$months = $currentYear->getMonths();
```

### 5.6. Interval Operations
```php
use Katu\Tools\Calendar\Interval;
use Katu\Tools\Calendar\Time;

// Create interval
$start = new Time("2024-01-01 09:00:00");
$end = new Time("2024-01-01 17:00:00");
$interval = new Interval($start, $end);

// Interval properties
echo "Duration: " . $interval->getSeconds() . " seconds";
echo "Duration: " . $interval->getDays() . " days";

// Interval checks
if ($interval->getIsActive()) {
    echo "Interval is currently active";
}

if ($interval->getIsInPast()) {
    echo "Interval is in the past";
}

// Check if time is within interval
$checkTime = new Time("2024-01-01 12:00:00");
if ($interval->contains($checkTime)) {
    echo "Time is within interval";
}

// Check interval overlap
$otherInterval = new Interval(
    new Time("2024-01-01 14:00:00"),
    new Time("2024-01-01 18:00:00")
);

if ($interval->overlaps($otherInterval)) {
    echo "Intervals overlap";
}
```

### 5.7. Timeout Operations
```php
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Calendar\Time;

// Create timeout
$timeout = new Timeout("+1 hour");
$timeoutWithRef = new Timeout("+30 minutes", new Time("2024-01-01 10:00:00"));

// Check expiration
if ($timeout->getIsExpired()) {
    echo "Timeout has expired";
}

// Get remaining time
$remaining = $timeout->getRemainingSeconds();
echo "Remaining: " . $remaining->getMinutes() . " minutes";
```

### 5.8. Collection Operations
```php
use Katu\Tools\Calendar\DayCollection;
use Katu\Tools\Calendar\Day;

// Create collection
$days = new DayCollection();

// Add days
$days->addDay(new Day("2024-01-01"));
$days->addDay(new Day("2024-01-02"));
$days->addDay(new Day("2024-01-03"));

// Get first and last
$first = $days->getFirst();
$last = $days->getLast();

// Get sorted collection
$sorted = $days->getSorted();

// Iterate through days
foreach ($days->getDays() as $day) {
    echo "Day: " . $day;
}
```

---

## 6. Advanced Features

### 6.1. Time Manipulation
```php
use Katu\Tools\Calendar\Time;

$time = new Time("2024-01-15 14:30:00");

// Change specific parts
$newTime = $time->change("+1 day");
$newTime = $time->change("+2 hours");
$newTime = $time->change("+30 minutes");

// Set specific values
$newTime = $time->setDay(20);
$newTime = $time->setMonth(6);
$newTime = $time->setYear(2025);

// Get specific weekday
$monday = $time->getThisWeekday("Monday");
$friday = $time->getThisWeekday("Friday");
```

### 6.2. Period Calculations
```php
use Katu\Tools\Calendar\Day;
use Katu\Tools\Calendar\Week;
use Katu\Tools\Calendar\Month;

$day = new Day("2024-01-15");

// Get all days in week containing this day
$week = new Week($day);
$weekDays = $week->getDays();

// Get all days in month containing this day
$month = new Month($day);
$monthDays = $month->getDays();

// Get all weeks in month
$monthWeeks = $month->getWeeks();
```

### 6.3. Interval Validation
```php
use Katu\Tools\Calendar\Interval;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

// Validate interval parameters
$startParam = new Param("start", "2024-01-01 09:00:00");
$endParam = new Param("end", "2024-01-01 17:00:00");

$validation = Interval::validate($startParam, $endParam);

if ($validation->hasErrors()) {
    // Handle validation errors
    foreach ($validation->getErrors() as $error) {
        echo "Error: " . $error->getMessage();
    }
} else {
    // Create valid interval
    $interval = new Interval(
        $validation->getParams()[0]->getOutput(),
        $validation->getParams()[1]->getOutput()
    );
}
```

### 6.4. Duration Calculations
```php
use Katu\Tools\Calendar\Seconds;
use Katu\Tools\Calendar\Time;

// Create seconds from value
$seconds = new Seconds(3600); // 1 hour
echo "Hours: " . $seconds->getHours();
echo "Minutes: " . $seconds->getMinutes();
echo "Days: " . $seconds->getDays();

// Create seconds from string
$secondsFromString = Seconds::createFromString("+1 hour");

// Convert to time
$time = $seconds->getTime();
```

---

## 7. Best Practices

### 7.1. Timezone Handling
- Always specify timezone when creating Time objects
- Use consistent timezone across the application
- Convert to UTC for storage and calculations

### 7.2. Performance
- Use appropriate period classes for calculations
- Cache frequently used time calculations
- Avoid creating unnecessary Time objects

### 7.3. Validation
- Always validate time inputs
- Use Interval::validate() for interval validation
- Check for valid date ranges

### 7.4. Collections
- Use collections for managing multiple time periods
- Sort collections when order matters
- Use appropriate collection types for different periods

---

## 8. Integration Examples

### 8.1. Controller Integration
```php
class CalendarController extends Controller
{
    public function getDaysInRange(ServerRequestInterface $request): ResponseInterface
    {
        $startDate = $request->getQueryParams()['start'] ?? null;
        $endDate = $request->getQueryParams()['end'] ?? null;

        if (!$startDate || !$endDate) {
            return $this->errorResponse("Start and end dates are required");
        }

        $start = new Time($startDate);
        $end = new Time($endDate);

        $interval = new Interval($start, $end);
        $days = $interval->getDays();

        return $this->jsonResponse([
            'days' => array_map(fn($day) => (string)$day, $days->getDays())
        ]);
    }
}
```

### 8.2. Model Integration
```php
class Event extends Model
{
    public function getStartTime(): Time
    {
        return new Time($this->startTime);
    }

    public function getEndTime(): Time
    {
        return new Time($this->endTime);
    }

    public function getInterval(): Interval
    {
        return new Interval($this->getStartTime(), $this->getEndTime());
    }

    public function getDuration(): Seconds
    {
        return $this->getInterval()->getSeconds();
    }
}
```

### 8.3. Template Integration
```twig
{# In Twig template #}
{% set event = eventModel %}
{% set interval = event.getInterval() %}
{% set duration = interval.getSeconds() %}

<div class="event">
    <h3>{{ event.title }}</h3>
    <p>Start: {{ event.getStartTime() }}</p>
    <p>End: {{ event.getEndTime() }}</p>
    <p>Duration: {{ duration.getHours() }} hours</p>
</div>
```

---

## 9. Common Patterns

### 9.1. Date Range Pattern
```php
// Date range calculations
$start = new Time("2024-01-01");
$end = new Time("2024-12-31");
$interval = new Interval($start, $end);

$days = $interval->getDays();
$weeks = $interval->getWeeks();
$months = $interval->getMonths();
```

### 9.2. Time Period Pattern
```php
// Time period calculations
$period = new Day("2024-06-15");
$week = $period->getWeek();
$month = $period->getMonth();
$year = $period->getYear();

$nextDay = $period->getNext();
$previousDay = $period->getPrevious();
```

### 9.3. Duration Pattern
```php
// Duration calculations
$duration = new Seconds(3600); // 1 hour
$timeout = new Timeout("2 hours");

$total = $duration->add($timeout);
$remaining = $timeout->subtract($duration);
```

---

## 10. Troubleshooting

### 10.1. Common Issues
- **Invalid Date Format:** Use proper date format strings
- **Timezone Issues:** Ensure consistent timezone usage
- **Interval Validation:** Check start/end time order
- **Collection Sorting:** Use getSorted() for ordered collections

### 10.2. Debugging
- Use `var_dump()` to inspect time objects
- Check timezone settings
- Validate interval parameters
- Test with simple date ranges first

---

This documentation provides comprehensive coverage of the Calendar system. For specific implementation details, refer to the source code in `src/Tools/Calendar/`.
