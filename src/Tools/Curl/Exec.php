<?php

namespace Katu\Tools\Curl;

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

	public function __toString()
	{
		return $this->getCommand();
	}

	public function setMethod(string $method)
	{
		$this->method = $method;

		return $this;
	}

	public function setUser(\Katu\Models\Presets\User $user = null)
	{
		$this->user = $user;

		return $this;
	}

	public function getCommand()
	{
		$segments = [
			"curl",
		];

		if ($this->allowInsecure) {
			$segments[] = "--insecure";
		}

		$segments[] = "--request " . $this->method;

		if ($this->user) {
			$segments[] = "--header \"Authorization: Bearer {$this->user->getSafeAccessToken()->getToken()}\"";
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

	public function exec()
	{
		return exec($this->getCommand());
	}
}
