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
		$payload = new \SendGrid\Mail\Mail;
		$payload->addHeaders($request->getEmail()->getHeaders());
		$payload->setFrom($request->getEmail()->getSender()->getEmailAddress(), $request->getEmail()->getSender()->getName());
		$payload->setSubject($request->getEmail()->getSubject());
		$payload->addContent("text/html", $request->getEmail()->getResolvedHTML());
		$payload->addContent("text/plain", $request->getEmail()->getResolvedPlain());

		if ($request->getEmail()->getTemplate()) {
			$payload->setTemplateId($request->getEmail()->getTemplate());
		}

		array_walk($request->getEmail()->getRecipients()->getArrayCopy(), function (TEmailAddress $recipient) use (&$payload) {
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
		}, $request->getEmail()->getAttachments()->getArrayCopy()));

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
