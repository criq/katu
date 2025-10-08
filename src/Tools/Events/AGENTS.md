# Event System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Event system in the KATU framework. The event system provides event dispatching, listener management, pattern matching, and event handling for decoupled application components.

---

## 1. System Overview

### 1.1. Purpose
- **Event Dispatching:** Publish-subscribe pattern for application events
- **Listener Management:** Register and manage event listeners
- **Pattern Matching:** Flexible event name pattern matching
- **Decoupled Architecture:** Loose coupling between application components
- **Event Handling:** Structured event processing and management
- **Error Handling:** Robust error handling for event listeners

### 1.2. Architecture
- **Core Classes:** `Dispatcher`, `Event`, `Listener`, `ListenerCollection`
- **Pattern System:** `Pattern`, `PatternCollection` - Event name pattern matching
- **Event Dispatching:** Centralized event dispatching and management
- **Listener Registration:** Dynamic listener registration and management
- **Pattern Matching:** Wildcard and regex pattern support
- **Error Handling:** Exception handling for event listeners

---

## 2. Core Event Classes

### 2.1. Dispatcher (`Katu\Tools\Events\Dispatcher`)
**Location:** `Dispatcher.php`

Main event dispatcher for managing events and listeners:

```php
// Key methods:
public function getListeners(): ListenerCollection
public function addListener(Listener $listener): Dispatcher
public function addListeners(iterable $listeners): Dispatcher
public function getEventListeners(Event $event): ListenerCollection
public function trigger(string $name, array $args = [])
public function triggerEvent(Event $event)
```

**Key Features:**
- Event dispatching
- Listener management
- Pattern-based event matching
- Collection-based listener storage

### 2.2. Event (`Katu\Tools\Events\Event`)
**Location:** `Event.php`

Event representation with name and arguments:

```php
// Key methods:
public function __construct(string $name, array $args = [])
public function setName(string $name): Event
public function getName(): string
public function setArgs(array $args = []): Event
public function getArgs(): array
public function getArg(string $key)
```

**Key Features:**
- Event name management
- Argument handling
- Event data storage
- Simple event structure

### 2.3. Listener (`Katu\Tools\Events\Listener`)
**Location:** `Listener.php`

Event listener with pattern matching and callback execution:

```php
// Key methods:
public function __construct(?string $pattern = null, ?callable $callable = null)
public function setPattern(?string $pattern): Listener
public function setPatterns(PatternCollection $patterns): Listener
public function getPatterns(): PatternCollection
public function setCallable(?callable $callable): Listener
public function getCallable(): ?callable
public function matchesEventName(string $eventName): bool
public function runWithEvent(Event $event): bool
```

**Key Features:**
- Pattern-based event matching
- Callback execution
- Error handling
- Flexible listener configuration

### 2.4. ListenerCollection (`Katu\Tools\Events\ListenerCollection`)
**Location:** `ListenerCollection.php`

Collection for managing multiple listeners:

```php
// Key methods:
public function filterForEventName(string $eventName)
```

**Key Features:**
- Listener collection management
- Event name filtering
- Array-based storage

---

## 3. Pattern System

### 3.1. Pattern (`Katu\Tools\Events\Pattern`)
**Location:** `Pattern.php`

Event name pattern matching:

```php
// Key methods:
public function __construct(?string $text)
public function setText(string $text): Pattern
public function getText(): string
public function getRegex(): string
public function matches(string $attempt): bool
```

**Key Features:**
- Pattern text management
- Regex conversion
- Pattern matching
- Wildcard support

### 3.2. PatternCollection (`Katu\Tools\Events\PatternCollection`)
**Location:** `PatternCollection.php`

Collection for managing multiple patterns:

```php
// Key methods:
public static function createFromString(string $string): PatternCollection
```

**Key Features:**
- Pattern collection management
- String-based pattern creation
- Multiple pattern support

---

## 4. Event Dispatching

### 4.1. Basic Event Dispatching
```php
// Create dispatcher
$dispatcher = new Dispatcher();

// Trigger simple event
$dispatcher->trigger("user.created", ["userId" => 123]);

// Trigger event with object
$event = new Event("user.updated", ["user" => $user, "changes" => $changes]);
$dispatcher->triggerEvent($event);
```

### 4.2. Event with Arguments
```php
// Create event with multiple arguments
$event = new Event("order.completed", [
    "orderId" => 456,
    "customerId" => 789,
    "total" => 99.99,
    "items" => $orderItems
]);

$dispatcher->triggerEvent($event);
```

### 4.3. Event Argument Access
```php
// In listener callback
$listener = new Listener("order.*", function(Event $event) {
    $orderId = $event->getArg("orderId");
    $customerId = $event->getArg("customerId");
    $total = $event->getArg("total");

    // Process event
    $this->processOrder($orderId, $customerId, $total);
});
```

---

## 5. Listener Management

### 5.1. Basic Listener Registration
```php
// Create listener
$listener = new Listener("user.created", function(Event $event) {
    $userId = $event->getArg("userId");
    $this->sendWelcomeEmail($userId);
});

// Add to dispatcher
$dispatcher->addListener($listener);
```

### 5.2. Multiple Listener Registration
```php
// Create multiple listeners
$listeners = [
    new Listener("user.*", function(Event $event) {
        $this->logUserActivity($event);
    }),
    new Listener("user.created", function(Event $event) {
        $this->sendWelcomeEmail($event->getArg("userId"));
    }),
    new Listener("user.updated", function(Event $event) {
        $this->updateUserCache($event->getArg("userId"));
    })
];

// Add all listeners
$dispatcher->addListeners($listeners);
```

### 5.3. Dynamic Listener Creation
```php
// Create listener with custom pattern
$listener = new Listener("order.*.completed", function(Event $event) {
    $this->processOrderCompletion($event);
});

$dispatcher->addListener($listener);
```

---

## 6. Pattern Matching

### 6.1. Basic Pattern Matching
```php
// Exact match
$listener = new Listener("user.created", $callback);

// Wildcard patterns
$listener = new Listener("user.*", $callback);        // Matches user.created, user.updated, etc.
$listener = new Listener("*.created", $callback);     // Matches user.created, order.created, etc.
$listener = new Listener("*.*", $callback);          // Matches any event with two parts
```

### 6.2. Advanced Pattern Matching
```php
// Multiple patterns for single listener
$patterns = PatternCollection::createFromString("user.* order.* payment.*");
$listener = new Listener();
$listener->setPatterns($patterns);
$listener->setCallable(function(Event $event) {
    $this->logActivity($event);
});
```

### 6.3. Pattern Testing
```php
// Test pattern matching
$pattern = new Pattern("user.*");
$matches = $pattern->matches("user.created");  // true
$matches = $pattern->matches("order.created"); // false

// Test listener matching
$listener = new Listener("user.*", $callback);
$matches = $listener->matchesEventName("user.created"); // true
```

---

## 7. Event System Integration

### 7.1. Model Integration
```php
class User extends \App\Models\Model
{
    public function persist(): Model
    {
        $isNew = !$this->id;
        $result = parent::persist();

        if ($isNew) {
            \App\App::getEventDispatcher()->trigger("user.created", [
                "userId" => $this->id,
                "user" => $this
            ]);
        } else {
            \App\App::getEventDispatcher()->trigger("user.updated", [
                "userId" => $this->id,
                "user" => $this,
                "changes" => $this->getChanges()
            ]);
        }

        return $result;
    }
}
```

### 7.2. Controller Integration
```php
class UserController extends \Katu\Controllers\Controller
{
    public function createUser(ServerRequestInterface $request): ResponseInterface
    {
        $user = new User();
        $user->name = $request->getParsedBody()["name"];
        $user->email = $request->getParsedBody()["email"];
        $user->persist();

        // Event is automatically triggered by model
        return $this->getViewResponse("user.created", [
            "user" => $user
        ]);
    }
}
```

### 7.3. Service Integration
```php
class EmailService
{
    public function __construct()
    {
        $this->registerEventListeners();
    }

    private function registerEventListeners()
    {
        $dispatcher = \App\App::getEventDispatcher();

        $dispatcher->addListener(new Listener("user.created", function(Event $event) {
            $this->sendWelcomeEmail($event->getArg("userId"));
        }));

        $dispatcher->addListener(new Listener("order.completed", function(Event $event) {
            $this->sendOrderConfirmation($event->getArg("orderId"));
        }));
    }
}
```

---

## 8. Error Handling

### 8.1. Listener Error Handling
```php
// Listener with error handling
$listener = new Listener("user.*", function(Event $event) {
    try {
        $this->processUserEvent($event);
    } catch (\Exception $e) {
        \App\App::getLogger()->error("Event listener failed", [
            "event" => $event->getName(),
            "error" => $e->getMessage()
        ]);
    }
});
```

### 8.2. Dispatcher Error Handling
```php
// Custom dispatcher with error handling
class SafeDispatcher extends Dispatcher
{
    public function triggerEvent(Event $event)
    {
        foreach ($this->getEventListeners($event) as $listener) {
            try {
                $listener->runWithEvent($event);
            } catch (\Throwable $e) {
                \App\App::getLogger()->error("Event listener error", [
                    "event" => $event->getName(),
                    "listener" => get_class($listener),
                    "error" => $e->getMessage()
                ]);
            }
        }
    }
}
```

---

## 9. Best Practices

### 9.1. Event Naming
- Use dot notation for hierarchical events (user.created, order.completed)
- Use descriptive names that indicate the action
- Follow consistent naming conventions
- Use past tense for completed actions

### 9.2. Listener Design
- Keep listeners focused on single responsibilities
- Implement proper error handling
- Use appropriate pattern matching
- Avoid long-running operations in listeners

### 9.3. Event Data
- Include relevant context in event arguments
- Use consistent argument naming
- Avoid passing large objects
- Include identifiers for related entities

### 9.4. Performance
- Use specific patterns when possible
- Avoid overly broad wildcards
- Implement listener prioritization if needed
- Monitor event system performance

---

## 10. Common Patterns

### 10.1. User Lifecycle Events
```php
// User registration flow
$dispatcher->addListener(new Listener("user.created", function(Event $event) {
    $userId = $event->getArg("userId");
    $this->sendWelcomeEmail($userId);
    $this->createUserProfile($userId);
    $this->logUserRegistration($userId);
}));

$dispatcher->addListener(new Listener("user.updated", function(Event $event) {
    $userId = $event->getArg("userId");
    $this->updateUserCache($userId);
    $this->logUserUpdate($userId);
}));
```

### 10.2. Order Processing Events
```php
// Order lifecycle
$dispatcher->addListener(new Listener("order.created", function(Event $event) {
    $this->reserveInventory($event->getArg("orderId"));
}));

$dispatcher->addListener(new Listener("order.completed", function(Event $event) {
    $this->sendOrderConfirmation($event->getArg("orderId"));
    $this->updateInventory($event->getArg("orderId"));
    $this->processPayment($event->getArg("orderId"));
}));
```

### 10.3. System Events
```php
// System monitoring
$dispatcher->addListener(new Listener("system.*", function(Event $event) {
    $this->logSystemEvent($event);
}));

$dispatcher->addListener(new Listener("error.*", function(Event $event) {
    $this->handleSystemError($event);
    $this->notifyAdministrators($event);
}));
```

---

## 11. Advanced Usage

### 11.1. Event Prioritization
```php
// Custom listener collection with prioritization
class PrioritizedListenerCollection extends ListenerCollection
{
    public function addListener(Listener $listener, int $priority = 0)
    {
        $listener->setPriority($priority);
        parent::addListener($listener);

        // Sort by priority
        $this->sortByPriority();
    }

    private function sortByPriority()
    {
        $listeners = $this->getArrayCopy();
        usort($listeners, function($a, $b) {
            return $b->getPriority() - $a->getPriority();
        });
        $this->exchangeArray($listeners);
    }
}
```

### 11.2. Event Middleware
```php
// Event middleware for logging and monitoring
class EventMiddleware
{
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function triggerEvent(Event $event)
    {
        $startTime = microtime(true);

        // Log event start
        \App\App::getLogger()->info("Event triggered", [
            "event" => $event->getName(),
            "args" => $event->getArgs()
        ]);

        // Dispatch event
        $this->dispatcher->triggerEvent($event);

        // Log event completion
        $duration = microtime(true) - $startTime;
        \App\App::getLogger()->info("Event completed", [
            "event" => $event->getName(),
            "duration" => $duration
        ]);
    }
}
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Listeners Not Firing:** Check pattern matching and listener registration
- **Event Not Found:** Verify event name and dispatcher configuration
- **Pattern Issues:** Test pattern matching with specific event names
- **Performance Issues:** Monitor listener execution time

### 12.2. Debugging
- Use logging to track event dispatching
- Test pattern matching with specific event names
- Monitor listener execution and errors
- Check event argument passing

### 12.3. Performance Optimization
- Use specific patterns instead of broad wildcards
- Implement listener prioritization
- Monitor event system performance
- Consider asynchronous event processing

---

This documentation provides comprehensive coverage of the KATU Event system. For specific implementation details, refer to the event classes in `src/Tools/Events/` and the integration with other framework components.
