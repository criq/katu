<?php

namespace Katu\Models;

class File extends \Katu\Model {

	const TABLE = 'files';

	static function createFromUpload($creator, $upload) {
		if (!static::checkCrudParams($creator)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}
		if (!$upload || !($upload instanceof \Katu\Upload)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid upload.");
		}

		// Check source file.
		if (!file_exists($upload->path)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Source file doesn't exist.");
		}

		// Check the writability of files folder.
		if (!is_writable(static::getDirPath())) {
			throw new \Katu\Exceptions\ArgumentErrorException("File folder isn't writable.");
		}

		// Get a new file name.
		$path = static::copyUpload($upload, static::generatePath($upload->fileName));

		return static::insert([
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDatetimeFormat()),
			'creatorId'   => (int)    ($creator ? $creator->id : null),
			'path'        => (string) ($path),
			'name'        => (string) ($upload->fileName),
			'type'        => (string) ($upload->fileType),
			'size'        => (string) ($upload->fileSize),
		]);
	}

	static function checkCrudParams($creator) {
		if ($creator && !($creator instanceof \App\Models\Creator)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid file creator.", 'file');
		}

		return true;
	}

	public function delete() {
		foreach (\App\Models\FileAttachment::getBy([
			'fileId' => $this,
		]) as $fileAttachment) {
			$fileAttachment->delete();
		}

		@unlink($this->getPath());

		return parent::delete();
	}

	static function getDirName() {
		return \Katu\Config::get('app', 'files', 'dir');
	}

	static function getDirPath() {
		return realpath(BASE_DIR . '/' . static::getDirName());
	}

	static function generatePath($srcName = null) {
		while (true) {

			try {
				$subDirs = \Katu\Config::get('app', 'files', 'subDirs');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$subDirs = 3;
			}

			try {
				$fileNameLength = \Katu\Config::get('app', 'files', 'fileNameLength');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameLength = rand(32, 64);
			}

			try {
				$fileNameChars = \Katu\Config::get('app', 'files', 'fileNameChars');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			}

			$subDirNames = [];
			for ($i = 0; $i < $subDirs; $i++) {
				$subDirNames[] = \Katu\Utils\Random::getFromChars($fileNameChars, rand(1, 3));
			}

			$path = trim(implode('/', [
				implode('/', $subDirNames),
				\Katu\Utils\Random::getFromChars($fileNameChars, $fileNameLength),
			]), '/');

			if ($srcName) {
				$srcPathinfo = pathinfo($srcName);
				if (isset($srcPathinfo['extension'])) {
					$path .= '.' . $srcPathinfo['extension'];
				}
			}

			$dstPath = static::getDirPath() . '/' . $path;
			if (file_exists($dstPath)) {
				continue;
			}

			return $path;

		}
	}

	static function copyUpload($upload, $path) {
		$srcPath = $upload->path;
		$dstPath = static::getDirPath() . '/' . $path;
		$dstDirPath = dirname($dstPath);

		@mkdir($dstDirPath, 0777, true);

		if (!copy($srcPath, $dstPath)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Error occured during copying the upload.");
		}

		return $path;
	}

	public function attachTo($creator, $object) {
		return \App\Models\FileAttachment::make($creator, $object, $this);
	}

	public function getPath() {
		return static::getDirPath() . '/' . $this->path;
	}

	public function getThumbnailUrl($size = 640, $quality = 100, $options = []) {
		return \Katu\Utils\Image::getThumbnailUrl($this->getPath(), $size, $quality, $options);
	}

	public function getThumbnailPath($size = 640, $quality = 100, $options = []) {
		return \Katu\Utils\Image::getThumbnailPath($this->getPath(), $size, $quality, $options);
	}

}
