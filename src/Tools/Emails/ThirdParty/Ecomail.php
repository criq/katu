<?php

namespace Katu\Tools\Emails\ThirdParty;

use Katu\Errors\Error;
use Katu\Errors\ErrorCollection;
use Katu\Tools\Emails\Response;
use Katu\Types\TURL;

class Ecomail extends \Katu\Tools\Emails\ThirdParty
{
	public function getEmail(): array
	{
		$email = [];

		if ($this->getTemplate()) {
			$email["message"]["template_id"] = $this->getTemplate();
		}

		$email["message"]["subject"] = $this->getSubject();
		$email["message"]["html"] = $this->getHtml();
		$email["message"]["text"] = $this->getPlain() ?: strip_tags($this->html);
		$email["message"]["from_email"] = $this->getSender()->getEmailAddress();
		$email["message"]["from_name"] = $this->getSender()->getName();

		if ($this->getReplyTo()) {
			$email["message"]["reply_to"] = $this->getReplyTo()->getEmailAddress();
		}

		foreach ($this->getRecipients() as $recipient) {
			$email["message"]["to"][] = [
				"email" => $recipient->getEmailAddress(),
				"name" => $recipient->getName(),
			];
		}

		foreach ($this->globalVariables as $name => $content) {
			$email["message"]["global_merge_vars"][] = [
				"name" => $name,
				"content" => $content,
			];
		}

		foreach ($this->attachments as $attachment) {
			$email["message"]["attachments"][] = [
				"type" => $attachment["file"]->getMime(),
				"name" => isset($attachment["name"]) ? $attachment["name"] : $attachment["file"]->getBasename(),
				"content" => \base64_encode($attachment["file"]->get()),
			];
		}

		return $email;
	}

	public function getEndpointURL(): TURL
	{
		return $this->getTemplate()
			? new TURL("http://api2.ecomailapp.cz/transactional/send-template")
			: new TURL("http://api2.ecomailapp.cz/transactional/send-message")
			;
	}

	public function send(): Response
	{
		$curl = new \Curl\Curl;
		$curl->setHeader("key", \Katu\Config\Config::get("ecomail", "api", "key"));

		$res = $curl->post($this->getEndpointURL(), $this->getEmail());
		$info = $curl->getInfo();

		if ($info["http_code"] == 200) {
			return (new Response(true))->setPayload($res);
		} else {
			$errors = new ErrorCollection;
			foreach ((array)$res->errors as $key => $error) {
				$errors[] = new Error($error[0], $key);
			}

			return (new Response(false))->setPayload($res)->setErrors($errors);
		}
	}
}
