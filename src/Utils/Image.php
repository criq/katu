<?php

namespace Katu\Utils;

class Image {

	static function getThumbnailURL($uri, $size, $quality = 100) {
		$thumbnailPath = self::getThumbnailPath($uri, $size, $quality);
		if (!file_exists($thumbnailPath)) {

			@mkdir(dirname($thumbnailPath), 0777, TRUE);
			$image = \Intervention\Image\Image::make($uri);
			$image->resize($size, $size, TRUE);
			$image->save($thumbnailPath);

		}

		return \Katu\Utils\URL::joinPaths(\Katu\Utils\URL::getBase(), TMP_DIR, 'image/thumbnails', self::getThumbnailFilename($uri, $size, $quality));
	}

	static function getThumbnailFilename($uri, $size, $quality = 100) {
		return implode('_', array(sha1($uri), $size, $quality)) . '.jpg';
	}

	static function getThumbnailPath($uri, $size, $quality = 100) {
		return \Katu\Utils\FS::joinPaths(TMP_PATH, 'image/thumbnails', self::getThumbnailFilename($uri, $size, $quality));
	}

}
