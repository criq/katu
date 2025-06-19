<?php

namespace Katu\Tools\Images;

use Katu\Tools\Images\Filters\FitFilter;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Strings\Code;
use Katu\Types\TArray;
use Katu\Types\TImageSize;
use Katu\Types\TURL;
use Psr\Http\Message\ServerRequestInterface;

class Image implements RestResponseInterface, PackagedInterface
{
	protected $source;

	public function __construct($input)
	{
		$this->setSource(Source::createFromInput($input));
	}

	public function __toString(): string
	{
		return (string)$this->getURL();
	}

	public function getPackage(): Package
	{
		return new Package([
			"source" => $this->getSource()->getPackage()->getPayload(),
		]);
	}

	public static function createFromPackage(Package $package): Image
	{
		return new static(Source::createFromPackage(new Package($package->getPayload()["source"])));
	}

	public function getURI(): string
	{
		return $this->getSource()->getURI();
	}

	public function getURL(): ?TURL
	{
		$source = $this->getSource();
		if ($source) {
			return $source->getURL();
		}

		return null;
	}

	public function setSource(Source $source): Image
	{
		$this->source = $source;

		return $this;
	}

	public function getSource(): Source
	{
		return $this->source;
	}

	public function getImageVersion($version): ?ImageVersion
	{
		if ($version instanceof Code) {
			$version = $version->getConstantFormat();
		}

		if ($version instanceof Version) {
			$resolvedVersion = $version;
		} elseif (is_string($version)) {
			$resolvedVersion = \App\App::getImageConfig()->getVersions()->filterByTitle($version)->getFirst();
		}

		if (!($resolvedVersion ?? null)) {
			return null;
		}

		return new ImageVersion($this, $resolvedVersion);
	}

	public function getImageVersions(VersionCollection $versions): ImageVersionCollection
	{
		return new ImageVersionCollection(array_values(array_filter(array_map(function (Version $version) {
			return $this->getImageVersion($version);
		}, $versions->getArrayCopy()))));
	}

	public function getInterventionImage(): ?\Intervention\Image\Image
	{
		return \Intervention\Image\ImageManagerStatic::make((string)$this->getSource()->getLocalFile());
	}

	public function getPixel(): Image
	{
		$version = new Version("pixel", "png", 100, new FilterCollection([
			new FitFilter([
				"width" => 1,
				"height" => 1,
			]),
		]));

		$imageVersion = new ImageVersion($this, $version);

		return $imageVersion->getVersionImage();
	}

	public function getTemporaryFile(): \Katu\Files\File
	{
		$file = \Katu\Files\File::createTemporaryWithExtension("png");
		$file->touch();

		$interventionImage = $this->getInterventionImage();
		$interventionImage->save($file);

		return $file;
	}

	public function getColors()
	{
		set_time_limit(600);
		\Katu\Tools\System\Memory::setLimit(\Katu\Types\TFileSize::createFromShorthand("2G"));

		$interventionImage = $this->getInterventionImage();

		$array = [];
		for ($x = 0; $x < $interventionImage->width(); $x++) {
			for ($y = 0; $y < $interventionImage->height(); $y++) {
				$array[] = $interventionImage->pickColor($x, $y, "hex");
			}
		}

		return $array;
	}

	public function getSortedColors(): array
	{
		return (new TArray(array_count_values($this->getColors())))->sortNaturally()->reverse()->getArray();
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

	/****************************************************************************
	 * REST.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		$defaultOptions = new OptionCollection([
			new Option("QUALITY", 80),
		]);

		$options = $defaultOptions->getMergedWith($options);
		$versions = $options->getValue("IMAGE_VERSIONS");

		$data = [];
		if ($versions) {
			$data["versions"] = array_map(function (Version $version) use ($request, $options) {
				return (new ImageVersion($this, $version))->getRestResponse($request, $options);
			}, $versions->getAssoc()->getArrayCopy());
		}

		return new RestResponse($data);
	}
}
