<?php

namespace Katu\Utils;

class Image {

	const THUMBNAIL_DIR = 'image/thumbnails';

	static function getValidSource($source) {
		if ($source instanceof \App\Models\File) {
			$source = $source->getPath();
		} elseif ($source instanceof \App\Models\FileAttachment) {
			$source = $source->getFile()->getPath();
		} elseif ($source instanceof \Katu\ReadOnlyModel) {
			$source = $source->getImagePath();
		}

		return $source;
	}

	static function getThumbnailFilename($uri, $size, $quality = 100, $options = []) {
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

		$fileNameSuffixes = [];
		foreach ($options as $key => $value) {
			$fileNameSuffixes[] = $key;
			$fileNameSuffixes[] = $value;
		}

		$fileNameSuffix = implode('_', $fileNameSuffixes);

		if (isset($pathinfo['extension'])) {
			$fileNameExtension = '.' . $pathinfo['extension'];
		}
		if (isset($options['extension'])) {
			$fileNameExtension = '.' . ltrim($options['extension'], '.');
		}

		return implode([
			implode('_', array_filter([
				$fileNameHash,
				$fileNameSuffix,
			])),
			$fileNameExtension,
		]);
	}

	static function getDirName() {
		return \Katu\Config::get('app', 'tmp', 'publicDir');
	}

	static function getDirPath() {
		$path = BASE_DIR . '/' . static::getDirName();

		// Check the writability of the folder.
		if (!is_writable($path)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Public tmp folder isn't writable.");
		}

		return realpath($path);
	}

	static function getThumbnailUrl($uri, $size, $quality = 100, $options = []) {
		$thumbnailPath = static::getThumbnailPath($uri, $size, $quality, $options);
		try {
			static::makeThumbnail($uri, $thumbnailPath, $size, $quality, $options);
		} catch (\Exception $e) {
			return false;
		}

		return \Katu\Utils\Url::joinPaths(\Katu\Utils\Url::getBase(), \Katu\Config::get('app', 'tmp', 'publicUrl'), static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality, $options));
	}

	static function getThumbnailPath($uri, $size, $quality = 100, $options = []) {
		$thumbnailPath = \Katu\Utils\FS::joinPaths(static::getDirPath(), static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality, $options));
		try {
			static::makeThumbnail($uri, $thumbnailPath, $size, $quality, $options);
		} catch (\Exception $e) {
			return false;
		}

		return $thumbnailPath;
	}

	static function makeThumbnail($source, $destination, $size, $quality = 100, $options = []) {
		if (!file_exists($destination)) {

			@mkdir(dirname($destination), 0777, true);

			$source = static::getValidSource($source);

			try {
				$image = \Intervention\Image\Image::make($source);
			} catch (\Exception $e) {
				error_log($e);

				throw new \Katu\Exceptions\ImageErrorException($e->getMessage());
			}

			if (isset($options['format']) && $options['format'] == 'square') {
				$image->grab($size, $size);
			} else {
				$image->resize($size, $size, true);
			}

			$image->save($destination);

		}

		return true;
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

	static function getColorRgb($color) {
		return \Katu\Types\TColorRgb::getFromImageColor($color);
	}

	static function getColorAtCoords($path, \Katu\Types\TCoordsRectangle $coordsRectangle) {
		$createFunctionName = static::getImageCreateFunctionName($path);
		if (!$createFunctionName) {
			throw new \Exception("Invalid image type.");
		}

		$image = $createFunctionName($path);
		$pixel = imagecreatetruecolor(1, 1);
		imagecopyresampled($pixel, $image, 0, 0, $coordsRectangle->xa, $coordsRectangle->ya, 1, 1, $coordsRectangle->xb - $coordsRectangle->xa, $coordsRectangle->yb - $coordsRectangle->ya);

		return static::getColorRgb(imagecolorat($pixel, 0, 0));
	}

}
