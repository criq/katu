<?php

namespace Katu\Tools\Cookies;

use Katu\Tools\Calendar\Time;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;

class Cookie
{
	const DEFAULT_IS_HTTP_ONLY = true;
	const DEFAULT_IS_SECURE = false;
	const DEFAULT_LIFETIME = "1 year";
	const DEFAULT_PATH = "/";

	protected $key;
	protected $value;

	protected $domain;
	protected $isHttpOnly;
	protected $isSecure;
	protected $path;
	protected $timeExpires;

	public function __construct(string $key, ?string $value = null)
	{
		$this->setKey($key);
		$this->setValue($value);
	}

	public static function getDefaultOptions(): OptionCollection
	{
		$defaultLifetime = static::DEFAULT_LIFETIME;

		return new OptionCollection([
			new Option("DOMAIN", static::getDefautDomain()),
			new Option("IS_HTTP_ONLY", static::DEFAULT_IS_HTTP_ONLY),
			new Option("IS_SECURE", static::DEFAULT_IS_SECURE),
			new Option("LIFETIME", abs((new Time("+ {$defaultLifetime}"))->getAge()->getValue())),
			new Option("PATH", static::DEFAULT_PATH),
		]);
	}

	public static function getOptions(): OptionCollection
	{
		try {
			$config = \Katu\Config\Config::get("app", "cookie");
		} catch (\Throwable $e) {
			$config = [];
		}

		return static::getDefaultOptions()->getMergedWith(OptionCollection::createFromArray($config));
	}

	public static function getDefautDomain()
	{
		return "." . \Katu\Tools\Routing\URL::getBase()->get2ndLevelDomain();
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

		$ttl = static::getOptions()->getValue("LIFETIME");
		$time = new Time("+ {$ttl} seconds");

		return $time->getTimestamp();
	}

	public function getPath(): string
	{
		return static::getOptions()->getValue("PATH");
	}

	public function getDomain(): string
	{
		return static::getOptions()->getValue("DOMAIN");
	}

	public function getIsSecure(): bool
	{
		return static::getOptions()->getValue("IS_SECURE");
	}

	public function getIsHttpOnly(): bool
	{
		return static::getOptions()->getValue("IS_HTTP_ONLY");
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
			null,
			(new Time("- 1 year"))->getTimestamp(),
			$this->getPath(),
			$this->getDomain(),
			$this->getIsSecure(),
			$this->getIsHttpOnly(),
		);
	}
}
