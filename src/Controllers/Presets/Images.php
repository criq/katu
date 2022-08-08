<?php

namespace Katu\Controllers\Presets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Images extends \Katu\Controllers\Controller
{
	public function getVersionSrcURL(ServerRequestInterface $request, ResponseInterface $response, string $version)
	{
		try {
			try {
				$url = new \Katu\Types\TURL(trim($request->getQueryParams()["url"] ?? null));
			} catch (\Throwable $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			try {
				$version = \Katu\Tools\Images\Version::createFromConfig($version);
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$image = new \Katu\Tools\Images\Image($url);
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

	public static function getVersionSrcFile(ServerRequestInterface $request, ResponseInterface $response, string $fileId, string $fileSecret, string $version)
	{
		try {
			$fileClassName = \App\App::getFileModelClass()->getName();

			$file = $fileClassName::getOneBy([
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
