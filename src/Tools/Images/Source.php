<?php

namespace Katu\Tools\Images;

abstract class Source
{
	protected $input;

	abstract public function getDir();
	abstract public function getExtension();
	abstract public function getURI();
	abstract public function getURL();

	public function __construct($input)
	{
		$this->input = $input;
	}

	public static function createFromInput($input)
	{
		// Image.
		if ($input instanceof Image) {
			return $input->getSource();

		// Image source.
		} elseif ($input instanceof static) {
			return $input;

		// File on filesystem.
		} elseif ($input instanceof \Katu\Files\File) {
			return new Sources\URL($input->getURL());

		// URL.
		} elseif ($input instanceof \Katu\Types\TURL) {
			return new Sources\URL($input);

		// File model.
		} elseif ($input instanceof \Katu\Models\Presets\File) {
			return new Sources\File($input);

		// File attachment.
		} elseif ($input instanceof \Katu\Models\Presets\FileAttachment) {
			return new Sources\File($input->getFile());

		// Model.
		} elseif ($input instanceof \Katu\Models\Base) {
			$imageFile = $input->getImageFile();
			return $imageFile ? new Sources\File($imageFile) : false;

		// String.
		} elseif (is_string($input)) {
			try {
				return new Sources\URL(new \Katu\Types\TURL($input));
			} catch (\Exception $e) {
				// Nevermind.
			}

			try {
				$file = new \Katu\Files\File($input);
				if ($file->exists()) {
					return new Sources\URL($file->geTURL());
				}

				throw new \Exception;
			} catch (\Exception $e) {
				// Nevermind.
			}
		}

		return false;
	}

	public function getInput()
	{
		return $this->input;
	}

	public function getHash()
	{
		return sha1($this->getUri());
	}
}
