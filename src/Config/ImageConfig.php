<?php

namespace Katu\Config;

use Katu\Tools\Images\VersionCollection;

class ImageConfig extends \Katu\Config\Config
{
	public function getVersions(): VersionCollection
	{
		return new VersionCollection;
	}

	public function getCacheTimeout(): int
	{
		return 86400 * 28;
	}
}
