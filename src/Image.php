<?php

namespace Katu;

class Image
{
	protected $source;

	public function __construct($source = null)
	{
		$this->source = Image\Source::createFromInput($source);
	}

	public function __toString(): string
	{
		return (string)$this->getSource()->getUrl();
	}

	public function getSource()
	{
		return $this->source;
	}

	public function getImageVersion(): \Katu\Image\ImageVersion
	{
		$args = func_get_args();
		if (isset($args[0]) && $args[0] instanceof \Katu\Image\Version) {
			$version = $args[0];
		} else {
			$version = call_user_func_array(['\\Katu\\Image\\Version', 'createFromConfig'], $args);
		}

		return new \Katu\Image\ImageVersion($this, $version);
	}

	public function getInterventionImage()
	{
		return \Intervention\Image\ImageManagerStatic::make($this->getSource()->getUri());
	}

	public function getPixel()
	{
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

	public function getColors($n = 1)
	{
		try {
			$palette = \League\ColorExtractor\Palette::fromFilename($this->getSource()->getUri());
		} catch (\Throwable $e) {
			$palette = null;
		}

		if (!$palette) {
			return false;
		}

		$mostUsedColors = array_keys($palette->getMostUsedColors($n));

		return array_map(function ($color) {
			return new \Katu\Types\TColor(\League\ColorExtractor\Color::fromIntToHex($color));
		}, $mostUsedColors);
	}

	public function getImageSize(): ?\Katu\Types\TImageSize
	{
		try {
			$size = getimagesize($this->getSource()->getUri());
			return new \Katu\Types\TImageSize($size[0], $size[1]);
		} catch (\Throwable $e) {
			\App\Extensions\ErrorHandler::log($e);

			return null;
		}
	}

	public function getMime(): ?string
	{
		try {
			$size = getimagesize($this->getSource()->getUri());

			return $size['mime'];
		} catch (\Throwable $e) {
			\App\Extensions\ErrorHandler::log($e);

			return null;
		}
	}

	public function getEmbedSrc(): ?string
	{
		try {
			$mime = $this->getMime();
			$base64 = base64_encode(file_get_contents($this->getSource()->getUri()));

			if ($mime && $base64) {
				return "data:{$mime};base64,{$base64}";
			}

			return null;
		} catch (\Throwable $e) {
			\App\Extensions\ErrorHandler::log($e);

			return null;
		}
	}
}
