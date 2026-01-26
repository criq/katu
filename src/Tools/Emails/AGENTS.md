# Email System - AI Agent Documentation

> **Agent Protocol: Keep This Document Updated**
> All agents are required to update this document with any new, relevant information discovered during their work. This includes, but is not limited to, changes in architecture, new dependencies, updated build processes, or newly established coding conventions. A well-maintained document ensures efficiency and prevents repeated discovery work.

This document provides comprehensive technical documentation for the Email system in the KATU framework. The email system provides multi-service email sending capabilities with advanced features like attachments, templates, and recipient variables.

---

## 1. System Overview

### 1.1. Purpose
- **Multi-Service Support:** Amazon SES, SendGrid, Smartemailing, Ecomail
- **Email Composition:** HTML and plain text email creation
- **Template System:** Dynamic email templates with variables
- **Attachment Support:** File attachments with proper MIME handling
- **Recipient Management:** Multiple recipients with individual variables
- **Service Abstraction:** Unified interface across different email services

### 1.2. Architecture
- **Core Classes:** `Email`, `Service`, `Request`, `Response`
- **Service System:** Abstract service with concrete implementations
- **Attachment System:** File attachment handling with storage integration
- **Template Engine:** Variable substitution and template rendering
- **Configuration:** Service-specific configuration management

---

## 2. Core Email Classes

### 2.1. Email (`Katu\Tools\Emails\Email`)
**Location:** `Email.php`

The main email composition class:

```php
// Key methods:
public function setSubject(string $subject): Email
public function setSender(TEmailAddress $sender): Email
public function addRecipient(TEmailAddress $recipient): Email
public function setHTML(string $html): Email
public function setPlain(string $plain): Email
public function addAttachment(Attachment $attachment): Email
public function addVariable(Variable $variable): Email
public function addRecipientVariable(RecipientVariable $variable): Email
public function dispatch(): Response
```

**Key Features:**
- HTML and plain text content
- Multiple recipients (TO, CC, BCC)
- File attachments
- Template variables
- Per-recipient variables
- Custom headers
- Reply-to addresses

### 2.2. Service (`Katu\Tools\Emails\Service`)
**Location:** `Service.php`

Abstract base class for email services:

```php
// Key methods:
abstract public function dispatch(Request $request): Response
public function createRequest(Email $email): Request
```

**Key Features:**
- Service abstraction
- Request/response pattern
- Unified interface across services

### 2.3. Request (`Katu\Tools\Emails\Request`)
**Location:** `Request.php`

Email request container for service communication:

```php
// Key methods:
public function __construct(Service $service, Email $email)
public function getService(): Service
public function getEmail(): Email
```

### 2.4. Response (`Katu\Tools\Emails\Response`)
**Location:** `Response.php`

Email sending response with status and metadata:

```php
// Key methods:
public function __construct(bool $success, ?string $message = null, ?array $data = null)
public function isSuccess(): bool
public function getMessage(): ?string
public function getData(): ?array
```

---

## 3. Email Services

### 3.1. Amazon SES (`Services/AmazonSES.php`)
**Location:** `Services/AmazonSES.php`

Amazon Simple Email Service:

```php
// Constructor
$service = new AmazonSES($accessKeyId, $secretAccessKey, $region, $configurationSetName);

// Usage
$email = new Email();
$email->setSubject("Test Email")
      ->setSender(new TEmailAddress("sender@example.com"))
      ->addRecipient(new TEmailAddress("recipient@example.com"))
      ->setHTML("<h1>Hello World</h1>");

$response = $service->dispatch($service->createRequest($email));
```

**Key Features:**
- AWS SDK integration
- Configuration set support
- Region-specific endpoints
- Attachment support
- HTML and plain text content

### 3.2. SendGrid (`Services/Sendgrid.php`)
**Location:** `Services/Sendgrid.php`

SendGrid email service:

```php
$service = new Sendgrid($apiKey);
$response = $service->dispatch($service->createRequest($email));
```

**Key Features:**
- SendGrid API integration
- Template support
- Advanced analytics
- Delivery tracking

### 3.3. Smartemailing (`Services/Smartemailing.php`)
**Location:** `Services/Smartemailing.php`

Smartemailing service:

```php
$service = new Smartemailing($username, $apiKey);
$response = $service->dispatch($service->createRequest($email));
```

**Key Features:**
- Smartemailing API integration
- Marketing automation
- Contact management

### 3.4. Ecomail (`Services/Ecomail.php`)
**Location:** `Services/Ecomail.php`

Ecomail service:

```php
$service = new Ecomail($apiKey);
$response = $service->dispatch($service->createRequest($email));
```

**Key Features:**
- Ecomail API integration
- Email marketing features
- Contact segmentation

---

## 4. Email Components

### 4.1. Attachments (`Attachment.php`)
**Location:** `Attachment.php`

File attachment handling:

```php
// Key methods:
public function __construct(\Katu\Storage\Entity $entity, ?string $name = null, ?string $contentId = null)
public function setEntity(\Katu\Storage\Entity $entity): Attachment
public function setName(?string $name): Attachment
public function setContentId(?string $contentId): Attachment
public function getEntity(): \Katu\Storage\Entity
public function getName(): ?string
public function getContentId(): ?string
```

**Key Features:**
- Storage entity integration
- Custom file names
- Content-ID for inline attachments
- MIME type detection

### 4.2. Variables (`Variable.php`)
**Location:** `Variable.php`

Template variable system:

```php
$variable = new Variable("name", "John Doe");
$email->addVariable($variable);
```

### 4.3. Recipient Variables (`RecipientVariable.php`)
**Location:** `RecipientVariable.php`

Per-recipient variable system:

```php
$recipientVar = new RecipientVariable($recipient, "name", "Jane Doe");
$email->addRecipientVariable($recipientVar);
```

---

## 5. Usage Patterns

### 5.1. Basic Email Sending
```php
use Katu\Tools\Emails\Email;
use Katu\Tools\Emails\Services\AmazonSES;
use Katu\Types\TEmailAddress;

// Create service
$service = new AmazonSES($accessKeyId, $secretAccessKey, $region);

// Create email
$email = new Email();
$email->setSubject("Welcome to Our Service")
      ->setSender(new TEmailAddress("noreply@example.com", "Our Service"))
      ->addRecipient(new TEmailAddress("user@example.com"))
      ->setHTML("<h1>Welcome!</h1><p>Thank you for joining us.</p>")
      ->setPlain("Welcome! Thank you for joining us.");

// Send email
$response = $service->dispatch($service->createRequest($email));

if ($response->isSuccess()) {
    echo "Email sent successfully!";
} else {
    echo "Error: " . $response->getMessage();
}
```

### 5.2. Email with Attachments
```php
use Katu\Tools\Emails\Attachment;
use Katu\Storage\Entity;

// Create attachment
$fileEntity = new Entity("path/to/file.pdf");
$attachment = new Attachment($fileEntity, "document.pdf");
$email->addAttachment($attachment);

// Send with attachment
$response = $service->dispatch($service->createRequest($email));
```

### 5.3. Template Variables
```php
use Katu\Tools\Emails\Variable;

// Add template variables
$email->addVariable(new Variable("userName", "John Doe"));
$email->addVariable(new Variable("companyName", "Acme Corp"));

// Set HTML with template
$email->setHTML("
    <h1>Hello {{userName}}!</h1>
    <p>Welcome to {{companyName}}.</p>
");
```

### 5.4. Multiple Recipients
```php
// Add multiple recipients
$email->addRecipient(new TEmailAddress("user1@example.com"));
$email->addRecipient(new TEmailAddress("user2@example.com"));

// Add CC recipients
$email->addCC(new TEmailAddress("manager@example.com"));

// Add BCC recipients
$email->addBCC(new TEmailAddress("admin@example.com"));
```

### 5.5. Per-Recipient Variables
```php
use Katu\Tools\Emails\RecipientVariable;

$recipient1 = new TEmailAddress("user1@example.com");
$recipient2 = new TEmailAddress("user2@example.com");

// Add per-recipient variables
$email->addRecipientVariable(new RecipientVariable($recipient1, "name", "John"));
$email->addRecipientVariable(new RecipientVariable($recipient2, "name", "Jane"));

$email->setHTML("<h1>Hello {{name}}!</h1>");
```

### 5.6. Custom Headers
```php
use Katu\Tools\HTTPTools\Header;

// Add custom headers
$email->addHeader(new Header("X-Custom-Header", "Custom Value"));
$email->addHeader(new Header("X-Priority", "1"));
```

---

## 6. Configuration Management

### 6.1. Provider Configuration
```php
use Katu\Tools\Emails\ProviderConfiguration;

// Create configuration
$config = new ProviderConfiguration("amazon-ses");
$config->setAccessKeyId($accessKeyId);
$config->setSecretAccessKey($secretAccessKey);
$config->setRegion($region);

// Use configuration
$service = new AmazonSES($config);
```

### 6.2. Configuration Collection
```php
use Katu\Tools\Emails\ServiceConfigurationCollection;

$configs = new ServiceConfigurationCollection();
$configs[] = $amazonSESConfig;
$configs[] = $sendGridConfig;
```

---

## 7. Advanced Features

### 7.1. Template System
```php
// Load template from file
$template = file_get_contents("templates/welcome.html");

// Set template with variables
$email->setTemplate($template);
$email->addVariable(new Variable("userName", $user->name));
$email->addVariable(new Variable("activationLink", $activationUrl));
```

### 7.2. Reply-To Management
```php
$email->setReplyTo(new TEmailAddress("support@example.com", "Support Team"));
```

### 7.3. Dispatchable Interface
```php
// Check if email is ready to send
if ($email->isDispatchable()) {
    $response = $service->dispatch($service->createRequest($email));
}
```

---

## 8. Error Handling

### 8.1. Response Handling
```php
$response = $service->dispatch($service->createRequest($email));

if (!$response->isSuccess()) {
    // Handle error
    $errorMessage = $response->getMessage();
    $errorData = $response->getData();

    // Log error or show to user
    error_log("Email sending failed: " . $errorMessage);
}
```

### 8.2. Service-Specific Errors
```php
try {
    $response = $service->dispatch($service->createRequest($email));
} catch (Exception $e) {
    // Handle provider-specific exceptions
    error_log("Service error: " . $e->getMessage());
}
```

---

## 9. Best Practices

### 9.1. Email Content
- Always provide both HTML and plain text versions
- Use semantic HTML for better email client compatibility
- Test emails across different clients
- Keep subject lines concise and descriptive

### 9.2. Service Selection
- Choose service based on volume and requirements
- Use Amazon SES for high-volume transactional emails
- Use SendGrid for marketing emails with analytics
- Consider provider-specific features and limitations

### 9.3. Performance
- Batch similar emails when possible
- Use appropriate provider for email type
- Monitor delivery rates and adjust accordingly
- Implement retry logic for failed sends

### 9.4. Security
- Validate all email addresses
- Sanitize template variables
- Use secure storage for attachments
- Implement rate limiting for email sending

---

## 10. Integration Examples

### 10.1. Controller Integration
```php
class NotificationController extends Controller
{
    public function sendWelcomeEmail(User $user): ResponseInterface
    {
        $service = new AmazonSES($this->getSESConfig());

        $email = new Email();
        $email->setSubject("Welcome to Our Service")
              ->setSender(new TEmailAddress("noreply@example.com"))
              ->addRecipient(new TEmailAddress($user->email))
              ->setHTML($this->renderWelcomeTemplate($user));

        $response = $service->dispatch($service->createRequest($email));

        if ($response->isSuccess()) {
            return $this->successResponse("Email sent successfully");
        } else {
            return $this->errorResponse("Failed to send email");
        }
    }
}
```

### 10.2. Job Integration
```php
class SendEmailJob extends Job
{
    public function getCallback(): callable
    {
        return function() {
            $emails = $this->getPendingEmails();

            foreach ($emails as $emailData) {
                $email = $this->createEmailFromData($emailData);
                $response = $this->provider->dispatch($this->provider->createRequest($email));

                if ($response->isSuccess()) {
                    $this->markEmailAsSent($emailData['id']);
                }
            }
        };
    }
}
```

---

## 11. Common Patterns

### 11.1. Email Sending Pattern
```php
// Complete email sending with template
$email = new Email();
$email->setTo("user@example.com")
      ->setFrom("noreply@example.com")
      ->setSubject("Welcome to our service")
      ->setTemplate("welcome.twig", [
          "name" => "John Doe",
          "activation_url" => "https://example.com/activate/123"
      ]);

$service = new Sendgrid($apiKey);
$service->dispatch($service->createRequest($email));
```

### 11.2. Bulk Email Pattern
```php
// Bulk email sending with rate limiting
$users = User::getBy(["newsletter" => true]);
$service = new Sendgrid($apiKey);

foreach ($users as $user) {
    $email = new Email();
    $email->setTo($user->email)
          ->setTemplate("newsletter.twig", ["user" => $user]);

    $service->dispatch($service->createRequest($email));

    // Rate limiting
    usleep(100000); // 100ms delay
}
```

### 11.3. Email with Attachments Pattern
```php
// Email with file attachments
$email = new Email();
$email->setTo("client@example.com")
      ->setSubject("Invoice #12345")
      ->setTemplate("invoice.twig", ["invoice" => $invoice]);

// Add attachment
$file = new File("invoices/invoice_12345.pdf");
$attachment = new Attachment($file);
$email->addAttachment($attachment);

$service->dispatch($service->createRequest($email));
```

---

## 12. Troubleshooting

### 12.1. Common Issues
- **Authentication Errors:** Check service credentials and permissions
- **Attachment Issues:** Verify file paths and storage entity setup
- **Template Variables:** Ensure variable names match template placeholders
- **Provider Limits:** Check sending quotas and rate limits

### 12.2. Debugging
- Enable provider-specific logging
- Check response messages for detailed error information
- Verify email content and recipient addresses
- Test with simple emails before complex templates

---

This documentation provides comprehensive coverage of the Email system. For specific implementation details, refer to the source code in `src/Tools/Emails/`.
