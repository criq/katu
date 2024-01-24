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
		$imageVersion->getVersionImage();

		return $response
			->withHeader("Content-Type", $imageVersion->getFile()->getMime())
			->withHeader("Cache-Control", "max-age=604800, public")
			->withBody(\GuzzleHttp\Psr7\Utils::streamFor($imageVersion->getFile()->get()))
			;

	}

	/**
	 * @deprecated
	*/
	public static function getVersionSrcFile(ServerRequestInterface $request, ResponseInterface $response, string $fileId, string $fileSecret, string $version)
	{
		try {
			$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\File::class);

			$file = $fileClass::getOneBy([
				"id" => $fileId,
				"secret" => $fileSecret,
			]);

			if (!$file) {
				throw new \Katu\Exceptions\ModelNotFoundException;
			}

			try {
				$version = \Katu\Tools\Images\Version::createFromConfig($version);
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$image = new \Katu\Tools\Images\Image($file);
			$imageVersion = $image->getImageVersion($version);
			$imageVersion->getVersionImage();

			return $response
				->withHeader("Content-Type", $imageVersion->getFile()->getMime())
				->withHeader("Cache-Control", "max-age=604800, public")
				->withBody(\GuzzleHttp\Psr7\Utils::streamFor($imageVersion->getFile()->get()))
				;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\NotFoundException;
		}
	}
}
