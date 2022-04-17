<?php

namespace Katu\Tools\Views;

class FilesystemLoaderTwigEngine extends TwigEngine
{
	protected static function getTwigLoader(): \Twig\Loader\LoaderInterface
	{
		return new \Twig\Loader\FilesystemLoader(static::getTwigDirs());
	}

	protected static function getTwigDirs(): array
	{
		$dirs = [
			new \Katu\Files\File(realpath(new \Katu\Files\File(__DIR__, "..", "..", "Views"))),
			new \Katu\Files\File(\Katu\App::getBaseDir(), "vendor"),
			new \Katu\Files\File(\Katu\App::getBaseDir(), "app", "Views"),
		];

		$dirs = array_unique(array_filter(array_map(function ($dir) {
			return $dir->exists() ? $dir->getPath() : null;
		}, $dirs)));

		return $dirs;
	}
}
