<?php

namespace Katu;

class Image {

	protected $source;

	public function __construct($source = null) {
		$this->source = Image\Source::createFromInput($source);
	}

	public function __toString() {
		return (string)$this->getSource()->getUrl();
	}

	public function getSource() {
		return $this->source;
	}

	public function getImageVersion() {
		$args = func_get_args();
		if (isset($args[0]) && $args[0] instanceof \Katu\Image\Version) {
			$version = $args[0];
		} else {
			$version = call_user_func_array(['\\Katu\\Image\\Version', 'createFromConfig'], $args);
		}

		return new \Katu\Image\ImageVersion($this, $version);
	}

	public function getInterventionImage() {
		return \Intervention\Image\ImageManagerStatic::make($this->getSource()->getUri());
	}

	public function getPixel() {
		$version = (new \Katu\Image\Version('pixel'))
			->addFilter(new \Katu\Image\Filters\Fit([
				'width' => 1,
				'height' => 1,
			]))
			->setQuality(100)
			->setExtension('png')
			;

		$imageVersion = new \Katu\Image\ImageVersion($this, $version);

		return $imageVersion->getImage();
	}

	public function getColors($n = 1) {
		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($uri, $n) {

			$palette = \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($uri) {

				try {
					return \League\ColorExtractor\Palette::fromFilename($uri);
				} catch (\Exception $e) {
					return false;
				}

			}, $uri);

			if (!$palette) {
				return false;
			}

			$mostUsedColors = array_keys($palette->getMostUsedColors($n));

			return array_map(function($color) {
				return new \Katu\Types\TColor(\League\ColorExtractor\Color::fromIntToHex($color));
			}, $mostUsedColors);

		}, $this->getSource()->getUri(), $n);
	}

	public function getImageSize() {
		return \Katu\Cache::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function($image) {

			try {
				$size = getimagesize($image->getSource()->getUri());
				return new \Katu\Types\TImageSize($size[0], $size[1]);
			} catch (\Exception $e) {
				throw new \Katu\Exceptions\DoNotCacheException;
			}

		}, $this);
	}

}
