<?php

namespace Katu\Image;

abstract class Source {

	protected $input;

	abstract public function getDir();
	abstract public function getExtension();
	abstract public function getUri();
	abstract public function geTURL();

	public function __construct($input) {
		$this->input = $input;
	}

	static function createFromInput($input) {
		// Image.
		if ($input instanceof \Katu\Image) {
			return $input->getSource();

		// Image source.
		} elseif ($input instanceof static) {
			return $input;

		// File on filesystem.
		} elseif ($input instanceof \Katu\Utils\File) {
			return new Sources\Url($input->geTURL());

		// URL.
		} elseif ($input instanceof \Katu\Types\TURL) {
			return new Sources\Url($input);

		// File model.
		} elseif ($input instanceof \App\Models\File) {
			return new Sources\File($input);

		// File attachment.
		} elseif ($input instanceof \App\Models\FileAttachment) {
			return new Sources\File($input->getFile());

		// Model.
		} elseif ($input instanceof \Katu\ModelBase) {
			$imageFile = $input->getImageFile();
			return $imageFile ? new Sources\File($imageFile) : false;

		// String.
		} elseif (is_string($input)) {

			try {
				return new Sources\Url(new \Katu\Types\TURL($input));
			} catch (\Exception $e) {
				// Nevermind.
			}

			try {
				$file = new \Katu\Utils\File($input);
				if ($file->exists()) {
					return new Sources\Url($file->geTURL());
				}

				throw new \Exception;
			} catch (\Exception $e) {
				// Nevermind.
			}

		}

		return false;
	}

	public function getInput() {
		return $this->input;
	}

	public function getHash() {
		return sha1($this->getUri());
	}

}
