<?php

namespace Katu\Tools\Images\Sources;

use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Images\Source;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;
use Katu\Types\TURL;

class URL extends \Katu\Tools\Images\Source
{
	public function __construct(\Katu\Types\TURL $input)
	{
		return parent::__construct($input);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"url" => (string)$this->getInput(),
		]);
	}

	public static function createFromPackage(Package $package): Source
	{
		return new static(new TURL($package->getPayload()["url"]));
	}

	public function getLocalFile(): ?\Katu\Files\File
	{
		$file = new \Katu\Files\File(\App\App::getTemporaryDir(), [sha1($this->getURI()), $this->getExtension()]);
		if (!$file->exists()) {
			$curl = new \Curl\Curl;

			// TODO - lepší ověření
			$res = $curl->get($this->getURI());
			if ($res) {
				$file->set($res);
			} else {
				$curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
				$curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
				$res = $curl->get($this->getURI());
				if ($res) {
					$file->set($res);
				}
			}
		}

		return $file;
	}

	public function getExtension(): ?string
	{
		try {
			$pathinfo = pathinfo($this->getInput()->getParts()["path"]);
			if (isset($pathinfo["extension"])) {
				return $pathinfo["extension"];
			}

			$size = \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, __LINE__), new Timeout("1 year"), function ($source) {
				return getimagesize($source->getURI());
			}, $this);

			if (isset($size["mime"])) {
				$extension = (new \Mimey\MimeTypes)->getExtension($size["mime"]);
				if ($extension) {
					return $extension;
				}
			}

			throw new \Exception;
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}

	public function getURI(): string
	{
		return (string)$this->getInput();
	}

	public function getURL(): ?TURL
	{
		return $this->getInput();
	}
}
