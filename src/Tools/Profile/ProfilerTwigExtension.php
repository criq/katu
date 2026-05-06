<?php

namespace Katu\Tools\Profile;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig: {% profile 'label' %}…{% endprofile %} and {{ profileLap('label') }} (prefer {% do profileLap('label') %} to avoid output).
 */
class ProfilerTwigExtension extends AbstractExtension
{
	public function getTokenParsers()
	{
		return [
			new ProfileTokenParser(),
		];
	}

	public function getFunctions()
	{
		return [
			new TwigFunction("profileLap", function (string $name) {
				ProfilerContext::lap($name);

				return "";
			}),
		];
	}
}
