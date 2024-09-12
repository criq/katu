<?php

namespace Katu\Tools\Emails\Providers;

use Katu\Tools\Emails\ProviderConfiguration;

class SmartemailingConfiguration extends ProviderConfiguration
{
	protected $template;

	public function setTemplate(?string $template): SmartemailingConfiguration
	{
		$this->template = $template;

		return $this;
	}

	public function getTemplate(): ?string
	{
		return $this->template;
	}
}
