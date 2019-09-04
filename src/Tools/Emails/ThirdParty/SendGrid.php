<?php

namespace Katu\Tools\Emails\ThirdParty;

class SendGrid extends \Katu\Tools\Emails\ThirdParty {

	public $attachments = [];

	static function getDefaultApi() {
		$app = \Katu\App::get();

		try {
			$key = \Katu\Config::get('app', 'email', 'useSendGridKey');
		} catch (\Exception $e) {
			$key = 'live';
		}

		return new \SendGrid(\Katu\Config::get('sendGrid', 'api', 'keys', $key));
	}

	public function getEmail($message = []) {
		$email = new \SendGrid\Email;

		if ($this->template) {
			$email->setTemplateId($this->template);
		}

		$email->setSubject($this->subject);
		$email->setHtml($this->html);
		$email->setText($this->plain);
		$email->setFrom($this->fromEmailAddress);
		$email->setFromName($this->fromName);
		$email->setHeaders($this->headers);

		/**********************************************************************
		 * Substitutions.
		 */

		$substitutions = [];

		foreach ($this->to as $toEmailAddress => $toName) {
			$email->addTo($toEmailAddress, $toName);
			foreach ($this->variables as $variable => $value) {
				$substitutions[$toEmailAddress][$variable] = $value;
			}
			if (isset($this->recipientVariables[$toEmailAddress])) {
				foreach ($this->recipientVariables[$toEmailAddress] as $variable => $value) {
					$substitutions[$toEmailAddress][$variable] = $value;
				}
			}
		}

		foreach ($this->cc as $toEmailAddress => $toName) {
			$email->addCc($toEmailAddress, $toName);
			foreach ($this->variables as $variable => $value) {
				$substitutions[$toEmailAddress][$variable] = $value;
			}
			if (isset($this->recipientVariables[$toEmailAddress])) {
				foreach ($this->recipientVariables[$toEmailAddress] as $variable => $value) {
					$substitutions[$toEmailAddress][$variable] = $value;
				}
			}
		}

		$smtpApiTo = [];
		$smtpApiSubstitutions = [];

		foreach ($substitutions as $emailAddress => $substitutions) {
			$smtpApiTo[] = $emailAddress;
			foreach ($substitutions as $variable => $value) {
				$smtpApiSubstitutions[$variable][] = $value;
			}
		}

		$email->setSmtpapiTos($smtpApiTo);
		$email->setSubstitutions($smtpApiSubstitutions);

		/**********************************************************************
		 * Sections.
		 */

		foreach ($this->variables as $variable => $value) {
			$email->addSection($variable, $value);
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
			$sendGridApi = $args[0];
		} else {
			$sendGridApi = static::getDefaultApi();
		}

		$email = $this->getEmail();

		return $sendGridApi->send($email);
	}

}
