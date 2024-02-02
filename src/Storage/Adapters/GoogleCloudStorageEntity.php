<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Entity;
use Katu\Storage\Storage;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TFileSize;
use Katu\Types\TURL;

class GoogleCloudStorageEntity extends Entity
{
	public function __construct(GoogleCloudStorage $storage, \Google\Cloud\Storage\StorageObject $storageObject)
	{
		$this->setStorage($storage);
		$this->setStorageObject($storageObject);
	}

	public static function createFromPackage(Package $package): GoogleCloudStorageEntity
	{
		$storage = Storage::createFromPackage(new Package($package->getPayload()["storage"]));
		$storageObject = $storage->getBucket()->object($package->getPayload()["name"]);

		return new static($storage, $storageObject);
	}

	public function getPackage(): Package
	{
		return new Package([
			"storage" => [
				"class" => (new TClass($this->getStorage()))->getPortableName(),
				"bucket" => $this->getStorageObject()->info()["bucket"],
			],
			"class" => (new TClass($this))->getPortableName(),
			"name" => $this->getStorageObject()->info()["name"],
		]);
	}

	public function getURI(): string
	{
		return $this->getStorageObject()->info()["selfLink"];
	}

	public function getFileName(): string
	{
		return basename(urldecode($this->getStorageObject()->info()["name"]));
	}

	public function getFileSize(): TFileSize
	{
		return new TFileSize($this->getStorageObject()->info()["size"]);
	}

	public function getContentType(): ?string
	{
		return $this->getStorageObject()->info()["contentType"];
	}

	public function getIsPublic(): bool
	{
		return (bool)count(array_filter($this->getStorageObject()->acl()->get(), function (array $acl) {
			return ($acl["entity"] ?? null) == "allUsers" && in_array($acl["role"], ["READER", "OWNER"]);
		}));
	}

	public function getPublicURL(): ?TURL
	{
		return $this->getIsPublic() ? new TURL("https://storage.googleapis.com/{$this->getStorageObject()->info()["bucket"]}/{$this->getStorageObject()->info()["name"]}") : null;
	}
}
