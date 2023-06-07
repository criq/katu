<?php

namespace Katu\Tools\Emails\ThirdParty;

class Sendgrid extends \Katu\Tools\Emails\ThirdParty
{
	public $attachments = [];

	public static function getDefaultApi()
	{
		try {
			$key = \Katu\Config\Config::get("app", "email", "useSendgridKey");
		} catch (\Throwable $e) {
			$key = "live";
		}

		try {
			return new \SendGrid(\Katu\Config\Config::get("sendgrid", "api", "keys", $key));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return new \SendGrid(\Katu\Config\Config::get("sendgrid", "api", "key"));
		}
	}

	public function getEmail()
	{
		$email = new \SendGrid\Mail\Mail;
		$email->setFrom($this->getSender()->getEmailAddress(), $this->getSender()->getName());
		$email->setSubject($this->subject);
		$email->addHeaders($this->headers);

		try {
			$email->addContent("text/html", $this->html);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$email->addContent("text/plain", $this->plain ?: strip_tags($this->html));
		} catch (\Throwable $e) {
			// Nevermind.
		}

		if ($this->template) {
			$email->setTemplateId($this->template);
		}

		foreach ($this->getRecipients() as $recipient) {
			$email->addTo($recipient->getEmailAddress(), $recipient->getName());
		}

		foreach ($this->attachments as $attachment) {
			$email->addAttachment($attachment["file"], $attachment["name"], $attachment["cid"]);
		}

		return $email;
	}

	public function send()
	{
		$args = [];
		if (isset($args[0]) && $args[0] instanceof \SendGrid) {
			$sendgridApi = $args[0];
		} else {
			$sendgridApi = static::getDefaultApi();
		}

		$email = $this->getEmail();

		return $sendgridApi->send($email);
	}
}
