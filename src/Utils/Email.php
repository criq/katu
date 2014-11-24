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

	public $template;
	public $content = [];
	public $variables = [];
	public $recipientVariables = [];

	public function __construct($subject = null) {
		$this->setSubject($subject);

		return $this;
	}

	static function resolveEmailAddress($emailAddress) {
		try {
			$fakeEmailAddress = \Katu\Config::get('app', 'email', 'useFakeEmailAddress');
			list($username, $domain) = explode('@', $fakeEmailAddress);
			return $username . '+' . md5($emailAddress) . '@' . $domain;
		} catch (\Exception $e) {
			return $emailAddress;
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
		$this->fromEmailAddress = static::resolveEmailAddress($fromEmailAddress);

		return $this;
	}

	public function setFromName($fromName) {
		$this->fromName = $fromName;

		return $this;
	}

	public function setFrom($fromEmailAddress, $fromName = null) {
		$this->setFromEmailAddress(static::resolveEmailAddress($fromEmailAddress));
		$this->setFromName($fromName);

		return $this;
	}

	public function addTo($toEmailAddress, $toName = null) {
		$this->to[static::resolveEmailAddress($toEmailAddress)] = $toName;

		return $this;
	}

	public function addCc($toEmailAddress, $toName = null) {
		$this->cc[static::resolveEmailAddress($toEmailAddress)] = $toName;

		return $this;
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;

		return $this;
	}

	public function setReplyTo($emailAddress) {
		$this->addHeader('Reply-To', static::resolveEmailAddress($emailAddress));

		return $this;
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
		$this->recipientVariables[static::resolveEmailAddress($emailAddress)][$name] = $value;

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

		$message['global_merge_vars'] = $this->getVariablesForMandrill();
		$message['merge_vars'] = $this->getRecipientVariablesForMandrill();

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
		var_dump($this); die;
		if ($this->template) {
			return $mandrillApi->messages->sendTemplate($this->template, $this->getContentForMandrill(), $this->getMessageForMandrill($message));
		} else {
			return $mandrillApi->messages->send($this->getMessageForMandrill($message));
		}
	}

}
