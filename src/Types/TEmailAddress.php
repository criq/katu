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
		return (string)$this->value;
	}

	public static function validateEmailAddress(string $emailAddress): bool
	{
		return (bool)filter_var($emailAddress, \FILTER_VALIDATE_EMAIL);
	}

	public function setEmailAddress($emailAddress): TEmailAddress
	{
		if ($emailAddress instanceof \App\Models\EmailAddress) {
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
		$this->name = trim($name);

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

	/**
	 * @deprecated
	 */
	public static function createFromEnvelope(?string $encoded = null)
	{
		if (preg_match("/^(?<name>.*)\s*<(?<emailAddress>.+)>$/U", $encoded, $match)) {
			return new static($match["emailAddress"], $match["name"]);
		} else {
			return new static($encoded);
		}
	}

	/**
	 * @deprecated
	 */
	public function getEnvelope()
	{
		return implode(" ", array_filter([
			$this->getName(),
			"<" . $this->getEmailAddress() . ">",
		]));
	}
}
