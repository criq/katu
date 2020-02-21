<?php

namespace Katu\Tools\Images;

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
		return \Intervention\Image\ImageManagerStatic::make($this->getSource()->getUri());
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

	public function getColors($n = 1)
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function ($uri, $n) {
			$palette = \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function ($uri) {
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

			return array_map(function ($color) {
				return new \Katu\Types\TColor(\League\ColorExtractor\Color::fromIntToHex($color));
			}, $mostUsedColors);
		}, $this->getSource()->getUri(), $n);
	}

	public function getImageSize()
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function ($image) {
			try {
				$size = getimagesize($image->getSource()->getUri());
				return new \Katu\Types\TImageSize($size[0], $size[1]);
			} catch (\Throwable $e) {
				\App\Extensions\Errors\Handler::log($e);
				throw new \Katu\Exceptions\DoNotCacheException;
			}
		}, $this);
	}

	public function getMime()
	{
		return \Katu\Cache\General::get([__CLASS__, __FUNCTION__, __LINE__], 86400 * 365, function ($image) {
			try {
				$size = getimagesize($image->getSource()->getUri());
				return $size['mime'];
			} catch (\Throwable $e) {
				\App\Extensions\Errors\Handler::log($e);
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
