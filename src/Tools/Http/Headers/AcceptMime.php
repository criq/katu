<?php

namespace Katu\Tools\Http\Headers;

class AcceptMime
{
	protected $mime;
	protected $q;

	public function __construct($mime, $q)
	{
		$this->mime = $mime;

		if (is_null($q)) {
			$this->q = (float)1;
		} elseif (preg_match("/^q=(?<q>[0-9\.]+)$/", $q, $match)) {
			$this->q = (float)$match["q"];
		}
	}

	public function __toString()
	{
		return $this->mime;
	}
}
