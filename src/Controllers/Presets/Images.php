<?php

namespace Katu\Controllers\Presets;

use Katu\Types\TClass;

class Images extends \App\Extensions\Controller
{
	public static function getVersionSrcUrl(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		try {
			try {
				$url = new \Katu\Types\TURL(trim($request->getParam('url')));
			} catch (\Throwable $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			try {
				$version = \Katu\Tools\Images\Version::createFromConfig($args['version']);
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$image = new \Katu\Tools\Images\Image($url);
			$imageVersion = $image->getImageVersion($version);
			$imageVersion->getVersionImage();

			return $response
				->withHeader('Content-Type', $imageVersion->getFile()->getMime())
				->withHeader('Cache-Control', 'max-age=604800, public')
				->write($imageVersion->getFile()->get())
				;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\NotFoundException;
		}
	}

	public static function getVersionSrcFile(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		try {
			$fileClass = \Katu\App::getExtendedClass(new TClass("App\Models\File"), new TClass("Katu\Models\Presets\File"))->getName();
			$file = $fileClass::getOneBy([
				'id' => $args['fileId'],
				'secret' => $args['fileSecret'],
			]);

			if (!$file) {
				throw new \Katu\Exceptions\ModelNotFoundException;
			}

			try {
				$version = \Katu\Tools\Images\Version::createFromConfig($args['version']);
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$image = new \Katu\Tools\Images\Image($file);
			$imageVersion = $image->getImageVersion($version);
			$imageVersion->getVersionImage();

			return $response
				->withHeader('Content-Type', $imageVersion->getFile()->getMime())
				->withHeader('Cache-Control', 'max-age=604800, public')
				->write($imageVersion->getFile()->get())
				;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\NotFoundException;
		}
	}
}
