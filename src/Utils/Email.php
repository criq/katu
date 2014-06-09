<?php

namespace Katu\Utils;

class Email {

	public $subject;
	public $text;
	public $html;

	public $fromEmailAddress;
	public $fromName;

	public $to = array();

	public $headers = array();

	public function __construct($subject = NULL) {
		$this->setSubject($subject);
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function setHtml($html) {
		$this->html = $html;
	}

	public function setBody($html, $text = NULL) {
		$this->setHtml($html);

		if ($text) {
			$this->setText($text);
		} else {
			$this->setText(strip_tags($html));
		}
	}

	public function setFromEmailAddress($fromEmailAddress) {
		$this->fromEmailAddress = $fromEmailAddress;
	}

	public function setFromName($fromName) {
		$this->fromName = $fromName;
	}

	public function setFrom($fromEmailAddress, $fromName = NULL) {
		$this->setFromEmailAddress($fromEmailAddress);
		$this->setFromName($fromName);
	}

	public function addTo($toEmailAddress, $toName = NULL) {
		$this->to[$toEmailAddress] = $toName;
	}

	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	public function setReplyTo($emailAddress) {
		$this->addHeader('Reply-To', $emailAddress);
	}

	public function sendWithMandrillThroughApi($mandrillApi, $options = array()) {
		$message = array(
			'subject'    => $this->subject,
			'html'       => $this->html,
			'text'       => $this->text,
			'from_email' => $this->fromEmailAddress,
			'from_name'  => $this->fromName,
			'headers'    => $this->headers,
		);

		foreach ($this->to as $toEmailAddress => $toName) {
			$message['to'][] = array(
				'email' => $toEmailAddress,
				'name'  => $toName,
				'type'  => 'to',
			);
		}

		return $mandrillApi->messages->send($message);
	}

}
