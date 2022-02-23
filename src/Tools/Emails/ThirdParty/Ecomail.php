<?php

namespace Katu\Tools\Emails\ThirdParty;

class Ecomail extends \Katu\Tools\Emails\ThirdParty
{
	public $attachments = [];

	public function getEmail(): array
	{
		$email = [];

		if ($this->template) {
			$email["message"]["template_id"] = $this->template;
		}

		$email["message"]["subject"] = $this->subject;
		$email["message"]["html"] = $this->html;
		$email["message"]["text"] = $this->plain;
		$email["message"]["from_email"] = $this->fromEmailAddress;
		$email["message"]["from_name"] = $this->fromName;

		foreach ($this->to as $toEmailAddress => $toName) {
			$email["message"]["to"][] = [
				"email" => $toEmailAddress,
				"name" => $toName,
			];
		}

		foreach ($this->variables as $name => $content) {
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

	public function send()
	{
		$curl = new \Curl\Curl;
		$curl->setHeader("key", \Katu\Config\Config::get("ecomail", "api", "key"));

		if ($this->template) {
			$res = $curl->post("http://api2.ecomailapp.cz/transactional/send-template", $this->getEmail());
		} else {
			$res = $curl->post("http://api2.ecomailapp.cz/transactional/send-message", $this->getEmail());
		}

		$info = $curl->getInfo();

		if ($info["http_code"] != 200) {
			throw new \Exception((string)$res);
		}

		return $res;
	}
}
