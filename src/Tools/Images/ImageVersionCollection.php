<?php

namespace Katu\Tools\Images;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ImageVersionCollection extends \ArrayObject implements RestResponseInterface
{
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse(array_map(function (ImageVersion $imageVersion) use ($request, $options) {
			return $imageVersion->getRestResponse($request, $options);
		}, $this->getArrayCopy()));
	}
}
