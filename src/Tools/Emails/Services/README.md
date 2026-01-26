# Email Services - Secret Configuration

Each email service implements `setConfigFromSecret()` to handle its own secret format. Secrets are stored as plain text in Google Secret Manager, and each service defines how to parse them into configuration arrays.

## Secret Format by Service

### Ecomail
**Format:** Plain text API key

**Example Secret Value:**
```
your-ecomail-api-key-here
```

**Parsed Config:**
```php
[
    "apiKey" => "your-ecomail-api-key-here"
]
```

### Sendgrid
**Format:** Plain text API key

**Example Secret Value:**
```
SG.your-sendgrid-api-key-here
```

**Parsed Config:**
```php
[
    "apiKey" => "SG.your-sendgrid-api-key-here"
]
```

### AmazonSES
**Format:** JSON object with required and optional fields

**Example Secret Value:**
```json
{
    "accessKeyId": "AKIAIOSFODNN7EXAMPLE",
    "secretAccessKey": "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
    "region": "us-east-1",
    "configurationSetName": "my-config-set"
}
```

**Parsed Config:**
```php
[
    "accessKeyId" => "AKIAIOSFODNN7EXAMPLE",
    "secretAccessKey" => "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",
    "region" => "us-east-1",
    "configurationSetName" => "my-config-set"
]
```

**Required Fields:**
- `accessKeyId`
- `secretAccessKey`

**Optional Fields:**
- `region` (defaults to "us-east-1")
- `configurationSetName` (defaults to null)

### Smartemailing
**Format:** JSON object OR colon-separated string

**Example Secret Value (JSON):**
```json
{
    "username": "your-username",
    "key": "your-api-key"
}
```

**Example Secret Value (Colon-separated):**
```
your-username:your-api-key
```

**Parsed Config:**
```php
[
    "username" => "your-username",
    "key" => "your-api-key"
]
```

**Required Fields:**
- `username`
- `key`

## Implementation Details

Each service:
1. Implements `setConfigFromSecret(string $secretValue): ?array` to parse the secret
2. Implements `setConfig(?array $config)` to apply configuration and initialize the service
3. Implements `getConfig(): ?array` to retrieve current configuration
4. Can be instantiated with empty constructor for use with `setConfig()`

## Usage Pattern

```php
// Get secret from Secret Manager
$secretValue = (new SecretManagerConfig)->getSecret("SECRET_NAME");

// Get service instance
$service = ServiceCollection::createDefault()->filterByCode("ECOMAIL")->getFirst();

// Parse secret into config
$config = $service->setConfigFromSecret($secretValue);

// Apply config to service
$service->setConfig($config);

// Service is now ready to use
$service->dispatch($request);
```

## Error Handling

- If secret value is empty or invalid, `setConfigFromSecret()` returns `null`
- Services validate required fields and return `null` if validation fails
- Invalid JSON in AmazonSES or Smartemailing secrets will return `null`
