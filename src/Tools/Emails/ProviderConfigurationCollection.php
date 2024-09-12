<?php

namespace Katu\Tools\Emails;

use Katu\Tools\Emails\Providers\SmartemailingConfiguration;
use Katu\Types\TClass;

class ProviderConfigurationCollection extends \ArrayObject
{
	public function filterByClass(TClass $class): ProviderConfigurationCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (ProviderConfiguration $providerConfiguration) use ($class) {
			return new TClass($providerConfiguration) == $class;
		})));
	}

	public function getFirst(): ?ProviderConfiguration
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getSmartemailingConfiguration(): ?SmartemailingConfiguration
	{
		return $this->filterByClass(new TClass(SmartemailingConfiguration::class))->getFirst() ?: new SmartemailingConfiguration;
	}
}
