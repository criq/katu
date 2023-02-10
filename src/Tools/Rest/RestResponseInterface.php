<?php

namespace Katu\Tools\Rest;

use Katu\Tools\Options\OptionCollection;
use Psr\Http\Message\ServerRequestInterface;

interface RestResponseInterface
{
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse;
}
