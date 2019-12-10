<?php

namespace Katu\Tools\Curl;

class Exec {

	public $method = 'GET';
	public $url;
	public $user;

	public $isSilent = true;

	public function __construct(\Katu\Types\TURL $url) {
		$this->url = $url;
	}

	public function setMethod(string $method) {
		$this->method = $method;

		return $this;
	}

	public function setUser(\Katu\Models\Presets\User $user = null) {
		$this->user = $user;

		return $this;
	}

	public function getCommand() {
		$segments = [
			'curl',
			'--request ' . $this->method,
		];

		if ($this->user) {
			$segments[] = '--header "Authorization: Bearer ' . $this->user->getValidAccessToken()->token . '"';
		}

		$segments[] = $this->url;

		if ($this->isSilent) {
			$segments[] = '>/dev/null 2>/dev/null &';
		}

		return implode(' ', $segments);
	}

	public function exec() {
		return exec($this->getCommand());
	}

}
