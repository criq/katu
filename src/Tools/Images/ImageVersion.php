<?php

namespace Katu\Tools\Images;

class ImageVersion {

	protected $image;
	protected $version;

	public function __construct(Image $image, Version $version) {
		$this->image = $image;
		$this->version = $version;
	}

	public function __toString() {
		if ($this->image->getSource() instanceof Sources\File) {
			return (string)\Katu\Tools\Routing\URL::getFor('images.getVersionSrc.file', [
				'fileId' => $this->image->getSource()->getInput()->getId(),
				'fileSecret' => $this->image->getSource()->getInput()->getSecret(),
				'version' => $this->version->getName(),
				'name' => $this->image->getSource()->getInput()->name,
			]);

		} elseif ($this->image->getSource() instanceof Sources\URL) {
			return (string)\Katu\Tools\Routing\URL::getFor('images.getVersionSrc.url', [
				'version' => $this->version->getName(),
			], [
				'url' => $this->image->getSource()->geTURL(),
			]);
		}

		return '';
	}

	public function getExtension() {
		return $this->version->getExtension() ?: $this->image->getSource()->getExtension();
	}

	public function getFile() {
		$pathSegments = [];

		$hash = $this->image->getSource()->getHash();
		$pathSegments[] = substr($hash, 0, 2);
		$pathSegments[] = substr($hash, 2, 2);
		$pathSegments[] = substr($hash, 4, 2);
		$pathSegments[] = $hash . '.' . $this->getExtension();

		return new \Katu\Files\File($this->version->getDir(), implode('/', $pathSegments));
	}

	public function getImage() {
		try {

			if (!$this->getFile()->exists()) {

				$interventionImage = $this->image->getInterventionImage();
				foreach ($this->version->getFilters() as $filter) {
					$filter->apply($interventionImage);
				}

				$this->getFile()->getDir()->makeDir();

				$interventionImage->save($this->getFile(), $this->version->getQuality());

			}

			return new Image($this->getFile());

		} catch (\Exception $e) {
			\App\Extensions\ErrorHandler::log($e);
			return false;
		}
	}

}
