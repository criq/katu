<?php

namespace Katu\Tools\Cookies;

use App\Config\CookieConfig;
use Katu\Tools\Calendar\Time;

class Cookie
{
	protected $domain;
	protected $isHttpOnly;
	protected $isSecure;
	protected $key;
	protected $lifetime;
	protected $path;
	protected $timeExpires;
	protected $value;

	public function __construct(string $key, ?string $value = null)
	{
		$this->setKey($key);
		$this->setValue($value);
	}

	public function setKey(string $key): Cookie
	{
		$this->key = $key;

		return $this;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function setValue(?string $value): Cookie
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function setTimeExpires(?Time $time): Cookie
	{
		$this->timeExpires = $time;

		return $this;
	}

	public function getTimeExpires(): ?Time
	{
		return $this->timeExpires;
	}

	public function getTimestampExpires(): ?int
	{
		if ($this->getTimeExpires()) {
			return $this->getTimeExpires()->getTimestamp();
		}

		$ttl = (new CookieConfig)->getLifetime();
		$time = new Time("+ {$ttl} seconds");

		return $time->getTimestamp();
	}

	public function getPath(): string
	{
		return (new CookieConfig)->getPath();
	}

	public function getDomain(): string
	{
		return (new CookieConfig)->getDomain();
	}

	public function getIsSecure(): bool
	{
		return (new CookieConfig)->getIsSecure();
	}

	public function getIsHttpOnly(): bool
	{
		return (new CookieConfig)->getIsHTTPOnly();
	}

	public function persist(): bool
	{
		return setcookie(
			$this->getKey(),
			$this->getValue(),
			$this->getTimestampExpires(),
			$this->getPath(),
			$this->getDomain(),
			$this->getIsSecure(),
			$this->getIsHttpOnly(),
		);
	}

	public function expire(): bool
	{
		return setcookie(
			$this->getKey(),
			"",
			(new Time("- 1 year"))->getTimestamp(),
			$this->getPath(),
			$this->getDomain(),
			$this->getIsSecure(),
			$this->getIsHttpOnly(),
		);
	}
}
