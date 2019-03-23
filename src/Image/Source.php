<?php

namespace Katu\Image;

abstract class Source {

	protected $input;

	abstract public function getDir();
	abstract public function getExtension();
	abstract public function getUrl();

	public function __construct($input) {
		$this->input = $input;
	}

	static function createFromInput($input) {
		// Image source.
		if ($input instanceof static) {
			return $input;

		// File.
		} elseif ($input instanceof \Katu\Utils\File) {
			return new Sources\File($input);

		// URL.
		} elseif ($input instanceof \Katu\Types\TUrl) {
			return new Sources\Url($input);

		// File model.
		} elseif ($input instanceof \App\Models\File) {
			return new Sources\File($input->getFile());

		// File attachment.
		} elseif ($input instanceof \App\Models\FileAttachment) {
			return new Sources\File($input->getFile()->getFile());

		// Model.
		} elseif ($input instanceof \Katu\ModelBase) {
			$imageFile = $input->getImageFile();
			return $imageFile ? new Sources\File($imageFile->getFile()) : false;

		} else {

			// Try URL.
			if (is_string($input)) {

				try {
					return new Sources\Url(new \Katu\Types\TUrl($input));
				} catch (\Exception $e) {
					// Nevermind.
				}

				try {
					$file = new \Katu\Utils\File($input);
					if ($file->exists()) {
						return new Sources\Url($file->getUrl());
					}

					throw new \Exception;
				} catch (\Exception $e) {
					// Nevermind.
				}

			}

		}

		return false;
	}

	public function getInput() {
		return $this->input;
	}

	public function getUri() {
		return (string)$this->input;
	}

	public function getHash() {
		return sha1($this->getUri());
	}

}
