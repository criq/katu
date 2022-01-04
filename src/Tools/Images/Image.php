<?php

namespace Katu\Tools\Images;

use Katu\Types\TArray;
use Katu\Types\TIdentifier;
use Katu\Types\TImageSize;
use Katu\Types\TURL;

class Image
{
	protected $source;

	public function __construct($source)
	{
		$this->source = Source::createFromInput($source);
	}

	public function __toString(): string
	{
		return (string)$this->getURL();
	}

	public function getURI(): string
	{
		return $this->getSource()->getURI();
	}

	public function getURL(): ?TURL
	{
		return $this->getSource()->getURL();
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
		$uri = $this->getSource()->getURI();

		try {
			return \Intervention\Image\ImageManagerStatic::make($uri);
		} catch (\Throwable $e) {
			if (preg_match("/(SSL operation failed|Peer certificate)/", $e->getMessage())) {
				$curl = new \Curl\Curl;
				$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
				$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
				$res = $curl->get($uri);

				return \Intervention\Image\ImageManagerStatic::make($res);
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

		return $imageVersion->getVersionImage();
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
		$interventionImage = $this->getInterventionImage();

		return new \Katu\Types\TImageSize($interventionImage->width(), $interventionImage->height());
	}

	public function getMime(): ?string
	{
		return $this->getInterventionImage()->mime;
	}

	public function getEmbedSrc(): ?string
	{
		$mime = $this->getMime();
		$file = $this->getTemporaryFile();
		$base64 = base64_encode($file->get());
		$file->delete();

		if ($mime && $base64) {
			return "data:{$mime};base64,{$base64}";
		}

		return null;
	}
}
