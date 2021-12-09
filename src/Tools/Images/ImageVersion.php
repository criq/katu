<?php

namespace Katu\Tools\Images;

use Katu\Types\TIdentifier;

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
		try {
			if ($this->getImage()->getSource() instanceof Sources\File) {
				return (string)\Katu\Tools\Routing\URL::getFor('images.getVersionSrc.file', [
					'fileId' => $this->getImage()->getSource()->getInput()->getId(),
					'fileSecret' => $this->getImage()->getSource()->getInput()->getSecret(),
					'version' => $this->getVersion()->getName(),
					'name' => $this->getImage()->getSource()->getInput()->name,
				]);
			} elseif ($this->getImage()->getSource() instanceof Sources\URL) {
				return (string)\Katu\Tools\Routing\URL::getFor('images.getVersionSrc.url', [
					'version' => $this->getVersion()->getName(),
				], [
					'url' => $this->getImage()->getSource()->geTURL(),
				]);
			}
		} catch (\Throwable $e) {
			(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __FUNCTION__)))->error($e);
		}

		return '';
	}

	public function getExtension(): string
	{
		return $this->getVersion()->getExtension() ?: $this->getImage()->getSource()->getExtension();
	}

	public function getFile(): \Katu\Files\File
	{
		$pathSegments = [];

		$hash = $this->image->getSource()->getHash();
		$pathSegments[] = substr($hash, 0, 2);
		$pathSegments[] = substr($hash, 2, 2);
		$pathSegments[] = substr($hash, 4, 2);
		$pathSegments[] = $hash . '.' . $this->getExtension();

		return new \Katu\Files\File($this->getVersion()->getDir(), implode('/', $pathSegments));
	}

	public function getImage(): ?Image
	{
		try {
			if (!$this->getFile()->exists()) {
				$interventionImage = $this->image->getInterventionImage();
				foreach ($this->getVersion()->getFilters() as $filter) {
					$filter->apply($interventionImage);
				}

				$this->getFile()->getDir()->makeDir();

				$interventionImage->save($this->getFile(), $this->getVersion()->getQuality());
			}

			return new Image($this->getFile());
		} catch (\Throwable $e) {
			(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __METHOD__)))->error($e);

			return null;
		}
	}

	public function getVersion(): Version
	{
		return $this->version;
	}

	public function getEmbedSrc(): ?string
	{
		$this->getImage();

		$file = $this->getFile();
		$mime = $file->getMime();
		$base64 = @base64_encode($file->get());

		if ($mime && $base64) {
			return 'data:' . $mime . ';base64,' . $base64;
		}

		return null;
	}
}
