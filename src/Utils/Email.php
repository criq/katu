<?php

namespace Katu\Utils;

class Email {

	public $subject;
	public $text;
	public $html;

	public $fromEmailAddress;
	public $fromName;

	public $to = array();
	public $cc = array();

	public $headers = array();

	public $template;
	public $content = array();
	public $variables = array();
	public $recipientVariables = array();

	public function __construct($subject = NULL) {
		$this->setSubject($subject);

		return $this;
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

	public function setBody($html, $text = NULL) {
		$this->setHtml($html);

		if ($text) {
			$this->setText($text);
		} else {
			$this->setText(strip_tags($html));
		}

		return $this;
	}

	public function setFromEmailAddress($fromEmailAddress) {
		$this->fromEmailAddress = $fromEmailAddress;

		return $this;
	}

	public function setFromName($fromName) {
		$this->fromName = $fromName;

		return $this;
	}

	public function setFrom($fromEmailAddress, $fromName = NULL) {
		$this->setFromEmailAddress($fromEmailAddress);
		$this->setFromName($fromName);

		return $this;
	}

	public function addTo($toEmailAddress, $toName = NULL) {
		$this->to[$toEmailAddress] = $toName;

		return $this;
	}

	public function addCc($toEmailAddress, $toName = NULL) {
		$this->cc[$toEmailAddress] = $toName;

		return $this;
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;

		return $this;
	}

	public function setReplyTo($emailAddress) {
		$this->addHeader('Reply-To', $emailAddress);

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
		$this->recipientVariables[$emailAddress][$name] = $value;

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
			$message['to'][] = array(
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'to',
			);
		}

		foreach ($this->cc as $toEmailAddress => $toName) {
			$message['to'][] = array(
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'cc',
			);
		}

		$message['global_merge_vars'] = $this->getVariablesForMandrill();
		$message['merge_vars'] = $this->getRecipientVariablesForMandrill();

		return $message;
	}

	public function getContentForMandrill() {
		$content = array();

		foreach ($this->content as $name => $value) {
			$content[] = array(
				'name'    => $name,
				'content' => $value,
			);
		}

		return $content;
	}

	public function getVariablesForMandrill() {
		$variables = array();

		foreach ($this->variables as $name => $value) {
			$variables[] = array(
				'name'    => $name,
				'content' => $value,
			);
		}

		return $variables;
	}

	public function getRecipientVariablesForMandrill() {
		$variables = array();

		foreach ($this->recipientVariables as $recipient => $vars) {
			$recipientVars = array();
			foreach ($vars as $name => $value) {
				$recipientVars[] = array(
					'name'    => $name,
					'content' => $value,
				);
			}
			$variables[] = array(
				'rcpt' => $recipient,
				'vars' => $recipientVars,
			);
		}

		return $variables;
	}

	public function sendWithMandrillThroughApi($mandrillApi, $message = array()) {
		if ($this->template) {
			return $mandrillApi->messages->sendTemplate($this->template, $this->getContentForMandrill(), $this->getMessageForMandrill($message));
		} else {
			return $mandrillApi->messages->send($this->getMessageForMandrill($message));
		}
	}

}
