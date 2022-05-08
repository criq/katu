<?php

namespace Katu\Files;

class UploadCollection extends \ArrayObject
{
	public static function createFromRequest(\Slim\Http\Request $request, string $key) : ?UploadCollection
	{
		$uploads = new static;

		$uploadedFiles = $request->getUploadedFiles();
		if (!isset($uploadedFiles[$key])) {
			return null;
		}

		if (($uploadedFiles[$key] ?? null) instanceof \Slim\Http\UploadedFile) {
			$upload = new Upload($uploadedFiles[$key]);
			if (($upload->error ?? null) === UPLOAD_ERR_NO_FILE) {
				return null;
			}

			$uploads[] = $upload;
		}

		if (is_array($uploadedFiles[$key] ?? null)) {
			foreach ($uploadedFiles[$key] as $uploadedFile) {
				$uploads[] = new Upload($uploadedFile);
			}
			if (($uploads[0]->error ?? null) === UPLOAD_ERR_NO_FILE) {
				return null;
			}
		}

		return $uploads;
	}
}
