<?php

namespace Katu\Controllers\Presets;

use Katu\Tools\Package\Package;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Images extends \Katu\Controllers\Controller
{
	public function getVersion(ServerRequestInterface $request, ResponseInterface $response, string $imagePackage, string $versionCode, string $extension)
	{
		\Katu\Tools\System\Memory::setLimit(\Katu\Types\TFileSize::createFromShorthand("2G"));

		$image = \Katu\Tools\Images\Image::createFromPackage(Package::createFromPortableString($imagePackage));
		if (!$image) {
			throw new \Katu\Exceptions\NotFoundException;
		}

		$version = \Katu\Tools\Images\Version::createFromConfig($versionCode);
		if (!$version) {
			throw new \Katu\Exceptions\NotFoundException;
		}

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
