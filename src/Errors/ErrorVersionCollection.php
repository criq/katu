<?php

namespace Katu\Errors;

use Katu\Tools\Intl\Locale;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorVersionCollection extends \ArrayObject implements RestResponseInterface
{
	public static function createFromArray(?array $array): ErrorVersionCollection
	{
		$res = new static;
		foreach ($array as $locale => $message) {
			$res[] = new ErrorVersion(new Locale($locale), $message);
		}

		return $res;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse(array_map(function (ErrorVersion $errorVersion) use ($request, $options) {
			return $errorVersion->getRestResponse($request, $options);
		}, $this->getArrayCopy()));
	}
}
