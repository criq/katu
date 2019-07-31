<?php

namespace Katu\Models\Presets;

use \Katu\Tools\Random\Generator;

class File extends \Katu\Models\Model {

	const TABLE = 'files';

	static function create($creator, $path, $fileName, $fileType, $fileSize) {
		return static::insert([
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'   =>          ($creator ? $creator->getId() : null),
			'path'        => (string) ($path),
			'name'        => (string) ($fileName),
			'type'        => (string) ($fileType),
			'size'        => (string) ($fileSize),
		]);
	}

	static function createFromFile(\Katu\Models\User $creator = null, \Katu\Files\File $file) {
		if (!$file->exists()) {
			throw new \Katu\Exceptions\InputErrorException("Invalid upload.");
		}

		// Check the writability of files folder.
		if (!is_writable(static::getDirPath())) {
			throw new \Katu\Exceptions\InputErrorException("File folder isn't writable.");
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$fileType = finfo_file($finfo, $file);
		finfo_close($finfo);

		$fileSize = filesize($file);

		// Get a new file name.
		$path = new \Katu\Files\File(static::generatePath($file));
		$file->copy(new \Katu\Files\File(FILE_PATH, $path));

		return static::create($creator, $path, $file->getBasename(), $fileType, $fileSize);
	}

	static function createFromUpload(\Katu\Models\User $creator = null, $upload) {
		if (!$upload || !($upload instanceof \Katu\Files\Upload)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid upload.");
		}

		// Check source file.
		if (!file_exists($upload->path)) {
			throw new \Katu\Exceptions\InputErrorException("Source file doesn't exist.");
		}

		// Check the writability of files folder.
		if (!is_writable(static::getDirPath())) {
			throw new \Katu\Exceptions\InputErrorException("File folder isn't writable.");
		}

		// Get a new file name.
		$path = new \Katu\Files\File(static::generatePath($upload->fileName));
		(new \Katu\Files\File($upload->path))->copy(new \Katu\Files\File(FILE_PATH, $path));

		return static::create($creator, $path, $upload->fileName, $upload->fileType, $upload->fileSize);
	}

	static function createFromUrl(\Katu\Models\User $creator = null, $url) {
		$url = new \Katu\Types\TURL($url);

		$temporaryFile = \Katu\Files\File::createTemporaryFromUrl($url);
		if (!$temporaryFile) {
			throw new \Katu\Exceptions\InputErrorException("Can't create file from URL $url.");
		}

		$file = static::createFromFile($creator, $temporaryFile);
		$temporaryFile->delete();

		$file->update('name', pathinfo($url->getParts()['path'])['basename']);
		$file->save();

		return $file;
	}

	public function delete() {
		foreach (\App\Models\FileAttachment::getBy([
			'fileId' => $this->getId(),
		]) as $fileAttachment) {
			$fileAttachment->delete();
		}

		@unlink($this->getPath());

		return parent::delete();
	}

	public function getFile() {
		return new \Katu\Files\File($this->getPath());
	}

	static function getDirName() {
		return \Katu\Config\Config::get('app', 'files', 'dir');
	}

	static function getDirPath() {
		return realpath(BASE_DIR . '/' . static::getDirName());
	}

	public function getName() {
		return $this->name;
	}

	static function generatePath($srcName = null) {
		while (true) {

			try {
				$subDirs = \Katu\Config\Config::get('app', 'files', 'subDirs');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$subDirs = 3;
			}

			try {
				$fileNameLength = \Katu\Config\Config::get('app', 'files', 'fileNameLength');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameLength = 32;
			}

			try {
				$fileNameChars = \Katu\Config\Config::get('app', 'files', 'fileNameChars');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameChars = 'abcdefghjkmnpqrstuvwxyz123456789';
			}

			$subDirNames = [];
			for ($i = 0; $i < $subDirs; $i++) {
				$subDirNames[] = Generator::getFromChars($fileNameChars, 1);
			}

			$path = trim(implode('/', [
				implode('/', $subDirNames),
				Generator::getFromChars($fileNameChars, $fileNameLength),
			]), '/');

			if ($srcName) {
				$srcPathinfo = pathinfo($srcName);
				if (isset($srcPathinfo['extension'])) {
					$path .= '.' . mb_strtolower($srcPathinfo['extension']);
				}
			}

			$dstPath = static::getDirPath() . '/' . $path;
			if (file_exists($dstPath)) {
				continue;
			}

			return $path;

		}
	}

	public function copy($destination) {
		$this->getFile()->copy($destination);

		return true;
	}

	public function move(\Katu\Files\File $destination) {
		$this->getFile()->move($destination);

		$path = preg_replace('/^' . preg_quote(FILE_PATH, '/') . '/', null, $destination);
		$path = ltrim($path, '/');

		$this->update('path', $path);
		$this->save();

		return true;
	}

	public function attachTo($creator, $object) {
		return \App\Models\FileAttachment::make($creator, $object, $this);
	}

	public function getSecret() {
		if (!$this->secret) {
			$this->update('secret', Generator::generateString($this->getTable()->getColumn('secret')->getProperties()->length, Generator::ALNUM));
			$this->save();
		}

		return $this->secret;
	}

	public function getPath() {
		return static::getDirPath() . '/' . $this->path;
	}

	static function getSupportedImageTypes() {
		return [
			'image/jpeg',
			'image/png',
			'image/gif',
		];
	}

	public function isSupportedImage() {
		return in_array($this->type, static::getSupportedImageTypes());
	}

}
