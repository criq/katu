<?php

namespace Katu\Tools\Emails\Services;

use Katu\Errors\Error;
use Katu\Errors\ErrorVersionCollection;
use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Tools\Emails\Variable;
use Katu\Tools\Emails\TransactionalEmailServiceInterface;
use Katu\Tools\Emails\Email;
use Katu\Types\TEmailAddress;
use Katu\Types\TURL;

class EcomailService implements TransactionalEmailServiceInterface
{
	protected $key;
	protected $config;

	public function __construct(string $key = "")
	{
		if (mb_strlen($key)) {
			$this->setKey($key);
		}
	}

	public function getCode(): string
	{
		return "ECOMAIL";
	}

	public function getTitle(): string
	{
		return "Ecomail";
	}

	public function getDescription(): ?string
	{
		return "Ecomail služba pro odesílání transakčních e-mailů";
	}

	public function getProviderClass(): string
	{
		return self::class;
	}

	public function setConfigFromSecret(string $secret): ?array
	{
		$value = trim($secret);
		if (!mb_strlen($value)) {
			return null;
		}

		// Try to parse as JSON first
		$decoded = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
			// If it's valid JSON with apiKey, use it
			if (isset($decoded["apiKey"])) {
				return [
					"apiKey" => $decoded["apiKey"],
				];
			}
		}

		// Otherwise, treat as plain API key
		return [
			"apiKey" => $value,
		];
	}

	public function setConfig(?array $config): EcomailService
	{
		$this->config = $config;

		return $this;
	}

	public function getConfig(): ?array
	{
		return $this->config;
	}

	public function setKey(string $key): TransactionalEmailServiceInterface
	{
		$this->key = $key;
		// Also update config for consistency
		$config = $this->getConfig() ?? [];
		$config["apiKey"] = $key;
		$this->setConfig($config);

		return $this;
	}

	public function getKey(): ?string
	{
		// Return key from property first, then from config
		if ($this->key !== null) {
			return $this->key;
		}
		return $this->getConfig()["apiKey"] ?? null;
	}

	public function getPayload(Request $request): array
	{
		$email = $request->getEmail();

		$payload = [];

		if ($email->getTemplate()) {
			$payload["message"]["template_id"] = $email->getTemplate();
		}

		$payload["message"]["from_email"] = $email->getSender()->getEmailAddress();
		$payload["message"]["from_name"] = $email->getSender()->getName();

		if ($email->getReplyTo()) {
			$payload["message"]["reply_to"] = $email->getReplyTo()->getEmailAddress();
		}

		$payload["message"]["to"] = array_map(function (TEmailAddress $recipient) {
			return [
				"email" => $recipient->getEmailAddress(),
				"name" => $recipient->getName(),
			];
		}, $email->getRecipients()->getArrayCopy());

		$payload["message"]["subject"] = $email->getSubject();
		$payload["message"]["html"] = $email->getResolvedHTML();
		$payload["message"]["text"] = $email->getResolvedPlain();

		$payload["message"]["global_merge_vars"] = array_map(function (Variable $variable) {
			return [
				"name" => $variable->getKey(),
				"content" => $variable->getValue(),
			];
		}, $email->getVariables()->getArrayCopy());

		$payload["message"]["attachments"] = array_map(function (Attachment $attachment) {
			return [
				"type" => $attachment->getContentType(),
				"name" => $attachment->getResolvedName(),
				"content" => $attachment->getEncodedContents(),
			];
		}, $email->getAttachments()->getArrayCopy());

		return $payload;
	}

	public function getAPIEndpointURL(Request $request): TURL
	{
		return $request->getEmail()->getTemplate()
			? new TURL("http://api2.ecomailapp.cz/transactional/send-template")
			: new TURL("http://api2.ecomailapp.cz/transactional/send-message")
			;
	}

	public function dispatch(Email $email): Response
	{
		$request = new Request($this, $email);
		$response = new Response($request);

		try {
			$curl = new \Curl\Curl;
			$curl->setHeader("key", $this->getKey());
			$curl->setHeader("Content-Type", "application/json");

			$url = $this->getAPIEndpointURL($request);
			$payload = $this->getPayload($request);

			$apiResponse = $curl->post($url, $payload);
			$curlInfo = $curl->getInfo();

			if ($curlInfo["http_code"] == 200) {
				$response->setStatus(true);
				$response->setPayload($apiResponse);
				$response->setMessageId($apiResponse->results->id ?? null);
			} else {
				foreach (($apiResponse->errors ?? []) as $key => $error) {
					$response->getErrors()->addError(new Error("Chyba při odesílání e-mailu přes Ecomail.", $key ?: "ECOMAIL_ERROR", ErrorVersionCollection::createFromArray([
						"cs" => $error[0] ?: "Chyba při odesílání e-mailu přes Ecomail.",
						"sk" => $error[0] ?: "Chyba pri odosielaní e-mailu cez Ecomail.",
						"en" => $error[0] ?: "Error sending email via Ecomail.",
					])));
				}

				// Insert contents of <title>.
				if (!$response->getErrors()->hasErrors()) {
					try {
						$title = trim(\Katu\Tools\DOM\DOM::crawlHTML($apiResponse)->filter("title")->text());
						if ($title) {
							$response->getErrors()->addError(new Error("Chyba při odesílání e-mailu přes Ecomail.", "ECOMAIL_ERROR", ErrorVersionCollection::createFromArray([
								"cs" => $title ?: "Chyba při odesílání e-mailu přes Ecomail.",
								"sk" => $title ?: "Chyba pri odosielaní e-mailu cez Ecomail.",
								"en" => $title ?: "Error sending email via Ecomail.",
							])));
						}
					} catch (\Throwable $e) {
						// Nevermind.
					}
				}

				// Insert whole response.
				if (!$response->getErrors()->hasErrors()) {
					$response->getErrors()->addError(new Error("Chyba při odesílání e-mailu přes Ecomail.", "ECOMAIL_ERROR", ErrorVersionCollection::createFromArray([
						"cs" => (string)$apiResponse ?: "Chyba při odesílání e-mailu přes Ecomail.",
						"sk" => (string)$apiResponse ?: "Chyba pri odosielaní e-mailu cez Ecomail.",
						"en" => (string)$apiResponse ?: "Error sending email via Ecomail.",
					])));
				}
			}
		} catch (\Throwable $e) {
			$response->setStatus(false);
			$response->setException($e);
		}

		return $response;
	}
}
