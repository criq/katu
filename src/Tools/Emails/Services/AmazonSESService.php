<?php

namespace Katu\Tools\Emails\Services;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Tools\Emails\TransactionalEmailServiceInterface;
use Katu\Tools\Emails\Email;
use Katu\Tools\Services\Service;
use Katu\Tools\Services\ServiceInterface;
use Katu\Types\TEmailAddress;
use Katu\Types\TIdentifier;

class AmazonSESService extends Service implements TransactionalEmailServiceInterface
{
	protected $sesClient;
	protected $configurationSetName;

	public function __construct(string $accessKeyId = "", string $secretAccessKey = "", string $region = "us-east-1", ?string $configurationSetName = null)
	{
		if (mb_strlen($accessKeyId) && mb_strlen($secretAccessKey)) {
			$this->sesClient = new \Aws\Ses\SesClient([
				"version" => "latest",
				"region"  => $region,
				"credentials" => [
					"key" => $accessKeyId,
					"secret" => $secretAccessKey,
				],
			]);

			$this->setConfigurationSetName($configurationSetName);
		}
	}

	public function getCode(): string
	{
		return "AMAZONSES";
	}

	public function getTitle(): string
	{
		return "Amazon SES";
	}

	public function getDescription(): ?string
	{
		return "Amazon Simple Email Service pro odesílání transakčních e-mailů";
	}

	public function setConfigFromSecret(string $secret): ?array
	{
		// AmazonSES expects JSON with: accessKeyId, secretAccessKey, region (optional), configurationSetName (optional)
		$value = trim($secret);
		if (!mb_strlen($value)) {
			return null;
		}

		// Try to parse as JSON first
		$decoded = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
			// Validate required fields
			if (isset($decoded["accessKeyId"]) && isset($decoded["secretAccessKey"])) {
				return [
					"accessKeyId" => $decoded["accessKeyId"],
					"secretAccessKey" => $decoded["secretAccessKey"],
					"region" => $decoded["region"] ?? "us-east-1",
					"configurationSetName" => $decoded["configurationSetName"] ?? null,
				];
			}
		}

		// If not JSON, assume it's a single access key (legacy format - not recommended)
		// Return null to indicate invalid format
		return null;
	}

	public function setConfig(?array $config): ServiceInterface
	{
		parent::setConfig($config);
		// Reinitialize SES client if config is available
		if ($config && isset($config["accessKeyId"]) && isset($config["secretAccessKey"])) {
			$this->sesClient = new \Aws\Ses\SesClient([
				"version" => "latest",
				"region"  => $config["region"] ?? "us-east-1",
				"credentials" => [
					"key" => $config["accessKeyId"],
					"secret" => $config["secretAccessKey"],
				],
			]);

			$this->setConfigurationSetName($config["configurationSetName"] ?? null);
		}

		return $this;
	}

	public function setConfigurationSetName(?string $configurationSetName): AmazonSESService
	{
		$this->configurationSetName = $configurationSetName;

		return $this;
	}

	public function getConfigurationSetName(): ?string
	{
		return $this->configurationSetName;
	}

	public function dispatch(Email $email): Response
	{
		$request = new Request($this, $email);
		$response = new Response($request);

		try {

			// Debug: Log the configuration set name value
			$configSetName = $this->getConfigurationSetName();
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES ConfigurationSetName value: " . var_export($configSetName, true));

			// Validate configuration set exists if specified
			$validConfigSetName = null;
			if ($configSetName && trim($configSetName) !== "") {
				try {
					// Try to describe the configuration set to see if it exists
					$this->sesClient->describeConfigurationSet([
						"ConfigurationSetName" => $configSetName
					]);
					$validConfigSetName = $configSetName;
					\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES ConfigurationSetName validated: " . $configSetName);
				} catch (\Aws\Exception\AwsException $e) {
					if ($e->getAwsErrorCode() === "ConfigurationSetDoesNotExist") {
						\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("warning", "AmazonSES ConfigurationSetName does not exist in AWS SES: " . $configSetName . " - proceeding without it");
					} else {
						// Re-throw other AWS errors
						throw $e;
					}
				}
			}

			// Debug: Log email details
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email sender: " . var_export($email->getSender(), true));
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email recipients count: " . $email->getRecipients()->count());
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email subject: " . var_export($email->getSubject(), true));
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email HTML length: " . strlen($email->getResolvedHTML() ?: ""));
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email Plain length: " . strlen($email->getResolvedPlain() ?: ""));
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES Email attachments count: " . $email->getAttachments()->count());

			// Validate required fields
			if (!$email->getSender() || !$email->getSender()->getEmailAddress()) {
				throw new \Exception("Sender email address is required");
			}

			if ($email->getRecipients()->count() === 0) {
				throw new \Exception("At least one recipient is required");
			}

			if (!$email->getSubject()) {
				throw new \Exception("Email subject is required");
			}

			if (!$email->getResolvedHTML() && !$email->getResolvedPlain()) {
				throw new \Exception("Email body (HTML or plain text) is required");
			}

			if ($email->getAttachments()->count() > 0) {
				// Use SendRawEmail for attachments
				$sendRawEmailParams = [
					"RawMessage" => [
						"Data" => $this->buildRawMessage($request)
					],
				];

				// Only add ConfigurationSetName if it has a non-empty value
				if ($validConfigSetName && trim($validConfigSetName) !== "") {
					$sendRawEmailParams["ConfigurationSetName"] = $validConfigSetName;
				}

				// Debug: Log the parameters being sent
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES SendRawEmail params: " . json_encode($sendRawEmailParams, JSON_PRETTY_PRINT));

				$result = $this->sesClient->sendRawEmail($sendRawEmailParams);
			} else {
				// Use SendEmail for simple emails - much cleaner with SDK!
				$sendEmailParams = [
					"Source" => $this->formatSender($email->getSender()),
					"Destination" => [
						"ToAddresses" => array_map(function (TEmailAddress $recipient) {
							return $recipient->getEmailAddress();
						}, $email->getRecipients()->getArrayCopy()),
					],
					"Message" => [
						"Subject" => [
							"Data" => $email->getSubject(),
							"Charset" => "UTF-8",
						],
						"Body" => [
							"Html" => [
								"Data" => $email->getResolvedHTML(),
								"Charset" => "UTF-8",
							],
							"Text" => [
								"Data" => $email->getResolvedPlain(),
								"Charset" => "UTF-8",
							],
						],
					],
				];

				// Only add ReplyToAddresses if there's actually a reply-to address
				if ($email->getReplyTo() && $email->getReplyTo()->getEmailAddress()) {
					$sendEmailParams["ReplyToAddresses"] = [$email->getReplyTo()->getEmailAddress()];
				}

				// Only add ConfigurationSetName if it has a non-empty value
				if ($validConfigSetName && trim($validConfigSetName) !== "") {
					$sendEmailParams["ConfigurationSetName"] = $validConfigSetName;
				}

				// Debug: Log the parameters being sent
				\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("info", "AmazonSES SendEmail params: " . json_encode($sendEmailParams, JSON_PRETTY_PRINT));

				$result = $this->sesClient->sendEmail($sendEmailParams);
			}

			$response->setStatus(true);
			$response->setPayload($result);
			$response->setMessageId($result["MessageId"]);

		} catch (\Aws\Exception\AwsException $e) {
			// Debug: Log detailed AWS error information
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES AWS Error: " . $e->getMessage());
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES AWS Error Code: " . $e->getAwsErrorCode());
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES AWS Error Type: " . $e->getAwsErrorType());
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES AWS Request ID: " . $e->getAwsRequestId());

			$response->setStatus(false);
			$response->setException($e);
			$response->getErrors()->addError(new Error("Chyba při odesílání e-mailu přes Amazon SES.", $e->getAwsErrorCode() ?: "AMAZON_SES_ERROR", ErrorVersionCollection::createFromArray([
				"cs" => $e->getMessage() ?: "Chyba při odesílání e-mailu přes Amazon SES.",
				"sk" => $e->getMessage() ?: "Chyba pri odosielaní e-mailu cez Amazon SES.",
				"en" => $e->getMessage() ?: "Error sending email via Amazon SES.",
			])));
		} catch (\Throwable $e) {
			// Debug: Log any other errors
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES General Error: " . $e->getMessage());
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->log("error", "AmazonSES Error Trace: " . $e->getTraceAsString());

			$response->setStatus(false);
			$response->setException($e);
			$response->getErrors()->addError(new Error("Chyba při odesílání e-mailu přes Amazon SES.", "AMAZON_SES_ERROR", ErrorVersionCollection::createFromArray([
				"cs" => $e->getMessage() ?: "Chyba při odesílání e-mailu přes Amazon SES.",
				"sk" => $e->getMessage() ?: "Chyba pri odosielaní e-mailu cez Amazon SES.",
				"en" => $e->getMessage() ?: "Error sending email via Amazon SES.",
			])));
		}

		return $response;
	}

	protected function formatSender(TEmailAddress $sender): string
	{
		return $sender->getName()
			? $sender->getName() . " <" . $sender->getEmailAddress() . ">"
			: $sender->getEmailAddress();
	}

	protected function buildRawMessage(Request $request): string
	{
		$email = $request->getEmail();
		$boundary = $this->generateBoundary();

		// Build headers
		$headers = [
			"From: " . $this->formatSender($email->getSender()),
			"To: " . $this->formatRecipients($email->getRecipients()->getArrayCopy()),
			"Subject: " . $email->getSubject(),
			"MIME-Version: 1.0",
			"Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"",
		];

		if ($email->getReplyTo()) {
			$headers[] = "Reply-To: " . $email->getReplyTo()->getEmailAddress();
		}

		$message = implode("\r\n", $headers) . "\r\n\r\n";

		// Add HTML part
		$message .= $this->buildMimePart($boundary, "text/html", "UTF-8", $email->getResolvedHTML());

		// Add text part
		$message .= $this->buildMimePart($boundary, "text/plain", "UTF-8", $email->getResolvedPlain());

		// Add attachments
		foreach ($email->getAttachments()->getArrayCopy() as $attachment) {
			$message .= $this->buildAttachmentPart($boundary, $attachment);
		}

		$message .= "--" . $boundary . "--\r\n";

		return base64_encode($message);
	}

	protected function formatRecipients(array $recipients): string
	{
		return implode(", ", array_map(function (TEmailAddress $recipient) {
			return $recipient->getName()
				? $recipient->getName() . " <" . $recipient->getEmailAddress() . ">"
				: $recipient->getEmailAddress();
		}, $recipients));
	}

	protected function buildMimePart(string $boundary, string $contentType, string $charset, string $content): string
	{
		return "--" . $boundary . "\r\n" .
			   "Content-Type: " . $contentType . "; charset=" . $charset . "\r\n" .
			   "Content-Transfer-Encoding: 7bit\r\n\r\n" .
			   $content . "\r\n\r\n";
	}

	protected function buildAttachmentPart(string $boundary, Attachment $attachment): string
	{
		return "--" . $boundary . "\r\n" .
			   "Content-Type: " . $attachment->getContentType() . "\r\n" .
			   "Content-Transfer-Encoding: base64\r\n" .
			   "Content-Disposition: attachment; filename=\"" . $attachment->getResolvedName() . "\"\r\n\r\n" .
			   $attachment->getEncodedContents() . "\r\n\r\n";
	}

	protected function generateBoundary(): string
	{
		return "boundary_" . uniqid() . "_" . time();
	}
}
