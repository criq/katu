<?php

namespace Katu\Files;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadCollection extends \ArrayObject
{
	public static function createFromInput($input): UploadCollection
	{
		if ($input instanceof UploadedFileInterface) {
			$array = [
				new Upload($input),
			];
		}

		if (is_array($input)) {
			$array = array_map(function ($uploadedFile) {
				if ($uploadedFile instanceof UploadedFileInterface) {
					return new Upload($uploadedFile);
				}
			}, $input);
		}

		return new static(array_values(array_filter($array ?? [])));
	}

	/**
	 * @deprecated
	 */
	public static function createFromRequest(ServerRequestInterface $request, string $key) : ?UploadCollection
	{
		$uploads = new static;

		$uploadedFiles = $request->getUploadedFiles();
		if (!isset($uploadedFiles[$key])) {
			return null;
		}

		if (($uploadedFiles[$key] ?? null) instanceof UploadedFileInterface) {
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

	public function filterWithoutError(): UploadCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (Upload $upload) {
			return !$upload->isInError();
		})));
	}

	public function getFirst(): ?Upload
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getFiles(): FileCollection
	{
		return new FileCollection(array_map(function (Upload $upload) {
			$file = File::createTemporaryWithFileName($upload->getFileName());
			$file->set($upload->getStream()->getContents());

			return $file;
		}, $this->filterWithoutError()->getArrayCopy()));
	}
}
