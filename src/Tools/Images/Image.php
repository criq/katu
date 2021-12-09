<?php

namespace Katu\Tools\Images;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TArray;
use Katu\Types\TIdentifier;
use Katu\Types\TImageSize;

class Image
{
	protected $source;

	public function __construct($source = null)
	{
		$this->source = Source::createFromInput($source);
	}

	public function __toString(): string
	{
		return (string)$this->getSource()->getURL();
	}

	public function getSource(): Source
	{
		return $this->source;
	}

	public function getImageVersion(): ?ImageVersion
	{
		try {
			$args = func_get_args();
			if (isset($args[0]) && $args[0] instanceof Version) {
				$version = $args[0];
			} else {
				$version = Version::createFromConfig(...$args);
			}

			return new ImageVersion($this, $version);
		} catch (\Throwable $e) {
			(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __METHOD__)))->error($e);

			return null;
		}
	}

	public function getInterventionImage(): ?\Intervention\Image\Image
	{
		$uri = $this->getSource()->getUri();

		try {
			return \Intervention\Image\ImageManagerStatic::make($uri);
		} catch (\Throwable $e) {
			if (preg_match("/(SSL operation failed|Peer certificate)/", $e->getMessage())) {
				$uri = preg_replace('/^https/', 'http', $uri);

				var_dump(file_get_contents($uri));die;

				return \Intervention\Image\ImageManagerStatic::make($uri);
			} else {
				(new \Katu\Tools\Logs\Logger(new TIdentifier(__CLASS__, __METHOD__)))->error($e);

				return null;
			}
		}
	}

	public function getPixel(): Image
	{
		$version = new Version('pixel', [
			new Filters\Fit([
				'width' => 1,
				'height' => 1,
			]),
		], 'png', 100);

		$imageVersion = new ImageVersion($this, $version);

		return $imageVersion->getImage();
	}

	public function getTemporaryFile(): \Katu\Files\File
	{
		$file = \Katu\Files\File::createTemporaryWithExtension('png');
		$file->touch();

		$interventionImage = $this->getInterventionImage();
		$interventionImage->save($file);

		return $file;
	}

	public function getColors()
	{
		set_time_limit(600);
		\Katu\Tools\System\Memory::setLimit('2G');

		$interventionImage = $this->getInterventionImage();

		$array = [];
		for ($x = 0; $x < $interventionImage->width(); $x++) {
			for ($y = 0; $y < $interventionImage->height(); $y++) {
				$array[] = $interventionImage->pickColor($x, $y, 'hex');
			}
		}

		return $array;
	}

	public function getSortedColors(): array
	{
		return (new TArray(array_count_values($this->getColors())))->natsort()->reverse()->getArray();
	}

	public function getImageSize(): ?TImageSize
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

	public function getMime(): ?string
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

	public function getEmbedSrc(): ?string
	{
		$mime = $this->getMime();
		$base64 = @base64_encode(@file_get_contents($this->getSource()->getUri()));

		if ($mime && $base64) {
			return 'data:' . $mime . ';base64,' . $base64;
		}

		return null;
	}
}
