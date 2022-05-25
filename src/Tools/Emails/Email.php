<?php

namespace Katu\Tools\Emails;

class Email
{
	public $attachments = [];
	public $cc = [];
	public $fromEmailAddress;
	public $fromName;
	public $headers = [];
	public $html = "";
	public $plain = "";
	public $subject;
	public $to = [];

	public function __construct($subject = null)
	{
		$this->setSubject($subject);

		return $this;
	}

	public function __toString(): string
	{
		return (string) $this->html;
	}

	public static function resolveEmailAddress($emailAddress)
	{
		if ($emailAddress instanceof \Katu\Models\Presets\EmailAddress) {
			$originalEmailAddress = $emailAddress->emailAddress;
		} elseif ($emailAddress instanceof \Katu\Types\TEmailAddress) {
			$originalEmailAddress = (string)$emailAddress;
		} else {
			$originalEmailAddress = $emailAddress;
		}

		try {
			$fakeEmailAddresses = (array)\Katu\Config\Config::get("app", "email", "useFakeEmailAddress");
			$emailAddresses = [];
			foreach ($fakeEmailAddresses as $fakeEmailAddress) {
				list($username, $domain) = explode("@", $fakeEmailAddress);
				$emailAddresses[] = $username . "+" . substr(md5($originalEmailAddress), 0, 8) . "@" . $domain;
			}

			return $emailAddresses;
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return [$originalEmailAddress];
		}
	}

	public function setSubject($subject): Email
	{
		$this->subject = $subject;

		return $this;
	}

	public function getSubject(): ?string
	{
		return $this->subject;
	}

	public function setPlain($plain): Email
	{
		$this->plain = $plain;

		return $this;
	}

	public function getPlain(): ?string
	{
		return $this->plain;
	}

	public function setText($text): Email
	{
		return $this->setPlain($text);
	}

	public function getText(): ?string
	{
		return $this->getPlain();
	}

	public function setHtml($html): Email
	{
		$this->html = $html;

		return $this;
	}

	public function getHtml(): ?string
	{
		return $this->html;
	}

	public function setBody($html, $plain = null): Email
	{
		$this->setHtml($html);
		$this->setPlain($plain ?: strip_tags($html));

		return $this;
	}

	public function setFromEmailAddress($fromEmailAddress): Email
	{
		$emailAddresses = static::resolveEmailAddress($fromEmailAddress);
		$this->fromEmailAddress = $emailAddresses[0];

		return $this;
	}

	public function getFromEmailAddress(): ?string
	{
		return $this->fromEmailAddress;
	}

	public function setFromName($fromName): Email
	{
		$this->fromName = $fromName;

		return $this;
	}

	public function getFromName(): ?string
	{
		return $this->fromName;
	}

	public function setFrom($fromEmailAddress, $fromName = null): Email
	{
		$this->setFromEmailAddress($fromEmailAddress);
		$this->setFromName($fromName);

		return $this;
	}

	public function setReplyTo($emailAddress): Email
	{
		$emailAddresses = static::resolveEmailAddress($emailAddress);
		$this->addHeader("Reply-To", $emailAddresses[0]);

		return $this;
	}

	public function addTo($toEmailAddress, $toName = null): Email
	{
		foreach (static::resolveEmailAddress($toEmailAddress) as $emailAddress) {
			$this->to[$emailAddress] = $toName;
		}

		return $this;
	}

	public function resetTo(): Email
	{
		$this->to = [];

		return $this;
	}

	public function getTo(): array
	{
		return (array)$this->to;
	}

	public function addCc($toEmailAddress, $toName = null): Email
	{
		foreach (static::resolveEmailAddress($toEmailAddress) as $emailAddress) {
			$this->cc[$emailAddress] = $toName;
		}

		return $this;
	}

	public function addHeader($name, $value): Email
	{
		$this->headers[$name] = $value;

		return $this;
	}
}
