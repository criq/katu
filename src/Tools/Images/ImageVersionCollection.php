<?php

namespace Katu\Tools\Images;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ImageVersionCollection extends \ArrayObject implements RestResponseInterface
{
	public function filterUsable(): ImageVersionCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (ImageVersion $imageVersion) {
			return $imageVersion->getIsUsable();
		})));
	}

	public function getAssoc(): ImageVersionCollection
	{
		return new static(array_combine(
			array_map(function (ImageVersion $imageVersion) {
				return $imageVersion->getVersion()->getTitle();
			}, $this->getArrayCopy()),
			array_values($this->getArrayCopy()),
		));
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse(array_map(function (ImageVersion $imageVersion) use ($request, $options) {
			return $imageVersion->getRestResponse($request, $options);
		}, $this->getAssoc()->getArrayCopy()));
	}
}
