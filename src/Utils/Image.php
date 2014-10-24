<?php

namespace Katu\Utils;

class Image {

	const THUMBNAIL_DIR = 'image/thumbnails';

	static function getThumbnailFilename($uri, $size, $quality = 100, $options = array()) {
		if ($uri instanceof \App\Models\File) {
			$uri = $uri->getPath();
		} elseif ($uri instanceof \App\Models\FileAttachment) {
			$uri = $uri->getFile()->getPath();
		}

		$suffixes = array();
		foreach ($options as $key => $value) {
			$suffixes[] = $key;
			$suffixes[] = $value;
		}

		return implode('_', array_filter(array_merge(array(sha1($uri), $size, $quality), $suffixes))) . '.jpg';
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

	static function getThumbnailUrl($uri, $size, $quality = 100, $options = array()) {
		static::makeThumbnail($uri, static::getThumbnailPath($uri, $size, $quality, $options), $size, $quality, $options);

		return \Katu\Utils\Url::joinPaths(\Katu\Utils\Url::getBase(), \Katu\Config::get('app', 'tmp', 'publicUrl'), static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality, $options));
	}

	static function getThumbnailPath($uri, $size, $quality = 100, $options = array()) {
		$thumbnailPath = \Katu\Utils\FS::joinPaths(static::getDirPath(), static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality, $options));
		static::makeThumbnail($uri, $thumbnailPath, $size, $quality, $options);

		return $thumbnailPath;
	}

	static function makeThumbnail($source, $destination, $size, $quality = 100, $options = array()) {
		if (!file_exists($destination)) {

			@mkdir(dirname($destination), 0777, TRUE);

			if ($source instanceof \App\Models\File) {
				$source = $source->getPath();
			} elseif ($source instanceof \App\Models\FileAttachment) {
				$source = $source->getFile()->getPath();
			}

			$image = \Intervention\Image\Image::make($source);

			if (isset($options['format']) && $options['format'] == 'square') {
				$image->grab($size, $size);
			} else {
				$image->resize($size, $size, TRUE);
			}

			$image->save($destination);

		}

		return TRUE;
	}

	static function getMime($path) {
		$size = @getimagesize($path);
		if (!isset($size['mime'])) {
			return FALSE;
		}

		return $size['mime'];
	}

	static function getType($path) {
		$mime = static::getMime($path);
		if (strpos($mime, 'image/') !== 0) {
			return FALSE;
		}

		list($image, $type) = explode('/', $mime);

		return $type;
	}

	static function getImageCreateFunctionName($path) {
		$type = static::getType($path);
		switch ($type) {
			case 'jpeg' : return 'imagecreatefromjpeg'; break;
			case 'gif' :  return 'imagecreatefromgif';  break;
			case 'png' :  return 'imagecreatefrompng';  break;
		}

		return FALSE;
	}

	static function getSize($path) {
		$size = @getimagesize($path);
		if (!$size) {
			return FALSE;
		}

		return new \Katu\Types\TImageSize($size[0], $size[1]);
	}

	static function getWidth($path) {
		$size = self::getSize($path);
		if ($size) {
			return $size->x;
		}

		return FALSE;
	}

	static function getHeight($path) {
		$size = self::getSize($path);
		if ($size) {
			return $size->y;
		}

		return FALSE;
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
