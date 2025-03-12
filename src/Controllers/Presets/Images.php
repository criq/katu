<?php

namespace Katu\Controllers\Presets;

use App\Config\ImageConfig;
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

		$version = (new ImageConfig)->getVersions()->filterByTitle($versionCode)->getFirst();
		if (!$version) {
			throw new \Katu\Exceptions\NotFoundException;
		}

		$imageVersion = $image->getImageVersion($version);
		$imageVersion->getVersionImage();

		try {
			$maxAge = (new ImageConfig)->getCacheTimeout();
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
