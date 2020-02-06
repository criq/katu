<?php

namespace Katu\Controllers\Presets;

class Images extends \Katu\Controllers\Controller
{

	public static function getVersionSrcFile($request, $response, $args)
	{
		try {
			$fileClass = \Katu\App::getExtendedClass('\\App\\Models\\File', '\\Katu\\Models\\File');
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
			$imageVersion->getImage();

			return $response
				->withHeader('Content-Type', $imageVersion->getFile()->getMime())
				->withHeader('Cache-Control', 'max-age=604800, public')
				->write($imageVersion->getFile()->get())
				;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\Exception;
		}
	}

	public static function getVersionSrcUrl($request, $response, $args)
	{
		try {
			try {
				$url = new \Katu\Types\TURL(trim($request->getParam('url')));
			} catch (\Exception $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			try {
				$version = \Katu\Tools\Images\Version::createFromConfig($args['version']);
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$image = new \Katu\Tools\Images\Image($url);
			$imageVersion = $image->getImageVersion($version);
			$imageVersion->getImage();

			return $response
				->withHeader('Content-Type', $imageVersion->getFile()->getMime())
				->withHeader('Cache-Control', 'max-age=604800, public')
				->write($imageVersion->getFile()->get())
				;
		} catch (\Throwable $e) {
			throw new \Katu\Exceptions\Exception;
		}
	}
}
