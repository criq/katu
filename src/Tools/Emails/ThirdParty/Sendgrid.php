<?php

namespace Katu\Tools\Emails\ThirdParty;

class Sendgrid extends \Katu\Tools\Emails\ThirdParty
{
	public static function getDefaultAPI(): \SendGrid
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

	public function getEmail(): \SendGrid\Mail\Mail
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

		foreach ($this->getAttachments() as $attachment) {
			$email->addAttachment($attachment->getFile(), $attachment->getName(), $attachment->getCID());
		}

		return $email;
	}

	public function send()
	{
		$args = [];
		if (isset($args[0]) && $args[0] instanceof \SendGrid) {
			$sendgridApi = $args[0];
		} else {
			$sendgridApi = static::getDefaultAPI();
		}

		$email = $this->getEmail();

		return $sendgridApi->send($email);
	}
}
