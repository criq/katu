<?php

namespace Katu\Tools\Emails\ThirdParty;

use Katu\Types\TIdentifier;

class Ecomail extends \Katu\Tools\Emails\ThirdParty
{
	public $attachments = [];

	public function getEmail(): array
	{
		$email = [];

		if ($this->getTemplate()) {
			$email["message"]["template_id"] = $this->getTemplate();
		}

		$email["message"]["subject"] = $this->getSubject();
		$email["message"]["html"] = $this->getHtml();
		$email["message"]["text"] = $this->getPlain() ?: strip_tags($this->html);
		$email["message"]["from_email"] = $this->getFromEmailAddress();
		$email["message"]["from_name"] = $this->getFromName();

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

		if ($this->getTemplate()) {
			$res = $curl->post("http://api2.ecomailapp.cz/transactional/send-template", $this->getEmail());
		} else {
			$res = $curl->post("http://api2.ecomailapp.cz/transactional/send-message", $this->getEmail());
		}

		$info = $curl->getInfo();

		if ($info["http_code"] != 200) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error(serialize($res));

			throw new \Exception("Došlo k chybě při odesílání e-mailu.");
		}

		return $res;
	}
}
