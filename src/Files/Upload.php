<?php

namespace Katu\Files;

class Upload
{
	const ERROR_OK       = 0;
	const ERROR_NO_FILE  = 1;
	const ERROR_SIZE     = 2;
	const ERROR_PROGRESS = 3;
	const ERROR_SERVER   = 4;

	public $error;
	public $fileName;
	public $fileSize;
	public $fileType;
	public $path;

	public function __construct(\Slim\Http\UploadedFile $upload)
	{
		$this->path = (string)$upload->file;
		$this->fileName = (string)$upload->getClientFilename();
		$this->fileType = (string)$upload->getClientMediaType();
		$this->fileSize = (int)$upload->getSize();
		$this->error = (int)$upload->getError();
	}

	public function getError() : int
	{
		return $this->error;
	}

	public function isInError() : bool
	{
		return (bool)$this->getErrorId();
	}

	public function getErrorMessage() : string
	{
		switch ($this->getError()) {
			// 0
			case UPLOAD_ERR_OK:
				return null;
				break;

			// 1
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
				break;

			// 2
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
				break;

			// 3
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded.';
				break;

			// 4
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded.';
				break;

			// 6
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder.';
				break;

			// 7
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk.';
				break;

			// 8
			case UPLOAD_ERR_EXTENSION:
				return 'A PHP extension stopped the file upload.';
				break;
		}
	}

	public function getErrorId() : int
	{
		switch ($this->getError()) {
			// 0
			case UPLOAD_ERR_OK:
				return static::ERROR_OK;
				break;

			// 1
			case UPLOAD_ERR_INI_SIZE:
				return static::ERROR_SIZE;
				break;

			// 2
			case UPLOAD_ERR_FORM_SIZE:
				return static::ERROR_SIZE;
				break;

			// 3
			case UPLOAD_ERR_PARTIAL:
				return static::ERROR_PROGRESS;
				break;

			// 4
			case UPLOAD_ERR_NO_FILE:
				return static::ERROR_NO_FILE;
				break;

			// 6
			case UPLOAD_ERR_NO_TMP_DIR:
				return static::ERROR_SERVER;
				break;

			// 7
			case UPLOAD_ERR_CANT_WRITE:
				return static::ERROR_SERVER;
				break;

			// 8
			case UPLOAD_ERR_EXTENSION:
				return static::ERROR_SERVER;
				break;
		}
	}

	public function getException() : ?\Throwable
	{
		if ($this->getErrorId()) {
			return new \Exception($this->getErrorMessage(), $this->getErrorId());
		}

		return null;
	}

	public function getFileName() : string
	{
		return $this->fileName;
	}

	public function getFileSize() : int
	{
		return $this->fileSize;
	}

	public function getFileType() : string
	{
		return $this->fileType;
	}

	public function isType(array $types) : bool
	{
		return in_array($this->fileType, $types);
	}

	public function isSupportedImage() : bool
	{
		return in_array($this->fileType, \Katu\Models\Presets\File::getSupportedImageTypes());
	}

	public function getPath() : string
	{
		return $this->path;
	}

	public function getFile() : \Katu\Files\File
	{
		return new \Katu\Files\File($this->path);
	}

	public function getHash() : string
	{
		return sha1(file_get_contents($this->path));
	}

	public static function getMaxSize() : \Katu\Types\TFileSize
	{
		return min(
			\Katu\Types\TFileSize::createFromINI(ini_get('upload_max_filesize')),
			\Katu\Types\TFileSize::createFromINI(ini_get('post_max_size')),
		);
	}
}
