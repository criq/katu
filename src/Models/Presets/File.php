<?php

namespace Katu\Models\Presets;

use Katu\Tools\Random\Generator;
use Katu\Types\TFileSize;

abstract class File extends \Katu\Models\Model
{
	const DEFAULT_DIR = "files";
	const TABLE = "files";

	public $name;
	public $path;
	public $secret;
	public $size;
	public $type;

	public static function create(\Katu\Models\Presets\User $user = null, string $path, string $fileName, string $fileType, int $fileSize): File
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"creatorId" => $user ? $user->getId() : null,
			"path" => $path,
			"name" => $fileName,
			"type" => $fileType,
			"size" => $fileSize,
		]);
	}

	public static function createFromFile(\Katu\Models\Presets\User $user = null, \Katu\Files\File $file): File
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
		$file->copy(new \Katu\Files\File(\App\App::getFileDir(), $path));

		return static::create($user, $path, $file->getBasename(), $fileType, $fileSize);
	}

	public static function createFromUpload(\Katu\Models\Presets\User $user = null, \Katu\Files\Upload $upload): File
	{
		if (!$upload) {
			throw new \Katu\Exceptions\InputErrorException("Invalid upload.");
		}

		// Check source file.
		if (!$upload->getStream()->isReadable()) {
			throw new \Katu\Exceptions\InputErrorException("Can't read source stream.");
		}

		// Check the writability of files folder.
		if (!static::getDir()->isWritable()) {
			throw new \Katu\Exceptions\InputErrorException("File folder isn't writable.");
		}

		// Get a new file name.
		$path = new \Katu\Files\File(static::generatePath($upload->getFileName()));

		// Copy into permanent file.
		(new \Katu\Files\File(\App\App::getFileDir(), $path))->set($upload->getStream()->getContents());

		return static::create($user, $path, $upload->getFileName(), $upload->getFileType(), $upload->getFileSize()->getInB()->getAmount());
	}

	public static function createFromURL(\Katu\Models\Presets\User $user = null, $url): File
	{
		$url = new \Katu\Types\TURL($url);

		$temporaryFile = \Katu\Files\File::createTemporaryFromURL($url);
		if (!$temporaryFile) {
			throw new \Katu\Exceptions\InputErrorException("Can't create file from URL {$url}.");
		}

		$file = static::createFromFile($user, $temporaryFile);
		$temporaryFile->delete();

		$file->name = pathinfo($url->getParts()["path"])["basename"];
		$file->save();

		return $file;
	}

	public function delete(): bool
	{
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		foreach ($fileAttachmentClass::getBy([
			"fileId" => $this->getId(),
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

	public function getFile(): ?\Katu\Files\File
	{
		return new \Katu\Files\File($this->getPath());
	}

	public static function getDirName(): string
	{
		try {
			return \Katu\Config\Config::get("app", "files", "dir");
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			return static::DEFAULT_DIR;
		}
	}

	public static function getDir(): \Katu\Files\File
	{
		return new \Katu\Files\File(\App\App::getBaseDir(), static::getDirName());
	}

	public function getName(): string
	{
		return $this->name;
	}

	public static function generatePath($srcName = null)
	{
		while (true) {
			try {
				$subDirs = \Katu\Config\Config::get("app", "files", "subDirs");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$subDirs = 3;
			}

			try {
				$fileNameLength = \Katu\Config\Config::get("app", "files", "fileNameLength");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameLength = 32;
			}

			try {
				$fileNameChars = \Katu\Config\Config::get("app", "files", "fileNameChars");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$fileNameChars = "abcdefghjkmnpqrstuvwxyz123456789";
			}

			$subDirNames = [];
			for ($i = 0; $i < $subDirs; $i++) {
				$subDirNames[] = Generator::getFromChars($fileNameChars, 1);
			}

			$path = trim(implode("/", [
				implode("/", $subDirNames),
				Generator::getFromChars($fileNameChars, $fileNameLength),
			]), "/");

			if ($srcName) {
				$srcPathinfo = pathinfo($srcName);
				if (isset($srcPathinfo["extension"])) {
					$path .= "." . mb_strtolower($srcPathinfo["extension"]);
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

		$path = preg_replace("/^" . preg_quote(\App\App::getFileDir(), "/") . "/", "", $destination);
		$path = ltrim($path, "/");

		$this->path = $path;
		$this->save();

		return true;
	}

	public function attachTo(\Katu\Models\Presets\User $user = null, \Katu\Models\Model $object)
	{
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		return $fileAttachmentClass::make($user, $object, $this);
	}

	public function getSecret(): string
	{
		if (!$this->secret) {
			$this->secret = Generator::generateString($this->getTable()->getColumn(new \Katu\PDO\Name("secret"))->getDescription()->length, Generator::ALNUM);
			$this->save();
		}

		return $this->secret;
	}

	public function getPath(): \Katu\Files\File
	{
		return new \Katu\Files\File(static::getDir(), $this->path);
	}

	public function getIsSupportedImage(): bool
	{
		return $this->getFile()->getIsSupportedImage();
	}

	public function getImage(): \Katu\Tools\Images\Image
	{
		return new \Katu\Tools\Images\Image($this);
	}

	public function getSize(): TFileSize
	{
		return new TFileSize($this->size);
	}
}
