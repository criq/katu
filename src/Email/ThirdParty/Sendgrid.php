<?php

namespace Katu\Email\ThirdParty;

class Sendgrid extends \Katu\Email\ThirdParty
{
	public $attachments = [];

	public static function getDefaultApi()
	{
		try {
			$key = \Katu\Config::get('app', 'email', 'useSendgridKey');
		} catch (\Throwable $e) {
			$key = 'live';
		}

		try {
			return new \SendGrid(\Katu\Config::get('sendgrid', 'api', 'keys', $key));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return new \SendGrid(\Katu\Config::get('sendgrid', 'api', 'key'));
		}
	}

	public function getEmail()
	{
		$email = new \SendGrid\Mail\Mail;
		$email->setFrom($this->fromEmailAddress, $this->fromName);
		$email->setSubject($this->subject);
		$email->addHeaders($this->headers);

		try {
			$email->addContent('text/html', $this->html);
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$email->addContent('text/plain', $this->plain ?: strip_tags($this->html));
		} catch (\Throwable $e) {
			// Nevermind.
		}

		if ($this->template) {
			$email->setTemplateId($this->template);
		}

		foreach ($this->to as $toEmailAddress => $toName) {
			$email->addTo($toEmailAddress, $toName, $this->substitutions[$toEmailAddress] ?? []);
		}

		foreach ($this->attachments as $attachment) {
			$email->addAttachment($attachment['file'], $attachment['name'], $attachment['cid']);
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