<?php

namespace Katu\Tools\Images;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

class Image
{
	protected $source;

	public function __construct($source = null)
	{
		$this->source = Source::createFromInput($source);
	}

	public function __toString()
	{
		return (string)$this->getSource()->getURL();
	}

	public function getSource()
	{
		return $this->source;
	}

	public function getImageVersion()
	{
		$args = func_get_args();
		if (isset($args[0]) && $args[0] instanceof Version) {
			$version = $args[0];
		} else {
			$version = Version::createFromConfig(...$args);
		}

		return new ImageVersion($this, $version);
	}

	public function getInterventionImage()
	{
		$uri = $this->getSource()->getUri();
		try {
			return \Intervention\Image\ImageManagerStatic::make($uri);
		} catch (\Throwable $e) {
			if (preg_match('/SSL operation failed/', $e->getMessage())) {
				$uri = preg_replace('/^https/', 'http', $uri);
				return \Intervention\Image\ImageManagerStatic::make($uri);
			} else {
				throw $e;
			}
		}
	}

	public function getPixel()
	{
		$version = (new Version('pixel'))
			->addFilter(new Filters\Fit([
				'width' => 1,
				'height' => 1,
			]))
			->setQuality(100)
			->setExtension('png')
			;

		$imageVersion = new ImageVersion($this, $version);

		return $imageVersion->getImage();
	}

	public function getColors($number = 1)
	{
		$timeout = new Timeout('1 year');

		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), $timeout, function ($image, $number) use ($timeout) {
			$palette = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), $timeout, function ($image) {
				try {
					return \League\ColorExtractor\Palette::fromGD($image->getInterventionImage()->getCore());
				} catch (\Throwable $e) {
					return false;
				}
			}, $image);

			if (!$palette) {
				return false;
			}

			$mostUsedColors = array_keys($palette->getMostUsedColors($number));

			return array_map(function ($color) {
				return new \Katu\Types\TColor(\League\ColorExtractor\Color::fromIntToHex($color));
			}, $mostUsedColors);
		}, $this, $number);
	}

	public function getImageSize()
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout('1 year'), function ($image) {
			try {
				$size = getimagesize($image->getSource()->getUri());
				return new \Katu\Types\TImageSize($size[0], $size[1]);
			} catch (\Throwable $e) {
				\Katu\Exceptions\Handler::log($e);
				throw new \Katu\Exceptions\DoNotCacheException;
			}
		}, $this);
	}

	public function getMime()
	{
		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout('1 year'), function ($image) {
			try {
				$size = getimagesize($image->getSource()->getUri());
				return $size['mime'];
			} catch (\Throwable $e) {
				\Katu\Exceptions\Handler::log($e);
				throw new \Katu\Exceptions\DoNotCacheException;
			}
		}, $this);
	}

	public function getEmbedSrc()
	{
		$mime = $this->getMime();
		$base64 = @base64_encode(@file_get_contents($this->getSource()->getUri()));

		if ($mime && $base64) {
			return 'data:' . $mime . ';base64,' . $base64;
		}

		return false;
	}
}
