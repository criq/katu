<?php

namespace Katu\Tools\Strings;

use App\Classes\Users\RepresentativeCollection;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Views\ArrayLoaderTwigEngine;
use Psr\Http\Message\ServerRequestInterface;

class ReplacementCollection extends \ArrayObject implements RestResponseInterface
{
	public static function createFromArray(array $array)
	{
		$replacements = new static;

		foreach ($array as $key => $value) {
			$replacements[] = new Replacement(new Code($key), $value);
		}

		return $replacements;
	}

	public static function createGlobal(): ReplacementCollection
	{
		return (new static)
			->mergeWith(RepresentativeCollection::createFromConfig()->getReplacements())
			;
	}

	public function mergeWith(ReplacementCollection $replacements): ReplacementCollection
	{
		return new static(array_merge($this->getArrayCopy(), $replacements->getArrayCopy()));
	}

	public function render(string $template): string
	{
		return (string)ArrayLoaderTwigEngine::renderString($template, $this->getArray());
	}

	public function getArray(): array
	{
		return array_combine(array_map(function (Replacement $replacement) {
			return $replacement->getCode()->getConstantFormat();
		}, $this->getArrayCopy()), array_map(function (Replacement $replacement) {
			return $replacement->getValue();
		}, $this->getArrayCopy()));
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse($this->getArray());
	}
}
