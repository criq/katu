<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Storage;
use Katu\Storage\Entity;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;
use Katu\Types\TFileSize;
use Katu\Types\TURL;

class GoogleCloudStorageEntity extends Entity
{
	private $storageObjectInfo;

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
				"bucket" => $this->getStorageObjectInfo()["bucket"],
			],
			"class" => (new TClass($this))->getPortableName(),
			"name" => $this->getStorageObjectInfo()["name"],
		]);
	}

	public function getStorageObjectInfo(): array
	{
		if (is_null($this->storageObjectInfo)) {
			$this->storageObjectInfo = $this->getStorageObject()->info();
		}

		return $this->storageObjectInfo;
	}

	public function getURI(): string
	{
		return $this->getStorageObjectInfo()["selfLink"];
	}

	public function getPath(): string
	{
		return rawurldecode($this->getStorageObjectInfo()["name"]);
	}

	public function getContentType(): ?string
	{
		return $this->getStorageObjectInfo()["contentType"];
	}

	public function getFileSize(): TFileSize
	{
		return new TFileSize($this->getStorageObjectInfo()["size"]);
	}

	public function getFileName(): string
	{
		return basename(urldecode($this->getStorageObjectInfo()["name"]));
	}

	// TODO - uniform ACL?
	public function getIsPublic(): bool
	{
		try {
			return (bool)count(array_filter($this->getStorageObject()->acl()->get(), function (array $acl) {
				return ($acl["entity"] ?? null) == "allUsers" && in_array($acl["role"], ["READER", "OWNER"]);
			}));
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	public function getPublicURL(): ?TURL
	{
		$name = rawurlencode($this->getStorageObjectInfo()["name"]);

		return $this->getIsPublic() ? new TURL("https://storage.googleapis.com/{$this->getStorageObjectInfo()["bucket"]}/{$name}") : null;
	}
}
