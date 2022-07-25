<?php

namespace Katu\Tools\Http\Headers;

use Psr\Http\Message\ServerRequestInterface;

class Accept extends \Katu\Tools\Http\HeaderCollection
{
	public static function createFromRequest(ServerRequestInterface $request)
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
