<?php

namespace Katu\Tools\Strings;

use App\Classes\Views\ArrayLoaderTwigEngine;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
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

	public function filterByCode($code): ReplacementCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Replacement $replacement) use ($code) {
			return $replacement->getCode()->getConstantFormat() == (new Code($code))->getConstantFormat();
		})));
	}

	public function mergeWith(ReplacementCollection $replacements): ReplacementCollection
	{
		return new static(array_merge($this->getArrayCopy(), $replacements->getArrayCopy()));
	}

	public function render(string $template): string
	{
		return (string)ArrayLoaderTwigEngine::renderString($template, $this->getArray());
	}

	public function sortByCode(): ReplacementCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (Replacement $a, Replacement $b) {
			return $a->getCode() < $b->getCode() ? -1 : 1;
		});

		return new static($array);
	}

	public function getArray(): array
	{
		return array_combine(array_map(function (Replacement $replacement) {
			return $replacement->getCode()->getConstantFormat();
		}, $this->getArrayCopy()), array_map(function (Replacement $replacement) {
			return $replacement->getValue();
		}, $this->getArrayCopy()));
	}

	public function getFirst(): ?Replacement
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getValue(): ?string
	{
		return $this->getFirst() ? $this->getFirst()->getValue() : null;
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse($this->getArray());
	}
}
