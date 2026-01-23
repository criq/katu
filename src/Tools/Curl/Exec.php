<?php

namespace Katu\Tools\Curl;

use Katu\Tools\Users\UserInterface;

class Exec
{
	public $isSilent = true;
	public $allowInsecure = true;
	public $method = "GET";
	public $url;
	public $user;

	public function __construct(\Katu\Types\TURL $url)
	{
		$this->url = $url;
	}

	public function __toString(): string
	{
		return $this->getCommand();
	}

	public function setMethod(string $method): Exec
	{
		$this->method = $method;

		return $this;
	}

	public function setUser(?UserInterface $user = null): Exec
	{
		$this->user = $user;

		return $this;
	}

	public function getUser(): ?UserInterface
	{
		return $this->user;
	}

	public function getCommand(): string
	{
		$segments = [
			"curl",
		];

		if ($this->allowInsecure) {
			$segments[] = "--insecure";
		}

		$segments[] = "--request " . $this->method;

		if ($this->user) {
			$segments[] = "--header \"Authorization: Bearer {$this->user->getOrCreateSafeAccessToken()->getToken()}\"";
		}

		if ($this->method == "GET") {
			$segments[] = "--url " . (string)$this->url;
		} else {
			$segments[] = "--url " . $this->url->getWithoutQuery();
			$segments[] = "-H \"Content-Type: application/json\"";
			$segments[] = "--data \"" . \Katu\Files\Formats\JSON::encodeStandard($this->url->getQueryParams()) . "\"";
		}

		if ($this->isSilent) {
			$segments[] = ">/dev/null 2>/dev/null &";
		}

		return implode(" ", $segments);
	}

	/**
	 * @return string|false
	 */
	public function exec()
	{
		return exec($this->getCommand());
	}
}
