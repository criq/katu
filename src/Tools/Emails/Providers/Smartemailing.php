<?php

namespace Katu\Tools\Emails\Providers;

use Katu\Tools\Emails\Attachment;
use Katu\Tools\Emails\Provider;
use Katu\Tools\Emails\Request;
use Katu\Tools\Emails\Response;
use Katu\Types\TEmailAddress;

class Smartemailing extends Provider
{
	protected $username;
	protected $key;

	public function __construct(string $username, string $key)
	{
		$this->setUsername($username);
		$this->setKey($key);
	}

	public function setUsername(string $username): Smartemailing
	{
		$this->username = $username;

		return $this;
	}

	public function getUsername(): string
	{
		return $this->username;
	}

	public function setKey(string $key): Smartemailing
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
		$payload["sender_credentials"]["from"] = $request->getEmail()->getSender()->getEmailAddress();
		$payload["sender_credentials"]["sender_name"] = $request->getEmail()->getSender()->getName();

		if ($request->getEmail()->getReplyTo()) {
			$payload["sender_credentials"]["reply_to"] = $request->getEmail()->getReplyTo()->getEmailAddress();
		} else {
			$payload["sender_credentials"]["reply_to"] = $request->getEmail()->getSender()->getEmailAddress();
		}

		$payload["tag"] = "";

		$configuration = $request->getEmail()->getProviderConfigurations()->getSmartemailingConfiguration();

		if ($request->getEmail()->getTemplate()) {
			$payload["email_id"] = $request->getEmail()->getTemplate();
		} elseif ($configuration->getTemplate()) {
			$payload["email_id"] = $configuration->getTemplate();
		} else {
			$payload["message_contents"]["subject"] = $request->getEmail()->getSubject();
			$payload["message_contents"]["html_body"] = $request->getEmail()->getResolvedHTML();
			$payload["message_contents"]["text_body"] = $request->getEmail()->getResolvedPlain();
		}

		$payload["tasks"] = array_map(function (TEmailAddress $recipient) use ($request) {
			return [
				"recipient" => [
					"emailaddress" => $recipient->getEmailAddress(),
				],
				"replace" => [],
				"template_variables" => array_merge(
					$request->getEmail()->getVariables()->getAssoc(),
					$request->getEmail()->getRecipientVariables()->filterByRecipient($recipient)->getVariables()->getAssoc(),
				),
				"attachments" => array_map(function (Attachment $attachment) {
					return [
						"file_name" => $attachment->getResolvedName(),
						"content_type" => $attachment->getContentType(),
						"data_base64" => $attachment->getEncodedContents(),
					];
				}, $request->getEmail()->getAttachments()->getArrayCopy()),
			];
		}, $request->getEmail()->getRecipients()->getArrayCopy());

		return $payload;
	}

	public function dispatch(Request $request): Response
	{
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
