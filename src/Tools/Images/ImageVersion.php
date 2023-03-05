<?php

namespace Katu\Tools\Images;

use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class ImageVersion
{
	protected $image;
	protected $version;

	public function __construct(Image $image, Version $version)
	{
		$this->image = $image;
		$this->version = $version;
	}

	public function __toString(): string
	{
		return (string)$this->getURL();
	}

	public function getImage(): Image
	{
		return $this->image;
	}

	public function getVersion(): Version
	{
		return $this->version;
	}

	public function getURL(): ?TURL
	{
		$url = $this->getImage()->getURL();
		if ($url) {
			return \Katu\Tools\Routing\URL::getFor("images.getVersionSrc.url", [
				"version" => $this->getVersion()->getName(),
			], [
				"url" => (string)$url,
			]);
		}

		$versionImage = $this->getVersionImage();
		if ($versionImage) {
			return $versionImage->getURL();
		}

		return null;
	}

	public function getExtension(): string
	{
		return $this->getVersion()->getExtension() ?: $this->getImage()->getSource()->getExtension();
	}

	public function getFile(): ?\Katu\Files\File
	{
		try {
			$pathSegments = [];

			$hash = $this->getImage()->getSource()->getHash();
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

	public function getVersionImage(): ?Image
	{
		try {
			if (!$this->getFile()->exists()) {
				$interventionImage = $this->getImage()->getInterventionImage();
				foreach ($this->getVersion()->getFilters() as $filter) {
					$filter->apply($interventionImage);
				}

				$this->getFile()->getDir()->makeDir();

				$interventionImage->save($this->getFile(), $this->getVersion()->getQuality());
			}

			return new Image($this->getFile());
		} catch (\Throwable $e) {
			\App\App::getLogger(new TIdentifier(__CLASS__, __METHOD__))->error(serialize($this->getFile()));

			return null;
		}
	}

	public function getEmbedSrc(): ?string
	{
		try {
			$this->getImage();

			$file = $this->getFile();
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
}
