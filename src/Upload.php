<?php

namespace Katu;

class Upload {

	const ERROR_OK       = 0;
	const ERROR_NO_FILE  = 1;
	const ERROR_SIZE     = 2;
	const ERROR_PROGRESS = 3;
	const ERROR_SERVER   = 4;

	public $fileName;
	public $fileType;
	public $fileSize;
	public $path;
	public $error;

	public function __construct($upload) {
		$this->fileName = (string) $upload['name'];
		$this->fileType = (string) $upload['type'];
		$this->fileSize = (int)    $upload['size'];
		$this->path     = (string) $upload['tmp_name'];
		$this->error    = (int)    $upload['error'];
	}

	static function get($key) {
		if (!isset($_FILES[$key])) {
			throw new \Exception("Upload missing.", self::ERROR_NO_FILE);
		}

		return new self($_FILES[$key]);
	}

	public function isInError() {
		return (bool) $this->getErrorID();
	}

	public function isType($types) {
		return in_array($this->fileType, (array) $types);
	}

	public function getErrorNumber() {
		return $this->error;
	}

	public function getErrorMessage() {
		switch ($this->getErrorNumber()) {

			// 0
			case UPLOAD_ERR_OK :
				return FALSE;
			break;

			// 1
			case UPLOAD_ERR_INI_SIZE :
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			break;

			// 2
			case UPLOAD_ERR_FORM_SIZE :
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			break;

			// 3
			case UPLOAD_ERR_PARTIAL :
				return 'The uploaded file was only partially uploaded.';
			break;

			// 4
			case UPLOAD_ERR_NO_FILE :
				return 'No file was uploaded.';
			break;

			// 6
			case UPLOAD_ERR_NO_TMP_DIR :
				return 'Missing a temporary folder.';
			break;

			// 7
			case UPLOAD_ERR_CANT_WRITE :
				return 'Failed to write file to disk.';
			break;

			// 8
			case UPLOAD_ERR_EXTENSION :
				return 'A PHP extension stopped the file upload.';
			break;

		}
	}

	public function getErrorID() {
		switch ($this->getErrorNumber()) {

			// 0
			case UPLOAD_ERR_OK :
				return static::ERROR_OK;
			break;

			// 1
			case UPLOAD_ERR_INI_SIZE :
				return static::ERROR_SIZE;
			break;

			// 2
			case UPLOAD_ERR_FORM_SIZE :
				return static::ERROR_SIZE;
			break;

			// 3
			case UPLOAD_ERR_PARTIAL :
				return static::ERROR_PROGRESS;
			break;

			// 4
			case UPLOAD_ERR_NO_FILE :
				return static::ERROR_NO_FILE;
			break;

			// 6
			case UPLOAD_ERR_NO_TMP_DIR :
				return static::ERROR_SERVER;
			break;

			// 7
			case UPLOAD_ERR_CANT_WRITE :
				return static::ERROR_SERVER;
			break;

			// 8
			case UPLOAD_ERR_EXTENSION :
				return static::ERROR_SERVER;
			break;

		}
	}

	public function getException() {
		return new \Exception($this->getErrorMessage(), $this->getErrorID());
	}

}
