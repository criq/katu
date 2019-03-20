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

			return new \Katu\Types\TColor($color);

		} catch (\Katu\Exceptions\ImageErrorException $e) {
			return false;
		}
	}

	static function extractColors($n = 1) {
		try {

			$client = new \League\ColorExtractor\Client;
			var_dump($client); die;
			$image = \Katu\Utils\Image::getVersionUrl($uri, [
				'filters' => [
					[
						'filter' => 'fit',
						'width' => 600,
						'height' => 600,
					],
				],
			]);
			$extracted = (array) $client->loadJpeg($image)->extract($n);
			return array_map(function($i) {
				$color = \Katu\Types\TColorRgb::getFromHex($i);
				return new \MischiefCollective\ColorJizz\Formats\RGB($color->r, $color->g, $color->b);
			}, $extracted);


		} catch (\Exception $e) {

			\Katu\ErrorHandler::log($e);

			return false;

		}
	}

}
