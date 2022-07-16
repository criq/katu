<?php

namespace Katu\Tools\Events;

class Pattern
{
	protected $text;

	public function __construct(?string $text)
	{
		$this->setText($text);
	}

	public function setText(string $text): Pattern
	{
		$this->text = $text;

		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function getRegex(): string
	{
		$regex = strtr($this->getText(), [
			".+" => "(\.[a-z0-9]+)",
			".*" => "(\.[a-z0-9]+)*",
		]);
		$regex = "/^{$regex}$/i";

		return $regex;
	}

	public function matches(string $attempt): bool
	{
		return preg_match($this->getRegex(), $attempt) ? true : false;
	}
}
