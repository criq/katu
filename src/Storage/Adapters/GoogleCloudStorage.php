<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Storage;
use Katu\Storage\Entity;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;

abstract class GoogleCloudStorage extends Storage
{
	protected $bucket;

	abstract public static function getClient(): \Google\Cloud\Storage\StorageClient;

	public function __construct(\Google\Cloud\Storage\Bucket $bucket)
	{
		$this->setBucket($bucket);
	}

	public static function createFromPackage(Package $package): GoogleCloudStorage
	{
		return new static(static::getClient()->bucket($package->getPayload()["bucket"]));
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"bucket" => $this->getBucket()->info()["name"],
		]);
	}

	public function setBucket(\Google\Cloud\Storage\Bucket $bucket): GoogleCloudStorage
	{
		$this->bucket = $bucket;

		return $this;
	}

	public function getBucket(): \Google\Cloud\Storage\Bucket
	{
		return $this->bucket;
	}

	public function write(Entity $item, $content): Entity
	{
		$this->getBucket()->upload($content, [
			"name" => $item->getFileName(),
		]);

		return $item;
	}

	public function read(Entity $item)
	{
		return $this->getBucket()->object($item->getFileName())->downloadAsString();
	}

	public function delete(Entity $item): bool
	{
		try {
			$this->getBucket()->object($item->getFileName())->delete();

			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getName(): string
	{
		return $this->getBucket()->info()["name"];
	}

	public function listEntities(): iterable
	{
		$class = \App\App::getContainer()->get(\Katu\Storage\Adapters\GoogleCloudStorageEntity::class);

		return array_map(function (\Google\Cloud\Storage\StorageObject $object) use ($class) {
			return (new $class($this, $object));
		}, iterator_to_array($this->getBucket()->objects()));
	}
}
