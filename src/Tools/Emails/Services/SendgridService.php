<?php

namespace Katu\Tools\Emails\Services;

use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Tools\Emails\TransactionalEmailServiceInterface;
use Katu\Tools\Emails\Email;
use Katu\Types\TEmailAddress;

class SendgridService implements TransactionalEmailServiceInterface
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
		return "SENDGRID";
	}

	public function getTitle(): string
	{
		return "Sendgrid";
	}

	public function getDescription(): ?string
	{
		return "Sendgrid služba pro odesílání transakčních e-mailů";
	}

	public function setConfigFromSecret(string $secret): ?array
	{
		// Sendgrid uses plain API key, wrap it in apiKey
		$value = trim($secret);
		return mb_strlen($value) ? [
			"apiKey" => $value,
		] : null;
	}

	public function setConfig(?array $config): SendgridService
	{
		$this->config = $config;
		// Update key from config if available
		if (isset($config["apiKey"])) {
			$this->setKey($config["apiKey"]);
		}

		return $this;
	}

	public function getConfig(): ?array
	{
		return $this->config;
	}

	public function setKey(string $key): SendgridService
	{
		$this->key = $key;
		// Also update config for consistency
		$config = $this->getConfig() ?? [];
		$config["apiKey"] = $key;
		$this->setConfig($config);

		return $this;
	}

	public function getKey(): string
	{
		return $this->key ?? ($this->getConfig()["apiKey"] ?? "");
	}

	public function getPayload(Request $request): \SendGrid\Mail\Mail
	{
		$email = $request->getEmail();

		$payload = new \SendGrid\Mail\Mail;
		$payload->addHeaders($email->getHeaders());
		$payload->setFrom($email->getSender()->getEmailAddress(), $email->getSender()->getName());
		$payload->setSubject($email->getSubject());
		$payload->addContent("text/html", $email->getResolvedHTML());
		$payload->addContent("text/plain", $email->getResolvedPlain());

		if ($email->getTemplate()) {
			$payload->setTemplateId($email->getTemplate());
		}

		array_walk($email->getRecipients()->getArrayCopy(), function (TEmailAddress $recipient) use (&$payload) {
			$personalization = new \SendGrid\Mail\Personalization;
			$personalization->addTo(new \SendGrid\Mail\To($recipient->getEmailAddress(), $recipient->getName()));
			$payload->addPersonalization($personalization);
		});

		$payload->addAttachments(array_map(function (Attachment $attachment) {
			return new \SendGrid\Mail\Attachment(
				$attachment->getContents(),
				$attachment->getContentType(),
				$attachment->getResolvedName(),
				$attachment->getContentId(),
			);
		}, $email->getAttachments()->getArrayCopy()));

		return $payload;
	}

	public function dispatch(Email $email): Response
	{
		$request = new Request($this, $email);
		$response = new Response($request);

		try {
			$api = new \SendGrid($this->getKey());
			$payload = $this->getPayload($request);

			$apiResponse = $api->send($payload);

			$response->setPayload($apiResponse);
			$response->setStatus(in_array($apiResponse->statusCode(), [200, 201, 202]));
			$response->setMessageId(array_values(array_filter(array_map(function (string $header) {
				if (preg_match("/^X-Message-Id: (?<messageId>.+)$/", $header, $match)) {
					return $match["messageId"];
				}
			}, $apiResponse->headers())))[0] ?? null);
		} catch (\Throwable $e) {
			$response->setStatus(false);
			$response->setException($e);
		}

		return $response;
	}
}
