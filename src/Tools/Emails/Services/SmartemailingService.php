<?php

namespace Katu\Tools\Emails\Services;

use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Tools\Emails\TransactionalEmailServiceInterface;
use Katu\Tools\Emails\Email;
use Katu\Types\TEmailAddress;

class SmartemailingService implements TransactionalEmailServiceInterface
{
	protected $username;
	protected $key;
	protected $config;

	public function __construct(string $username = "", string $key = "")
	{
		if (mb_strlen($username) && mb_strlen($key)) {
			$this->setUsername($username);
			$this->setKey($key);
		}
	}

	public function getCode(): string
	{
		return "SMARTEMAILING";
	}

	public function getTitle(): string
	{
		return "Smartemailing";
	}

	public function getDescription(): ?string
	{
		return "Smartemailing služba pro odesílání transakčních e-mailů";
	}

	public function setConfigFromSecret(string $secret): ?array
	{
		// Smartemailing expects JSON with: username, key
		$value = trim($secret);
		if (!mb_strlen($value)) {
			return null;
		}

		// Try to parse as JSON first
		$decoded = json_decode($value, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
			// Validate required fields
			if (isset($decoded["username"]) && isset($decoded["key"])) {
				return [
					"username" => $decoded["username"],
					"key" => $decoded["key"],
				];
			}
		}

		// If not JSON, try colon-separated format: "username:key"
		if (strpos($value, ":") !== false) {
			$parts = explode(":", $value, 2);
			if (count($parts) === 2 && mb_strlen(trim($parts[0])) && mb_strlen(trim($parts[1]))) {
				return [
					"username" => trim($parts[0]),
					"key" => trim($parts[1]),
				];
			}
		}

		// Invalid format
		return null;
	}

	public function setConfig(?array $config): SmartemailingService
	{
		$this->config = $config;
		// Update username and key from config if available
		if ($config) {
			if (isset($config["username"])) {
				$this->setUsername($config["username"]);
			}
			if (isset($config["key"])) {
				$this->setKey($config["key"]);
			}
		}

		return $this;
	}

	public function getConfig(): ?array
	{
		return $this->config;
	}

	public function setUsername(string $username): SmartemailingService
	{
		$this->username = $username;

		return $this;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function setKey(string $key): SmartemailingService
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function getPayload(Request $request): array
	{
		$email = $request->getEmail();

		$payload["sender_credentials"]["from"] = $email->getSender()->getEmailAddress();
		$payload["sender_credentials"]["sender_name"] = $email->getSender()->getName();

		if ($email->getReplyTo()) {
			$payload["sender_credentials"]["reply_to"] = $email->getReplyTo()->getEmailAddress();
		} else {
			$payload["sender_credentials"]["reply_to"] = $email->getSender()->getEmailAddress();
		}

		$payload["tag"] = "";

		if ($email->getTemplate()) {
			$payload["email_id"] = $email->getTemplate();
		} else {
			$payload["message_contents"]["subject"] = $email->getSubject();
			$payload["message_contents"]["html_body"] = $email->getResolvedHTML();
			$payload["message_contents"]["text_body"] = $email->getResolvedPlain();
		}

		$payload["tasks"] = array_map(function (TEmailAddress $recipient) use ($request) {
			$email = $request->getEmail();

			return [
				"recipient" => [
					"emailaddress" => $recipient->getEmailAddress(),
				],
				"replace" => [],
				"template_variables" => array_merge(
					$email->getVariables()->getAssoc(),
					$email->getRecipientVariables()->filterByRecipient($recipient)->getVariables()->getAssoc(),
				),
				"attachments" => array_map(function (Attachment $attachment) {
					return [
						"file_name" => $attachment->getResolvedName(),
						"content_type" => $attachment->getContentType(),
						"data_base64" => $attachment->getEncodedContents(),
					];
				}, $email->getAttachments()->getArrayCopy()),
			];
		}, $email->getRecipients()->getArrayCopy());

		return $payload;
	}

	public function dispatch(Email $email): Response
	{
		$request = new Request($this, $email);
		$response = new Response($request);

		try {
			$curl = new \Curl\Curl;
			$curl->setBasicAuthentication($this->getUsername(), $this->getKey());
			$curl->setHeader("Content-Type", "application/json");
			$curl->setHeader("Accept", "application/json");

			$url = "https://app.smartemailing.cz/api/v3/send/transactional-emails-bulk";
			$payload = $this->getPayload($request);

			$apiResponse = $curl->post($url, $payload);
			$curlInfo = $curl->getInfo();

			$response->setStatus(in_array($curlInfo["http_code"], [200, 201, 202]));
			$response->setPayload($apiResponse);
			$response->setMessageId($apiResponse->data[0]->id ?? null);
		} catch (\Throwable $e) {
			$response->setStatus(false);
			$response->setException($e);
		}

		return $response;
	}
}
