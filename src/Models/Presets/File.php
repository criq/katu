<?php

namespace Katu\Models\Presets;

use Katu\Tools\Random\Generator;

class File extends \Katu\Models\Model
{
	const DEFAULT_DIR = 'files';
	const TABLE = 'files';

	public static function create(\Katu\Models\Presets\User $creator = null, string $path, string $fileName, string $fileType, int $fileSize) : File
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\Calendar\Time,
			'creatorId' => $creator ? $creator->getId() : null,
			'path' => $path,
			'name' => $fileName,
			'type' => $fileType,
			'size' => $fileSize,
		]);
	}

	public static function createFromFile(\Katu\Models\Presets\User $creator = null, \Katu\Files\File $file) : File
	{
		if (!$file->exists()) {
			throw new \Katu\Exceptions\InputErrorException("Invalid upload.");
		}

		// Check the writability of files folder.
		if (!static::getDir()->isWritable()) {
			throw new \Katu\Exceptions\InputErrorException("File folder isn't writable.");
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$fileType = finfo_file($finfo, $file);
		finfo_close($finfo);

		$fileSize = filesize($file);

		// Get a new file name.
		$path = new \Katu\Files\File(static::generatePath($file));
		$file->copy(new \Katu\Files\File(\Katu\App::getFileDir(), $path));

		return static::create($creator, $path, $file->getBasename(), $fileType, $fileSize);
	}

	public static function createFromUpload(\Katu\Models\Presets\User $creator = null, \Katu\Files\Upload $upload) : File
	{
		if (!$upload) {
			throw new \Katu\Exceptions\InputErrorException("Invalid upload.");
		}

		// Check source file.
		if (!file_exists($upload->path)) {
			throw new \Katu\Exceptions\InputErrorException("Source file doesn't exist.");
		}

		// Check the writability of files folder.
		if (!static::getDir()->isWritable()) {
			throw new \Katu\Exceptions\InputErrorException("File folder isn't writable.");
		}

		// Get a new file name.
		$path = new \Katu\Files\File(static::generatePath($upload->fileName));
		(new \Katu\Files\File($upload->path))->copy(new \Katu\Files\File(\Katu\App::getFileDir(), $path));

		return static::create($creator, $path, $upload->fileName, $upload->fileType, $upload->fileSize);
	}

	public static function createFromURL(\Katu\Models\Presets\User $creator = null, $url) : File
	{
		$url = new \Katu\Types\TURL($url);

		$temporaryFile = \Katu\Files\File::createTemporaryFromURL($url);
		if (!$temporaryFile) {
			throw new \Katu\Exceptions\InputErrorException("Can't create file from URL $url.");
		}

		$file = static::createFromFile($creator, $temporaryFile);
		$temporaryFile->delete();

		$file->update('name', pathinfo($url->getParts()['path'])['basename']);
		$file->save();

		return $file;
	}

	public function delete(): bool
	{
		foreach (\Katu\Models\Presets\FileAttachment::getBy([
			'fileId' => $this->getId(),
		]) as $fileAttachment) {
			$fileAttachment->delete();
		}

		try {
			unlink($this->getPath());
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return parent::delete();
	}

	public function getFile() : ?\Katu\Files\File
	{
		return new \Katu\Files\File($this->getPath());
	}

	public static function getDirName() : string
	{
		try {
			return \Katu\Config\Config::get('app', 'files', 'dir');
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return static::DEFAULT_DIR;
		}
	}

	public static function getDir() : \Katu\Files\File
	{
		return new \Katu\Files\File(\Katu\App::getBaseDir(), static::getDirName());
	}

	public function getName() : string
	{
		return $this->name;
	}

	public static function generatePath($srcName = null)
	{
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

			$dstFile = new \Katu\Files\File(static::getDir(), $path);
			if ($dstFile->exists()) {
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

	public function move(\Katu\Files\File $destination)
	{
		$this->getFile()->move($destination);

		$path = preg_replace('/^' . preg_quote(\Katu\App::getFileDir(), '/') . '/', null, $destination);
		$path = ltrim($path, '/');

		$this->update('path', $path);
		$this->save();

		return true;
	}

	public function attachTo(\Katu\Models\Presets\User $creator = null, \Katu\Models\Model $object)
	{
		return \Katu\Models\Presets\FileAttachment::make($creator, $object, $this);
	}

	public function getSecret()
	{
		if (!$this->secret) {
			$this->update('secret', Generator::generateString($this->getTable()->getColumn('secret')->getDescription()->length, Generator::ALNUM));
			$this->save();
		}

		return $this->secret;
	}

	public function getPath()
	{
		return new \Katu\Files\File(static::getDir(), $this->path);
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

	public function getSize()
	{
		return new \Katu\Types\TFileSize($this->size);
	}
}
