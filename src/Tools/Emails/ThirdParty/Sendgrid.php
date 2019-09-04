<?php

namespace Katu\Tools\Emails\ThirdParty;

class Sendgrid extends \Katu\Tools\Emails\ThirdParty {

	public $attachments = [];

	static function getDefaultApi() {
		$app = \Katu\App::get();

		try {
			$key = \Katu\Config\Config::get('app', 'email', 'useSendgridKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \Sendgrid(\Katu\Config\Config::get('sendgrid', 'api', 'keys', $key));
	}

	public function getEmail($message = []) {
		$from = new \SendGrid\Mail\From($this->fromEmailAddress, $this->fromName);
		$email = new \SendGrid\Mail\Mail($from);

		$email->setSubject($this->subject);
		$email->addContent('text/html', $this->html);
		$email->addContent('text/plain', $this->plain ?: strip_tags($this->html));
		$email->addHeaders($this->headers);

		if ($this->template) {
			$email->setTemplateId($this->template);
		}

		/**********************************************************************
		 * Personalizations.
		 */

		foreach ($this->to as $toEmailAddress => $toName) {

			$personalization = new \SendGrid\Mail\Personalization;
			$personalization->addTo(new \SendGrid\Mail\To($toEmailAddress, $toName));

			$substitution = new \SendGrid\Mail\Substitution('content.value', $this->html);
			foreach ($this->variables as $variable => $value) {
				$substitution = new \SendGrid\Mail\Substitution($variable, $value);
				$personalization->addSubstitution($substitution);
			}

			$email->addPersonalization($personalization);

		}

		/**********************************************************************
		 * Attachments.
		 */

		foreach ($this->attachments as $attachment) {
			$email->addAttachment($attachment['file'], $attachment['name'], $attachment['cid']);
		}

		return $email;
	}

	public function send() {
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
