<?php

namespace Katu\PDO;

class StatementDump
{
	protected $source;

	public function __construct(?string $source = null)
	{
		$this->setSource($source);
	}

	public function setSource(?string $source = null)
	{
		$this->source = preg_replace("/\v+\t+/", " ", $source);

		return $this;
	}

	public function getSource() : ?string
	{
		return $this->source;
	}

	public function getSentSQL() : ?string
	{
		// echo($this->source);die;

		preg_match("/^Sent SQL: \[[0-9]+\](?<sql>.+)Params:/ms", $this->source, $match);

		return trim($match["sql"] ?? null) ?: null;
	}
}