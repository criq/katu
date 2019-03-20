<?php

namespace Katu;

class Image {

	protected $source;

	public function __construct($source = null) {
		$this->source = Image\Source::createFromInput($source);
	}

	public function getSource() {
		return $this->source;
	}

	public function getInterventionImage() {
		return \Intervention\Image\ImageManagerStatic::make($this->getSource()->getUri());
	}

	public function getColor() {
		try {

			$version = (new \Katu\Image\Version)
				->addFilter(new \Katu\Image\Filters\Fit([
					'width' => 1,
					'height' => 1,
				]))
				->setQuality(100)
				->setExtension('png')
				;

			$imageVersion = new \Katu\Image\ImageVersion($this, $version);
			$image = $imageVersion->getImage();
			$interventionImage = $image->getInterventionImage();

			$color = $interventionImage->pickColor(0, 0, 'hex');
			var_dump(new \Katu\Types\TColor($color)); die;



		} catch (\Katu\Exceptions\ImageErrorException $e) {
			return false;
		}
	}

}
