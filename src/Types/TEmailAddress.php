<?php

namespace Katu\Types;

class TEmailAddress
{
	protected $emailAddress;
	protected $name;

	public function __construct($emailAddress = null, ?string $name = null)
	{
		$this->setEmailAddress($emailAddress);
		$this->setName($name);
	}

	public function __toString(): string
	{
		return (string)$this->emailAddress;
	}

	public static function createFromEnvelope(?string $envelope = null): ?TEmailAddress
	{
		if (preg_match("/^(\"(?<quoted_name>[^\"]*)\"|(?<unquoted_name>[^<]*?))\s*<(?<emailAddress>.+)>$/U", $envelope, $match)) {
			$name = $match["quoted_name"] ?? $match["unquoted_name"] ?? null;
			return new static($match["emailAddress"], $name);
		} else {
			return new static($envelope);
		}
	}

	public function getEnvelope(): string
	{
		if ($this->getName() && $this->getEmailAddress()) {
			return implode(" ", array_filter([
				"\"{$this->getName()}\"",
				"<{$this->getEmailAddress()}>",
			]));
		}

		return $this->getEmailAddress();
	}

	public static function validateEmailAddress(string $emailAddress): bool
	{
		return (bool)filter_var($emailAddress, \FILTER_VALIDATE_EMAIL);
	}

	public function setEmailAddress($emailAddress): TEmailAddress
	{
		if ($emailAddress instanceof \Katu\Models\Presets\EmailAddress) {
			$emailAddress = $emailAddress->getEmailAddress();
		}

		$emailAddress = trim($emailAddress);
		if ($emailAddress && !static::validateEmailAddress($emailAddress)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid e-mail address.");
		}

		$this->emailAddress = trim($emailAddress);

		return $this;
	}

	public function getEmailAddress(): ?string
	{
		return $this->emailAddress;
	}

	public function setName(?string $name = null)
	{
		$this->name = trim($name, " \"") ?:  null;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function getDomain(): string
	{
		return explode("@", $this->getEmailAddress())[1];
	}
}
