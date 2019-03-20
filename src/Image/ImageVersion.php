<?php

namespace Katu\Image;

class ImageVersion {

	protected $image;
	protected $version;

	public function __construct($image, $version) {
		$this->image = $image;
		$this->version = $version;
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

		return new \Katu\Utils\File($this->version->getDir(), implode('/', $pathSegments));
	}



	public function getImage() {
		try {

			$image = new \Katu\Image($this->getFile());
			if (!$image->getSource()->getFile()->exists()) {

				$interventionImage = $this->image->getInterventionImage();
				foreach ($this->version->getFilters() as $filter) {
					$filter->apply($interventionImage);
				}

				$image->getSource()->getDir()->makeDir();

				$interventionImage->save($this->getFile(), $this->version->getQuality());

			}

			return $image;

		} catch (\Exception $e) {

		}
	}

}
