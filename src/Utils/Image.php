<?php

namespace Katu\Utils;

class Image {

	const THUMBNAIL_DIR = 'image/thumbnails';

	static function getThumbnailFilename($uri, $size, $quality = 100) {
		return implode('_', array(sha1($uri), $size, $quality)) . '.jpg';
	}

	static function getThumbnailURL($uri, $size, $quality = 100) {
		static::makeThumbnail($uri, static::getThumbnailPath($uri, $size, $quality), $size, $quality);

		return \Katu\Utils\URL::joinPaths(\Katu\Utils\URL::getBase(), TMP_DIR, static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality));
	}

	static function getThumbnailPath($uri, $size, $quality = 100) {
		$thumbnailPath = \Katu\Utils\FS::joinPaths(TMP_PATH, static::THUMBNAIL_DIR, self::getThumbnailFilename($uri, $size, $quality));
		static::makeThumbnail($uri, $thumbnailPath, $size, $quality);

		return $thumbnailPath;
	}

	static function makeThumbnail($source, $destination, $size, $quality = 100) {
		if (!file_exists($destination)) {

			@mkdir(dirname($destination), 0777, TRUE);
			$image = \Intervention\Image\Image::make($source);
			$image->resize($size, $size, TRUE);
			$image->save($destination);

		}

		return TRUE;
	}

	static function getMIME($path) {
		$size = @getimagesize($path);
		if (!isset($size['mime'])) {
			return FALSE;
		}

		return $size['mime'];
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
		$rgb['r'] = ($color >> 16) & 0xFF;
		$rgb['g'] = ($color >> 8) & 0xFF;
		$rgb['b'] = $color & 0xFF;

		return $rgb;
	}

	static function getColorAtCoords($path, $coords) {
		imagecreatefrom
		$pixel = imagecreatetruecolor(1, 1);
		imagecopyresampled($pixel, $image, 0, 0, $coords[0], $coords[1], 1, 1, $coords[2] - $coords[0], $coords[3] - $coords[1]);
		return static::getColorRgb(imagecolorat($pixel, 0, 0));
	}

}
