<?php

namespace Katu\Tools\Http\Headers;

class Accept extends \Katu\Tools\Http\Headers
{
	public static function createFromRequest(\Slim\Http\Request $request)
	{
		return new static($request->getHeader("accept"));
	}

	public function accepts(string $mime)
	{
		return in_array($mime, $this->getAcceptList());
	}

	public function getAcceptList()
	{
		$res = [];
		foreach ($this as $header) {
			foreach (explode(",", $header) as $i) {
				list($mime, $q) = array_pad(explode(";", $i), 2, null);
				$res[] = new AcceptMime($mime, $q);
			}
		}

		return $res;
	}
}
