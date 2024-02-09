<?php

namespace Katu\Controllers\Presets;

use Katu\Tools\Package\Package;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Images extends \Katu\Controllers\Controller
{
	public function getVersion(ServerRequestInterface $request, ResponseInterface $response, string $imagePackage, string $versionCode)
	{
		\Katu\Tools\System\Memory::setLimit(\Katu\Types\TFileSize::createFromShorthand("2G"));

		$image = \Katu\Tools\Images\Image::createFromPackage(Package::createFromPortableString($imagePackage));
		$version = \Katu\Tools\Images\Version::createFromConfig($versionCode);

		$imageVersion = $image->getImageVersion($version);
		$imageVersion->getVersionImage();

		try {
			$maxAge = (int)\Katu\Config\Config::get("images", "cache", "timeout");
			$response = $response->withAddedHeader("Cache-Control", "max-age={$maxAge}");
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return $response
			->withHeader("Content-Type", $imageVersion->getFile()->getMime())
			->withBody(\GuzzleHttp\Psr7\Utils::streamFor($imageVersion->getFile()->get()))
			;
	}
}
