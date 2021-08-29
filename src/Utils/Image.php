<?php

namespace Katu\Utils;

class Image {

	const VERSION_DIR = 'image/versions';

	static function getValidSource($source) {
		if ($source instanceof \App\Models\File) {
			$source = $source->getPath();
		} elseif ($source instanceof \App\Models\FileAttachment) {
			$source = $source->getFile()->getPath();
		} elseif ($source instanceof \Katu\ModelBase) {
			$source = $source->getImagePath();
		} elseif ($source instanceof \Katu\Utils\File) {
			$source = (string)$source;
		}

		return $source;
	}

	static function getDirName() {
		return \Katu\Config::get('app', 'tmp', 'publicDir');
	}

	static function getDirPath() {
		$path = BASE_DIR . '/' . static::getDirName();

		// Check the writability of the folder.
		if (!is_writable($path)) {
			throw new \Katu\Exceptions\ErrorException("Public tmp folder isn't writable.");
		}

		return realpath($path);
	}

	static function getMime($path) {
		$size = @getimagesize($path);
		if (!isset($size['mime'])) {
			return false;
		}

		return $size['mime'];
	}

	static function getType($path) {
		$mime = static::getMime($path);
		if (strpos($mime, 'image/') !== 0) {
			return false;
		}

		list($image, $type) = explode('/', $mime);

		return $type;
	}

	static function getImageCreateFunctionName($path) {
		$type = static::getType($path);
		switch ($type) {
			case 'jpeg' : return 'imagecreatefromjpeg'; break;
			case 'gif'  : return 'imagecreatefromgif';  break;
			case 'png'  : return 'imagecreatefrompng';  break;
		}

		return false;
	}

	static function getSize($path) {
		$size = @getimagesize($path);
		if (!$size) {
			return false;
		}

		return new \Katu\Types\TImageSize($size[0], $size[1]);
	}

	static function getWidth($path) {
		$size = self::getSize($path);
		if ($size) {
			return $size->x;
		}

		return false;
	}

	static function getHeight($path) {
		$size = self::getSize($path);
		if ($size) {
			return $size->y;
		}

		return false;
	}

	static function getVersionFilename($uri, $version) {
		$uri = static::getValidSource($uri);

		try {
			$url = new \Katu\Types\TUrl($uri);
			$parts = $url->getParts();
			$pathinfo = pathinfo($parts['path']);

			$fileNameHashParts = [
				$parts['scheme'],
				$parts['host'],
				$parts['path'],
				$parts['query'],
			];
		} catch (\Exception $e) {
			$pathinfo = pathinfo($uri);

			$fileNameHashParts = [
				$uri,
			];
		}

		$fileNameHash = sha1(JSON::encodeStandard($fileNameHashParts));

		$fileNameSuffixes = [
			'version' => (string)sha1(JSON::encodeStandard($version)),
		];

		$fileNameSuffix = (new \Katu\Types\TArray($fileNameSuffixes))->implodeWithKeys('_');

		$versionConfig = static::getVersionConfig($version);

		$fileNameExtension = null;
		if (isset($versionConfig['extension'])) {
			$fileNameExtension = '.' . $versionConfig['extension'];
		} elseif (isset($pathinfo['extension'])) {
			$fileNameExtension = '.' . $pathinfo['extension'];
		}

		return implode([
			implode('_', array_filter([
				$fileNameHash,
				$fileNameSuffix,
			])),
			$fileNameExtension,
		]);
	}

	static function getVersionUrl($source, $version) {
		if ($source instanceof \App\Models\File) {

			$file = $source;
			if (!$file) {
				return false;
			}

			return \Katu\Utils\Url::getFor('images.getVersionSrc.file', [
				'fileId' => $file->getId(),
				'fileSecret' => $file->getSecret(),
				'version' => $version,
				'name' => $file->name,
			]);

		} elseif ($source instanceof \App\Models\FileAttachment) {

			$file = $source->getFile();
			if (!$file) {
				return false;
			}

			return \Katu\Utils\Url::getFor('images.getVersionSrc.file', [
				'fileId' => $file->getId(),
				'fileSecret' => $file->secret,
				'version' => $version,
				'name' => $file->name,
			]);

		} elseif ($source instanceof \Katu\ModelBase) {

			$file = $source->getImageFile();
			if (!$file) {
				return false;
			}

			return \Katu\Utils\Url::getFor('images.getVersionSrc.file', [
				'fileId' => $file->getId(),
				'fileSecret' => $file->secret,
				'version' => $version,
				'name' => $file->name,
			]);

		} elseif ($source instanceof \Katu\Utils\File) {

			return \Katu\Utils\Url::getFor('images.getVersionSrc.url', [
				'version' => $version,
			], [
				'url' => (string)$source->getUrl(),
			]);

		}

		return \Katu\Utils\Url::getFor('images.getVersionSrc.url', [
			'version' => $version,
		], [
			'url' => (string)$source,
		]);
	}

	static function getVersionFile($uri, $version) {
		$versionFilename = self::getVersionFilename($uri, $version);
		$thumbnailPath = new \Katu\Utils\File(\Katu\Utils\FileSystem::joinPaths(static::getDirPath(), static::VERSION_DIR, $version, substr($versionFilename, 0, 2), substr($versionFilename, 2, 2), $versionFilename));

		try {
			static::makeVersion($uri, $thumbnailPath, $version);
		} catch (\Exception $e) {
			\Katu\ErrorHandler::handle($e);
			return false;
		}

		return $thumbnailPath;
	}

	static function makeVersion($source, $destination, $version) {
		if (!file_exists($destination)) {

			$versionConfig = static::getVersionConfig($version);

			if (isset($versionConfig['quality'])) {
				$quality = $versionConfig['quality'];
			} else {
				$quality = 100;
			}

			// Get valid source.
			$source = static::getValidSource($source);
			if (!$source) {
				throw new \Katu\Exceptions\ImageErrorException;
			}

			// See if there's already been a failure.
			$sourceHash = sha1(serialize($source));
			if (Tmp::get(['image', 'failure', $sourceHash])) {
				throw new \Katu\Exceptions\ImageErrorException;
			}

			@mkdir(dirname($destination), 0777, true);

			// Try a URL as a source.
			try {
				$source = \Katu\Cache\Url::get(new \Katu\Types\TUrl($source));
			} catch (\Exception $e) {
				// Nevermind.
			}

			try {
				$image = \Intervention\Image\ImageManagerStatic::make($source);
			} catch (\Exception $e) {
				// Save the failure info.
				Tmp::set(['image', 'failure', $sourceHash], (new \Katu\Utils\DateTime())->getDbDateTimeFormat());

				throw $e;
			}

			if (isset($versionConfig['filters'])) {
				foreach ((array) $versionConfig['filters'] as $filter) {

					if (isset($filter['filter'])) {

						switch ($filter['filter']) {
							case 'fit' :

								$image->fit($filter['width'], $filter['height'], function($constraint) {
									$constraint->aspectRatio();
								});

							break;
							case 'resize' :

								$image->resize($filter['width'], $filter['height'], function($constraint) {
									$constraint->aspectRatio();
								});

							break;
							case 'contrast' :

								$image->contrast($filter['level']);

							break;
							case 'sharpen' :

								$image->sharpen($filter['level']);

							break;
							case 'blur' :

								$image->blur($filter['level']);

							break;
							case 'insert' :

								$image->insert(
									(new \Katu\Utils\File($filter['source']))->getPath(),
									isset($filter['position']) ? $filter['position'] : null,
									isset($filter['x']) ? $filter['x'] : null,
									isset($filter['y']) ? $filter['y'] : null
								);

							break;
						}

					}

				}
			}

			$image->save($destination, $quality);

		}

		return true;
	}

	static function getVersionConfig($version) {
		try {
			return \Katu\Config::get('image', 'versions', $version);
		} catch (\Exception $e) {
			try {
				if (method_exists('\\App\\Extensions\\Image', 'getVersionConfig')) {
					return \App\Extensions\Image::getVersionConfig($version);
				}
				throw new \Exception;
			} catch (\Exception $e) {
				return false;
			}
		}
	}

	/* Colors *******************************************************************/

	static function getColor($color) {
		return \Katu\Types\TColor::getFromImageColor($color);
	}

	static function getColorAtCoords($path, \Katu\Types\TCoordsRectangle $coordsRectangle = null) {
		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], null, function($path, $coordsRectangle) {

			$createFunctionName = static::getImageCreateFunctionName($path);
			if (!$createFunctionName) {
				throw new \Exception("Invalid image type.");
			}

			$image = $createFunctionName($path);
			$pixel = imagecreatetruecolor(1, 1);

			if (!$coordsRectangle) {
				$coordsRectangle = new \Katu\Types\TCoordsRectangle(0, 0, imagesx($image), imagesy($image));
			}

			imagecopyresampled($pixel, $image, 0, 0, $coordsRectangle->xa, $coordsRectangle->ya, 1, 1, $coordsRectangle->xb - $coordsRectangle->xa, $coordsRectangle->yb - $coordsRectangle->ya);

			return static::getColor(imagecolorat($pixel, 0, 0));

		}, $path, $coordsRectangle);
	}

	static function getColors($path) {
		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], null, function($path) {

			$colors = [];

			$image = \Intervention\Image\ImageManagerStatic::make($path);

			for ($x = 0; $x < $image->width(); $x++) {
				for ($y = 0; $y < $image->height(); $y++) {
					$colors[] = $image->pickColor($x, $y, 'hex');
				}
			}

			return $colors;

		}, $path);
	}

	static function getColorSums($path) {
		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], null, function($path) {

			$colors = [];

			$image = \Intervention\Image\ImageManagerStatic::make($path);

			for ($x = 0; $x < $image->width(); $x++) {
				for ($y = 0; $y < $image->height(); $y++) {
					$color = $image->pickColor($x, $y, 'hex');
					if (!isset($colors[$color])) {
						$colors[$color] = 0;
					}
					$colors[$color]++;
				}
			}

			return $colors;

		}, $path);
	}

}
