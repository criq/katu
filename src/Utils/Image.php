<?php

namespace Katu\Utils;

use \Intervention\Image\Image as InterventionImage;

class Image {

	const THUMBNAIL_DIR = 'image/thumbnails';

	static function getThumbnailFilename($uri, $size, $quality = 100) {
		return implode('_', array(sha1($uri), $size, $quality)) . '.jpg';
	}

	static function getSquareThumbnailFilename($uri, $size, $quality = 100) {
		return implode('_', array(sha1($uri), $size, $quality, 'sq')) . '.jpg';
	}

	static function getThumbnailUrl($uri, $size, $quality = 100) {
		static::makeThumbnail($uri, static::getThumbnailPath($uri, $size, $quality), $size, $quality);

		return \Katu\Utils\Url::joinPaths(\Katu\Utils\Url::getBase(), TMP_DIR, static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality));
	}

	static function getSquareThumbnailUrl($uri, $size, $quality = 100) {
		static::makeSquareThumbnail($uri, static::getSquareThumbnailPath($uri, $size, $quality), $size, $quality);

		return \Katu\Utils\Url::joinPaths(\Katu\Utils\Url::getBase(), TMP_DIR, static::THUMBNAIL_DIR, self::getSquareThumbnailFilename($uri, $size, $quality));
	}

	static function getThumbnailPath($uri, $size, $quality = 100) {
		$thumbnailPath = \Katu\Utils\FS::joinPaths(TMP_PATH, static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality));
		static::makeThumbnail($uri, $thumbnailPath, $size, $quality);

		return $thumbnailPath;
	}

	static function getSquareThumbnailPath($uri, $size, $quality = 100) {
		$thumbnailPath = \Katu\Utils\FS::joinPaths(TMP_PATH, static::THUMBNAIL_DIR, self::getSquareThumbnailFilename($uri, $size, $quality));
		static::makeSquareThumbnail($uri, $thumbnailPath, $size, $quality);

		return $thumbnailPath;
	}

	static function makeThumbnail($source, $destination, $size, $quality = 100) {
		if (!file_exists($destination)) {

			@mkdir(dirname($destination), 0777, TRUE);
			$image = InterventionImage::make($source);
			$image->resize($size, $size, TRUE);
			$image->save($destination);

		}

		return TRUE;
	}

	static function makeSquareThumbnail($source, $destination, $size, $quality = 100) {
		if (!file_exists($destination)) {

			@mkdir(dirname($destination), 0777, TRUE);
			$image = InterventionImage::make($source);
			$image->fit($size, $size);
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
