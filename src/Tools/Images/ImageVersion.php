<?php

namespace Katu\Tools\Images;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;
use Psr\Http\Message\ServerRequestInterface;

class ImageVersion implements RestResponseInterface
{
	protected $image;
	protected $version;

	public function __construct(Image $image, Version $version)
	{
		$this->setImage($image);
		$this->setVersion($version);
	}

	public function __toString(): string
	{
		return (string)$this->getURL();
	}

	public function setImage(Image $image): ImageVersion
	{
		$this->image = $image;

		return $this;
	}

	public function getImage(): Image
	{
		return $this->image;
	}

	public function setVersion(Version $version): ImageVersion
	{
		$this->version = $version;

		return $this;
	}

	public function getVersion(): Version
	{
		return $this->version;
	}

	public function getURL(): ?TURL
	{
		try {
			return \Katu\Tools\Routing\URL::getFor("images.getVersion", [
				"imagePackage" => $this->getImage()->getPackage(),
				"versionCode" => $this->getVersion()->getTitle(),
				"extension" => $this->getVersion()->getExtension(),
			]);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getExtension(): string
	{
		$versionExtension = $this->getVersion()->getExtension();
		if ($versionExtension) {
			return $versionExtension;
		}

		$source = $this->getImage()->getSource();
		if (!$source) {
			return "";
		}

		return $source->getExtension();
	}

	public function getFile(): ?\Katu\Files\File
	{
		$source = $this->getImage()->getSource();
		if (!$source) {
			return null;
		}

		try {
			$pathSegments = [];

			$hash = $source->getHash();
			if (!$hash) {
				return null;
			}

			$pathSegments[] = substr($hash, 0, 2);
			$pathSegments[] = substr($hash, 2, 2);
			$pathSegments[] = substr($hash, 4, 2);
			$pathSegments[] = "{$hash}.{$this->getExtension()}";

			return new \Katu\Files\File($this->getVersion()->getDir(), implode("/", $pathSegments));
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return null;
		}
	}

	public function getIsUsable(): bool
	{
		try {
			$file = $this->getFile();
			if (!$file) {
				return false;
			}

			return $file->exists();
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getVersionImage(): ?Image
	{
		$file = $this->getFile();
		if (!$file) {
			return null;
		}

		try {
			if (!$file->exists()) {
				$interventionImage = $this->getImage()->getInterventionImage();
				if (!$interventionImage) {
					return null;
				}

				foreach ($this->getVersion()->getFilters() as $filter) {
					$filter->apply($interventionImage);
				}

				$file->getDir()->makeDir();
				$file->getDir()->chmod(0777);
				$file->touch();

				$interventionImage->save($file, $this->getVersion()->getQuality());
			}

			if (!$file->exists()) {
				return null;
			}

			return new Image($file);
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __METHOD__))->error($e, [
				"file" => serialize($file),
			]);

			return null;
		}
	}

	public function getMime(): ?string
	{
		try {
			if (!$this->getVersionImage()) {
				return null;
			}

			$file = $this->getFile();
			if (!$file || !$file->exists()) {
				return null;
			}

			return $file->getMime();
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __FUNCTION__))->error($e);

			return null;
		}
	}

	public function getEmbedSrc(): ?string
	{
		try {
			$this->getImage();

			if (!$this->getVersionImage()) {
				return null;
			}

			$file = $this->getFile();
			if (!$file || !$file->exists()) {
				return null;
			}

			$mime = $file->getMime();
			$base64 = @base64_encode($file->get());

			if ($mime && $base64) {
				return "data:{$mime};base64,{$base64}";
			}

			return null;
		} catch (\Throwable $e) {
			return null;
		}
	}

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		$file = $this->getFile();
		$versionImage = $this->getVersionImage();

		$size = null;
		if ($file && $file->exists()) {
			try {
				$size = $file->getSize()->getInB()->getAmount();
			} catch (\Throwable $e) {
				$size = null;
			}
		}

		return new RestResponse([
			"url" => (string)$this->getURL(),
			"type" => $this->getMime(),
			"extension" => $this->getExtension(),
			"size" => $size,
			"dimensions" => $versionImage ? $versionImage->getImageSize()->getRestResponse($request, $options) : null,
		]);
	}
}
