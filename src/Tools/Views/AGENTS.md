# Views System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Views system in the KATU framework. The views system provides template rendering, Twig integration, and view engine management for web application templating.

---

## 1. System Overview

### 1.1. Purpose
- **Template Rendering:** Twig-based template rendering and management
- **View Engine Management:** Multiple view engine implementations
- **Template Integration:** Seamless integration with Twig templating
- **Global Data:** Automatic injection of common template data
- **Custom Functions:** Extended Twig functions and filters
- **Stream Integration:** PSR-7 stream interface support

### 1.2. Architecture
- **Core Classes:** `ViewEngineInterface`, `TwigEngine`, `ArrayLoaderTwigEngine`, `FilesystemLoaderTwigEngine`
- **Twig Integration:** Full Twig templating engine integration
- **Template Loaders:** Array and filesystem template loaders
- **Global Data:** Automatic template data injection
- **Custom Functions:** Extended Twig functionality
- **Stream Support:** PSR-7 stream interface integration

---

## 2. Core View Classes

### 2.1. ViewEngineInterface (`Katu\Tools\Views\ViewEngineInterface`)
**Location:** `ViewEngineInterface.php`

Interface for view engine implementations:

```php
// Key methods:
public function render(string $template, array $data = []): StreamInterface
```

**Key Features:**
- Standardized view engine interface
- Template rendering with data
- PSR-7 stream interface support

### 2.2. TwigEngine (`Katu\Tools\Views\TwigEngine`)
**Location:** `TwigEngine.php`

Abstract base class for Twig-based view engines:

```php
// Key methods:
abstract protected static function getTwigLoader(): \Twig\Loader\LoaderInterface
public function __construct(?ServerRequestInterface $request = null)
public function setRequest(?ServerRequestInterface $request): TwigEngine
public function getRequest(): ?ServerRequestInterface
protected function createTwig(): \Twig\Environment
public function getTwig(): \Twig\Environment
protected function getTwigConfig(): array
protected function getCommonData(): array
public function getTemplate(string $template, array $data = []): ?string
public function render(string $template, array $data = []): StreamInterface
```

**Key Features:**
- Abstract Twig engine base
- Request integration
- Global data injection
- Custom Twig functions and filters
- Template rendering
- Error handling

### 2.3. ArrayLoaderTwigEngine (`Katu\Tools\Views\ArrayLoaderTwigEngine`)
**Location:** `ArrayLoaderTwigEngine.php`

Twig engine with array-based template loading:

```php
// Key methods:
public function __construct(?ServerRequestInterface $request = null, array $templates = [])
protected static function getTwigLoader(): LoaderInterface
public function setTemplates(array $templates): ArrayLoaderTwigEngine
public function setTemplate(string $name, ?string $template): ArrayLoaderTwigEngine
public static function renderStringWithoutGlobals(?string $template, ?array $replacements = []): string
public static function renderString(?string $template, ?array $replacements = []): string
```

**Key Features:**
- Array-based template loading
- Dynamic template management
- String template rendering
- Template registration

### 2.4. FilesystemLoaderTwigEngine (`Katu\Tools\Views\FilesystemLoaderTwigEngine`)
**Location:** `FilesystemLoaderTwigEngine.php`

Twig engine with filesystem-based template loading:

```php
// Key methods:
protected static function getTwigLoader(): \Twig\Loader\LoaderInterface
protected static function getTwigDirs(): array
```

**Key Features:**
- Filesystem template loading
- Multiple template directories
- Automatic directory discovery
- Template file management

---

## 3. Template Rendering

### 3.1. Basic Template Rendering
```php
// Create view engine
$engine = new FilesystemLoaderTwigEngine($request);

// Render template
$stream = $engine->render("template.twig", [
    "title" => "Hello World",
    "content" => "This is the content"
]);

// Get template string
$html = $engine->getTemplate("template.twig", $data);
```

### 3.2. Array-based Template Rendering
```php
// Create array loader engine
$engine = new ArrayLoaderTwigEngine($request, [
    "template" => "Hello {{ name }}!"
]);

// Render from array
$html = $engine->getTemplate("template", ["name" => "World"]);

// Render string directly
$html = ArrayLoaderTwigEngine::renderString("Hello {{ name }}!", ["name" => "World"]);
```

### 3.3. Template with Global Data
```php
// Global data is automatically injected
$engine = new FilesystemLoaderTwigEngine($request);

// Template has access to global data
$stream = $engine->render("template.twig", [
    "user" => $user,
    "settings" => $settings
]);

// Global data includes:
// - _site (baseDir, baseUrl, apiUrl, timezone)
// - _request (uri, url, ip, params, route)
// - _user (current user)
// - _settings (application settings)
// - _session (session data)
// - _flash (flash messages)
// - _cookies (cookie data)
// - _agent (user agent info)
```

---

## 4. Custom Twig Functions

### 4.1. Image Functions
```twig
{# Get image object #}
{% set image = getImage('path/to/image.jpg') %}
<img src="{{ image.getURL() }}" alt="{{ image.getAlt() }}">

{# Get hashed file #}
{% set hashedFile = getHashedFile('css/style.css') %}
<link rel="stylesheet" href="{{ hashedFile.getURL() }}">
```

### 4.2. URL Functions
```twig
{# Generate URL for route #}
<a href="{{ url('user.show', {id: user.id}) }}">View User</a>

{# Generate decoded URL #}
<a href="{{ urlDecoded('user.show', {id: user.id}) }}">View User</a>

{# Get current URL #}
<p>Current URL: {{ getCurrentURL() }}</p>

{# Make URL from parts #}
{% set customUrl = makeUrl('https://example.com', {param: 'value'}) %}
```

### 4.3. Configuration Functions
```twig
{# Get configuration #}
{% set config = getConfig('AppConfig') %}
<p>Base URL: {{ config.getBaseURL() }}</p>

{# Get timeout #}
{% set timeout = getTimeout('1 hour') %}
<p>Timeout: {{ timeout.getReadable() }}</p>

{# Get version #}
<p>Version: {{ getVersion() }}</p>

{# Get base directory #}
<p>Base Dir: {{ getBaseDir() }}</p>
```

### 4.4. Session and Security Functions
```twig
{# Get session #}
{% set session = getSession() %}
<p>User ID: {{ session.get('user_id') }}</p>

{# Get CSRF token #}
<form method="post">
    <input type="hidden" name="csrf_token" value="{{ getCsrfToken() }}">
    <button type="submit">Submit</button>
</form>
```

### 4.5. File Functions
```twig
{# Get file object #}
{% set file = getFile('uploads', 'document.pdf') %}
<a href="{{ file.getURL() }}">Download</a>

{# Get hashed file #}
{% set hashedFile = getHashedFile('js/app.js') %}
<script src="{{ hashedFile.getURL() }}"></script>
```

### 4.6. Utility Functions
```twig
{# Generate Lorem Ipsum #}
<p>{{ lipsum(3) }}</p>

{# Get job instance #}
{% set job = getJob('App\\Jobs\\ProcessDataJob', {param: 'value'}) %}
<p>Job: {{ job.getTitle() }}</p>

{# Debug function #}
{{ dump(variable1, variable2) }}
```

---

## 5. Custom Twig Filters

### 5.1. Text Filters
```twig
{# Shorten text #}
<p>{{ longText | shorten(100, {append: '...'}) }}</p>

{# Shorten URL #}
<p>{{ longUrl | shortenUrl(50) }}</p>

{# Convert to array #}
{% set array = object | asArray %}

{# Remove duplicates #}
{% set unique = array | unique %}

{# Join in sentence #}
<p>{{ names | joinInSentence(', ', ' and ') }}</p>
```

### 5.2. Date and Time Filters
```twig
{# Validate date #}
{% if date | isValidDateTime %}
    <p>Valid date: {{ date }}</p>
{% endif %}

{# Markdown conversion #}
<div>{{ markdownContent | markdown }}</div>
```

### 5.3. Formatting Filters
```twig
{# Non-breaking spaces #}
<p>{{ text | nbsp }}</p>

{# Convert to string #}
<p>{{ value | str }}</p>

{# JSON decode #}
{% set data = jsonString | json_decode %}
```

---

## 6. Global Template Data

### 6.1. Site Data
```twig
{# Site information #}
<p>Base Directory: {{ _site.baseDir }}</p>
<p>Base URL: {{ _site.baseUrl }}</p>
<p>API URL: {{ _site.apiUrl }}</p>
<p>Timezone: {{ _site.timezone }}</p>
```

### 6.2. Request Data
```twig
{# Request information #}
<p>Current URI: {{ _request.uri }}</p>
<p>Current URL: {{ _request.url }}</p>
<p>Client IP: {{ _request.ip }}</p>
<p>Route: {{ _request.route.name }}</p>
<p>Route Pattern: {{ _request.route.pattern }}</p>
<p>Route Params: {{ _request.route.params | dump }}</p>
```

### 6.3. User and Session Data
```twig
{# User information #}
{% if _user %}
    <p>Welcome, {{ _user.name }}!</p>
{% endif %}

{# Session data #}
<p>Session ID: {{ _session.getId() }}</p>

{# Flash messages #}
{% for message in _flash.getMessages() %}
    <div class="alert">{{ message }}</div>
{% endfor %}

{# Settings #}
<p>Site Name: {{ _settings.site_name }}</p>
```

### 6.4. Platform and Upload Data
```twig
{# Platform information #}
<p>Platform: {{ _platform }}</p>

{# Upload limits #}
<p>Max Upload Size: {{ _upload.maxSize }}</p>

{# User agent #}
<p>Browser: {{ _agent.browser() }}</p>
<p>Platform: {{ _agent.platform() }}</p>
<p>Device: {{ _agent.device() }}</p>
```

---

## 7. Template Engine Configuration

### 7.1. Development Configuration
```php
// Automatic configuration based on environment
$config = [
    "auto_reload" => true,  // In development
    "debug" => true,       // In development
    "strict_variables" => false,
    "cache" => false       // In development
];
```

### 7.2. Production Configuration
```php
// Production configuration
$config = [
    "auto_reload" => false,
    "debug" => false,
    "strict_variables" => false,
    "cache" => "/path/to/cache"  // Enable caching
];
```

---

## 8. Template Directory Structure

### 8.1. Default Template Directories
```php
// FilesystemLoaderTwigEngine automatically searches:
$dirs = [
    "vendor/criq/katu/src/Views",           // Framework templates
    "vendor/",                              // Vendor templates
    "app/Views"                             // Application templates
];
```

### 8.2. Custom Template Directories
```php
// Extend FilesystemLoaderTwigEngine
class CustomTwigEngine extends FilesystemLoaderTwigEngine
{
    protected static function getTwigDirs(): array
    {
        return array_merge(parent::getTwigDirs(), [
            new \Katu\Files\File(\App\App::getBaseDir(), "custom/templates"),
            new \Katu\Files\File(\App\App::getBaseDir(), "themes/default")
        ]);
    }
}
```

---

## 9. Error Handling

### 9.1. Template Error Handling
```php
// Template rendering with error handling
try {
    $html = $engine->getTemplate("template.twig", $data);
} catch (\Twig\Error\Error $e) {
    \App\App::getLogger()->error("Template error", [
        "template" => "template.twig",
        "error" => $e->getMessage()
    ]);

    // Return fallback template
    $html = $engine->getTemplate("error.twig", ["error" => $e->getMessage()]);
}
```

### 9.2. Development Error Handling
```php
// In development mode, errors are re-thrown
if (\App\App::getAppConfig()->getIsEnvironment("DEVELOPMENT")) {
    // Template errors are displayed directly
    $html = $engine->getTemplate("template.twig", $data);
}
```

---

## 10. Best Practices

### 10.1. Template Organization
- Use consistent template naming conventions
- Organize templates in logical directories
- Use template inheritance and blocks
- Implement template caching in production

### 10.2. Data Management
- Use global data for common information
- Pass specific data for template context
- Avoid passing large objects to templates
- Use appropriate data types

### 10.3. Performance
- Enable template caching in production
- Use appropriate template loaders
- Minimize global data size
- Optimize template rendering

### 10.4. Security
- Sanitize user input in templates
- Use CSRF tokens for forms
- Validate template data
- Avoid exposing sensitive information

---

## 11. Common Patterns

### 11.1. Base Template
```twig
{# base.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}{{ _settings.site_name }}{% endblock %}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    {% block content %}{% endblock %}
</body>
</html>
```

### 11.2. Page Template
```twig
{# page.twig #}
{% extends "base.twig" %}

{% block title %}{{ page.title }} - {{ parent() }}{% endblock %}

{% block content %}
    <h1>{{ page.title }}</h1>
    <div class="content">
        {{ page.content | markdown }}
    </div>
{% endblock %}
```

### 11.3. Component Template
```twig
{# components/user-card.twig #}
<div class="user-card">
    <img src="{{ user.avatar | default('/images/default-avatar.png') }}" alt="{{ user.name }}">
    <h3>{{ user.name }}</h3>
    <p>{{ user.email }}</p>
    <a href="{{ url('user.show', {id: user.id}) }}">View Profile</a>
</div>
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Template Not Found:** Check template paths and loader configuration
- **Global Data Missing:** Verify request object and configuration
- **Function Not Found:** Check Twig function registration
- **Filter Not Found:** Check Twig filter registration

### 12.2. Debugging
- Use `dump()` function for debugging
- Check template paths and directories
- Verify global data injection
- Monitor template rendering performance

### 12.3. Performance Issues
- Enable template caching
- Optimize global data
- Use appropriate template loaders
- Monitor template rendering time

---

This documentation provides comprehensive coverage of the KATU Views system. For specific implementation details, refer to the view classes in `src/Tools/Views/` and the integration with the Twig templating engine.
