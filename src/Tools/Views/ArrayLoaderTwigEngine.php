<?php

namespace Katu\Tools\Views;

use Katu\Tools\Strings\ReplacementCollection;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

class ArrayLoaderTwigEngine extends TwigEngine
{
	public function __construct(?ServerRequestInterface $request = null, array $templates = [])
	{
		parent::__construct(...func_get_args());

		$this->setTemplates($templates);
	}

	protected static function getTwigLoader(): LoaderInterface
	{
		return new ArrayLoader;
	}

	public function setTemplates(array $templates): ArrayLoaderTwigEngine
	{
		foreach ($templates as $name => $template) {
			$this->setTemplate($name, $template);
		}

		return $this;
	}

	public function setTemplate(string $name, ?string $template): ArrayLoaderTwigEngine
	{
		$this->getTwig()->getLoader()->setTemplate($name, $template);

		return $this;
	}

	public static function renderStringWithoutGlobals(?string $template, ?array $replacements = []): string
	{
		if (!$template) {
			return "";
		}

		return (new static)->setTemplate("template", $template)->render("template", $replacements);
	}

	public static function renderString(?string $template, ?array $replacements = []): string
	{
		return static::renderStringWithoutGlobals($template, array_merge(ReplacementCollection::createGlobal()->getArray(), $replacements));
	}
}
