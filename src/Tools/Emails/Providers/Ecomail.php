<?php

namespace Katu\Tools\Emails\Providers;

use Katu\Errors\Error;
use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Provider;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Tools\Emails\Variable;
use Katu\Types\TEmailAddress;
use Katu\Types\TURL;

class Ecomail extends Provider
{
	protected $key;

	public function __construct(string $key)
	{
		$this->setKey($key);
	}

	public function setKey(string $key): Ecomail
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

	public function dispatch(Request $request): Response
	{
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
					$response->getErrors()->addError(new Error($error[0], $key));
				}

				// Insert contents of <title>.
				if (!$response->getErrors()->hasErrors()) {
					try {
						$title = trim(\Katu\Tools\DOM\DOM::crawlHTML($apiResponse)->filter("title")->text());
						if ($title) {
							$response->getErrors()->addError(new Error($title));
						}
					} catch (\Throwable $e) {
						// Nevermind.
					}
				}

				// Insert whole response.
				if (!$response->getErrors()->hasErrors()) {
					$response->getErrors()->addError(new Error((string)$apiResponse));
				}
			}
		} catch (\Throwable $e) {
			$response->setStatus(false);
			$response->setException($e);
		}

		return $response;
	}
}
