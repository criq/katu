<?php

namespace Katu;

class Email {

	public $subject;
	public $plain = '';
	public $html = '';

	public $fromEmailAddress;
	public $fromName;

	public $to = [];
	public $cc = [];

	public $headers = [];

	public $attachments = [];

	public function __construct($subject = null) {
		$this->setSubject($subject);

		return $this;
	}

	public function __toString() {
		return (string) $this->html;
	}

	static function resolveEmailAddress($emailAddress) {
		if ($emailAddress instanceof \Katu\Models\EmailAddress) {
			$originalEmailAddress = $emailAddress->emailAddress;
		} elseif ($emailAddress instanceof \Katu\Types\TEmailAddress) {
			$originalEmailAddress = (string) $emailAddress;
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

	public function setPlain($plain) {
		$this->plain = $plain;

		return $this;
	}

	public function setText($text) {
		return $this->setPlain($text);
	}

	public function setHtml($html) {
		$this->html = $html;

		return $this;
	}

	public function setBody($html, $plain = null) {
		$this->setHtml($html);

		if ($plain) {
			$this->setPlain($plain);
		} else {
			$this->setPlain(strip_tags($html));
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

	public function setReplyTo($emailAddress) {
		$emailAddresses = static::resolveEmailAddress($emailAddress);
		$this->addHeader('Reply-To', $emailAddresses[0]);

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

}
