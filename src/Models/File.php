<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

class File extends \Katu\Model
{
	const TABLE = 'files';

	public static function create($creator, $path, $fileName, $fileType, $fileSize)
	{
		return static::insert([
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'   =>          ($creator ? $creator->getId() : null),
			'path'        => (string) ($path),
			'name'        => (string) ($fileName),
			'type'        => (string) ($fileType),
			'size'        => (string) ($fileSize),
		]);
	}

	public static function createFromFile(\Katu\Models\User $creator = null, \Katu\Utils\File $file)
	{
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
		$path = new \Katu\Utils\File(static::generatePath($file));
		$file->copy(new \Katu\Utils\File(FILE_PATH, $path));

		return static::create($creator, $path, $file->getBasename(), $fileType, $fileSize);
	}

	public static function createFromUpload(\Katu\Models\User $creator = null, $upload)
	{
		if (!$upload || !($upload instanceof \Katu\Upload)) {
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
		$path = new \Katu\Utils\File(static::generatePath($upload->fileName));
		(new \Katu\Utils\File($upload->path))->copy(new \Katu\Utils\File(FILE_PATH, $path));

		return static::create($creator, $path, $upload->fileName, $upload->fileType, $upload->fileSize);
	}

	public static function createFromUrl(\Katu\Models\User $creator = null, $url)
	{
		$url = new \Katu\Types\TUrl($url);

		$temporaryFile = \Katu\Utils\File::createTemporaryFromUrl($url);
		if (!$temporaryFile) {
			throw new \Katu\Exceptions\InputErrorException("Can't create file from URL $url.");
		}

		$file = static::createFromFile($creator, $temporaryFile);
		$temporaryFile->delete();

		$file->update('name', pathinfo($url->getParts()['path'])['basename']);
		$file->save();

		return $file;
	}

	public function delete()
	{
		foreach (\App\Models\FileAttachment::getBy([
			'fileId' => $this->getId(),
		]) as $fileAttachment) {
			$fileAttachment->delete();
		}

		@unlink($this->getPath());

		return parent::delete();
	}

	public function getFile()
	{
		return new \Katu\Utils\File($this->getPath());
	}

	public static function getDirName()
	{
		return \Katu\Config::get('app', 'files', 'dir');
	}

	public static function getDirPath()
	{
		return realpath(BASE_DIR . '/' . static::getDirName());
	}

	public function getName()
	{
		return $this->name;
	}

	public function getPathinfo()
	{
		return pathinfo($this->name);
	}

	public static function generatePath($srcName = null)
	{
		while (true) {
			try {
				$subDirs = \Katu\Config::get('app', 'files', 'subDirs');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$subDirs = 3;
			}

			try {
				$fileNameLength = \Katu\Config::get('app', 'files', 'fileNameLength');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameLength = 32;
			}

			try {
				$fileNameChars = \Katu\Config::get('app', 'files', 'fileNameChars');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameChars = 'abcdefghjkmnpqrstuvwxyz123456789';
			}

			$subDirNames = [];
			for ($i = 0; $i < $subDirs; $i++) {
				$subDirNames[] = \Katu\Utils\Random::getFromChars($fileNameChars, 1);
			}

			$path = trim(implode('/', [
				implode('/', $subDirNames),
				\Katu\Utils\Random::getFromChars($fileNameChars, $fileNameLength),
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

	public function copy($destination)
	{
		$this->getFile()->copy($destination);

		return true;
	}

	public function move(\Katu\Utils\File $destination)
	{
		$this->getFile()->move($destination);

		$path = preg_replace('/^' . preg_quote(FILE_PATH, '/') . '/', null, $destination);
		$path = ltrim($path, '/');

		$this->update('path', $path);
		$this->save();

		return true;
	}

	public function attachTo($creator, $object)
	{
		return \App\Models\FileAttachment::make($creator, $object, $this);
	}

	public function getPath()
	{
		return static::getDirPath() . '/' . $this->path;
	}

	public static function getSupportedImageTypes()
	{
		return [
			'image/gif',
			'image/jpeg',
			'image/png',
			'image/webp',
		];
	}

	public function isSupportedImage()
	{
		return in_array($this->type, static::getSupportedImageTypes());
	}

	public function getThumbnailUrl()
	{
		return \Katu\Utils\Image::getVersionUrl($this, 'thumbnail');
	}

	public function getSquareThumbnailUrl()
	{
		return \Katu\Utils\Image::getVersionUrl($this, 'squareThumbnail');
	}

	public function getThumbnailFile()
	{
		return \Katu\Utils\Image::getVersionFile($this, 'thumbnail');
	}

	public function getSquareThumbnailFile()
	{
		return \Katu\Utils\Image::getVersionFile($this, 'squareThumbnail');
	}

	public static function normalizePaths()
	{
		@set_time_limit(3600);

		\Katu\Utils\Cache::clearMemory();

		$filesDir = new \Katu\Utils\File(FILE_PATH);
		$normalizedDir = new \Katu\Utils\File(BASE_DIR, FILE_DIR . '-normalized');

		if (!$normalizedDir->exists() && !$normalizedDir->makeDir()) {
			throw new \Katu\Exceptions\Exception("Can't create normalized files dir.");
		}

		try {
			if (!$normalizedDir->chmod(0777)) {
				throw new \Katu\Exceptions\Exception("Can't change permissions on normalized files dir.");
			}
		} catch (\Exception $e) {
			// Nevermind.
		}

		if (!$normalizedDir->isWritable()) {
			throw new \Katu\Exceptions\Exception("Normalized dir isn't writable.");
		}

		$gitignoreOriginal = new \Katu\Utils\File($filesDir, '.gitignore');
		$gitignoreTarget = new \Katu\Utils\File($normalizedDir, '.gitignore');
		if (!$gitignoreOriginal->copy($gitignoreTarget)) {
			throw new \Katu\Exceptions\Exception("Can't copy .gitignore.");
		}

		try {
			$sql = " ALTER TABLE " . static::getTable() . " ADD `isNormalized` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '0' ";
			$res = File::getPdo()->createQuery($sql)->getResult();
		} catch (\Exception $e) {
			// Nevermind.
		}

		try {
			$sql = " ALTER TABLE " . static::getTable() . " ADD `preNormalizedPath` TEXT  NULL  AFTER `isNormalized` ";
			$res = File::getPdo()->createQuery($sql)->getResult();
		} catch (\Exception $e) {
			// Nevermind.
		}

		$sql = SX::select()
			->from(static::getTable())
			->where(SX::eq(static::getColumn('isNormalized'), 0))
			->setPage(SX::page(1, 500))
			;

		$files = static::getBySql($sql);
		if ($files->getTotal()) {
			foreach ($files as $file) {
				$file->update('preNormalizedPath', $file->path);
				$file->save();

				$destination = new \Katu\Utils\File($normalizedDir, static::generatePath($file->path));

				try {
					$file->copy($destination);
					if ($destination->exists()) {
						$path = preg_replace('/^' . preg_quote($normalizedDir, '/') . '/', null, $destination);
						$path = ltrim($path, '/');

						$file->update('isNormalized', 1);
						$file->update('path', $path);
						$file->save();
					}
				} catch (\Katu\Exceptions\Exception $e) {
					if ($e->getAbbr() == 'sourceFileUnavailable') {
						var_dump($file->path);
					} else {
						var_dump($file);
						// var_dump($e); die;
					}
				}
			}
		}

		echo "fin.";
	}
}
