<?php

namespace Katu\Utils;

class Email {

	public $subject;
	public $text;
	public $html;

	public $fromEmailAddress;
	public $fromName;

	public $to = [];
	public $cc = [];

	public $headers = [];

	public $attachments = [];

	public $template;
	public $content = [];
	public $variables = [];
	public $recipientVariables = [];

	public $async = false;

	public function __construct($subject = null) {
		$this->setSubject($subject);

		return $this;
	}

	public function __toString() {
		return $this->html;
	}

	static function resolveEmailAddress($emailAddress) {
		if ($emailAddress instanceof \Katu\Models\EmailAddress) {
			$originalEmailAddress = $emailAddress->emailAddress;
		} else {
			$originalEmailAddress = $emailAddress;
		}

		try {

			$fakeEmailAddresses = (array) \Katu\Config::get('app', 'email', 'useFakeEmailAddress');
			$emailAddresses = [];
			foreach ($fakeEmailAddresses as $fakeEmailAddress) {
				list($username, $domain) = explode('@', $fakeEmailAddress);
				$emailAddresses[] = $username . '+' . substr(md5($originalEmailAddress), 0, 8) . '@' . $domain;
			}

			return $emailAddresses;

		} catch (\Katu\Exceptions\MissingConfigException $e) {

			return [$originalEmailAddress];

		}
	}

	public function setSubject($subject) {
		$this->subject = $subject;

		return $this;
	}

	public function setText($text) {
		$this->text = $text;

		return $this;
	}

	public function setHtml($html) {
		$this->html = $html;

		return $this;
	}

	public function setBody($html, $text = null) {
		$this->setHtml($html);

		if ($text) {
			$this->setText($text);
		} else {
			$this->setText(strip_tags($html));
		}

		return $this;
	}

	public function setFromEmailAddress($fromEmailAddress) {
		$emailAddresses = static::resolveEmailAddress($fromEmailAddress);
		$this->fromEmailAddress = $emailAddresses[0];

		return $this;
	}

	public function setFromName($fromName) {
		$this->fromName = $fromName;

		return $this;
	}

	public function setFrom($fromEmailAddress, $fromName = null) {
		$this->setFromEmailAddress($fromEmailAddress);
		$this->setFromName($fromName);

		return $this;
	}

	public function addTo($toEmailAddress, $toName = null) {
		foreach (static::resolveEmailAddress($toEmailAddress) as $emailAddress) {
			$this->to[$emailAddress] = $toName;
		}

		return $this;
	}

	public function addCc($toEmailAddress, $toName = null) {
		foreach (static::resolveEmailAddress($toEmailAddress) as $emailAddress) {
			$this->cc[$emailAddress] = $toName;
		}

		return $this;
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;

		return $this;
	}

	public function setReplyTo($emailAddress) {
		$emailAddresses = static::resolveEmailAddress($emailAddress);
		$this->addHeader('Reply-To', $emailAddresses[0]);

		return $this;
	}

	public function addAttachment() {
		// File.
		if (count(func_get_args()) == 1 && func_get_arg(0) instanceof File) {

			$file = func_get_arg(0);
			$this->attachments[] = [
				'name'    => $file->getBasename(),
				'type'    => $file->getMime(),
				'content' => base64_encode(file_get_contents($file)),
			];

			return $this;

		} elseif (count(func_get_args()) == 3) {

			$this->attachments[] = [
				'name'    => func_get_arg(0),
				'type'    => func_get_arg(1),
				'content' => base64_encode(func_get_arg(2)),
			];

			return $this;

		}

		throw new \Katu\Exceptions\InputErrorException("Invalid attachment arguments.");
	}

	public function setTemplate($template) {
		$this->template = $template;

		return $this;
	}

	public function setContent($name, $value) {
		$this->content[$name] = $value;

		return $this;
	}

	public function setVariable($name, $value) {
		$this->variables[$name] = $value;

		return $this;
	}

	public function setRecipientVariable($emailAddress, $name, $value) {
		foreach (static::resolveEmailAddress($emailAddress) as $emailAddress) {
			$this->recipientVariables[$emailAddress][$name] = $value;
		}

		return $this;
	}

	public function setAsync($async = true) {
		$this->async = (bool) $async;

		return $this;
	}

	public function getMessageForMandrill($message) {
		$message['subject']    = $this->subject;
		$message['html']       = $this->html;
		$message['text']       = $this->text;
		$message['from_email'] = $this->fromEmailAddress;
		$message['from_name']  = $this->fromName;
		$message['headers']    = $this->headers;

		foreach ($this->to as $toEmailAddress => $toName) {
			$message['to'][] = [
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'to',
			];
		}

		foreach ($this->cc as $toEmailAddress => $toName) {
			$message['to'][] = [
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'cc',
			];
		}

		$message['attachments'] = $this->attachments;

		$message['global_merge_vars'] = $this->getVariablesForMandrill();
		$message['merge_vars'] = $this->getRecipientVariablesForMandrill();

		$message['async'] = $this->async;

		return $message;
	}

	public function getContentForMandrill() {
		$content = [];

		foreach ($this->content as $name => $value) {
			$content[] = [
				'name'    => $name,
				'content' => $value,
			];
		}

		return $content;
	}

	public function getVariablesForMandrill() {
		$variables = [];

		foreach ($this->variables as $name => $value) {
			$variables[] = [
				'name'    => $name,
				'content' => $value,
			];
		}

		return $variables;
	}

	public function getRecipientVariablesForMandrill() {
		$variables = [];

		foreach ($this->recipientVariables as $recipient => $vars) {
			$recipientVars = [];
			foreach ($vars as $name => $value) {
				$recipientVars[] = [
					'name'    => $name,
					'content' => $value,
				];
			}
			$variables[] = [
				'rcpt' => $recipient,
				'vars' => $recipientVars,
			];
		}

		return $variables;
	}

	public function sendWithMandrillThroughApi($mandrillApi, $message = []) {
		if ($this->template) {
			return $mandrillApi->messages->sendTemplate($this->template, $this->getContentForMandrill(), $this->getMessageForMandrill($message));
		} else {
			return $mandrillApi->messages->send($this->getMessageForMandrill($message));
		}
	}

}
