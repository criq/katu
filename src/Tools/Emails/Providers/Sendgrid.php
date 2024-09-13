<?php

namespace Katu\Tools\Emails\Providers;

use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Provider;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Types\TEmailAddress;

class Sendgrid extends Provider
{
	protected $key;

	public function __construct(string $key)
	{
		$this->setKey($key);
	}

	public function setKey(string $key): Sendgrid
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
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

	public function dispatch(Request $request): Response
	{
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
