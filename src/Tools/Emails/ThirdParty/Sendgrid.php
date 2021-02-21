<?php

namespace Katu\Tools\Emails\ThirdParty;

class Sendgrid extends \Katu\Tools\Emails\ThirdParty
{
	public $attachments = [];

	public static function getDefaultApi()
	{
		try {
			$key = \Katu\Config\Config::get('app', 'email', 'useSendgridKey');
		} catch (\Throwable $e) {
			$key = 'live';
		}

		try {
			return new \SendGrid(\Katu\Config\Config::get('sendgrid', 'api', 'keys', $key));
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return new \SendGrid(\Katu\Config\Config::get('sendgrid', 'api', 'key'));
		}
	}

	public function getEmail()
	{
		$email = new \SendGrid\Email;
		$email->setFrom($this->fromEmailAddress, $this->fromName);
		$email->setSubject($this->subject);
		$email->setHtml($this->html);
		$email->setText($this->plain ?: strip_tags($this->html));
		$email->setHeaders($this->headers);

		foreach ($this->to as $toEmailAddress => $toName) {
			$email->addTo($toEmailAddress, $toName);
		}

		if ($this->template) {
			$email->setTemplateId($this->template);
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
