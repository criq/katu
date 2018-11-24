<?php

namespace Katu\Controllers;

class Images extends \Katu\Controller {

	static function getVersionSrcFile($fileId, $fileSecret, $version, $name) {
		try {

			$app = \Katu\App::get();

			$fileClass = \Katu\App::getExtendedClass('\\App\\Models\\File', '\\Katu\\Models\\File');
			$file = $fileClass::getOneBy([
				'id' => $fileId,
				'secret' => $fileSecret,
			]);

			if (!$file) {
				throw new \Katu\Exceptions\ModelNotFoundException;
			}

			$versionFile = \Katu\Utils\Image::getVersionFile($file, $version);
			if (!$versionFile) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$app->response->headers->set('Content-Type', $file->type);
			$app->response->headers->set('Cache-Control', 'max-age=604800');
			$app->response->setBody($versionFile->get());

		} catch (\Exception $e) {
			throw new \Katu\Exceptions\Exception;
		}
	}

	static function getVersionSrcUrl($version) {
		try {

			$app = \Katu\App::get();

			$url = new \Katu\Types\TUrl($app->request->params('url'));

			$versionFile = \Katu\Utils\Image::getVersionFile($url, $version);
			if (!$versionFile) {
				throw new \Katu\Exceptions\NotFoundException;
			}

			$app->response->headers->set('Content-Type', $versionFile->getMime());
			$app->response->headers->set('Cache-Control', 'max-age=604800');
			$app->response->setBody($versionFile->get());

		} catch (\Exception $e) {
			throw new \Katu\Exceptions\Exception;
		}
	}

}
