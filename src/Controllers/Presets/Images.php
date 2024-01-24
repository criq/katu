<?php

namespace Katu\Controllers\Presets;

use Katu\Tools\Package\Package;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Images extends \Katu\Controllers\Controller
{
	public function getVersion(ServerRequestInterface $request, ResponseInterface $response, string $imagePackage, string $versionCode)
	{
		$image = \Katu\Tools\Images\Image::createFromPackage(Package::createFromPortableString($imagePackage));
		$version = \Katu\Tools\Images\Version::createFromConfig($versionCode);

		$imageVersion = $image->getImageVersion($version);

		return $response
			->withHeader("Content-Type", $imageVersion->getFile()->getMime())
			->withHeader("Cache-Control", "max-age=604800, public")
			->withBody(\GuzzleHttp\Psr7\Utils::streamFor($imageVersion->getFile()->get()))
			;
	}
}
