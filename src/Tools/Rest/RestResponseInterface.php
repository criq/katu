<?php

namespace Katu\Tools\Rest;

use Psr\Http\Message\ServerRequestInterface;

interface RestResponseInterface
{
	public function getRestResponse(?ServerRequestInterface $request = null): RestResponse;
}
