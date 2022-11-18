<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\AdapterInterface;
use Katu\Storage\Storage;
use Katu\Storage\Entity;
use Katu\Types\TFileSize;

class GoogleCloudStorageAdapter implements AdapterInterface
{
	protected $bucket;

	public function __construct(\Google\Cloud\Storage\Bucket $bucket)
	{
		$this->setBucket($bucket);
	}

	public function setBucket(\Google\Cloud\Storage\Bucket $bucket): GoogleCloudStorageAdapter
	{
		$this->bucket = $bucket;

		return $this;
	}

	public function getBucket(): \Google\Cloud\Storage\Bucket
	{
		return $this->bucket;
	}

	public function listEntities(Storage $storage): iterable
	{
		$entityClass = \App\App::getContainer()->get(\Katu\Storage\Entity::class);

		$res = [];
		foreach ($this->getBucket()->objects() as $object) {
			$res[] = new $entityClass($storage, $object->info()["name"]);
		}

		return $res;
	}

	public static function getNameFromURI(string $uri): string
	{
		preg_match("/https:\/\/www.googleapis.com\/storage\/v1\/b\/(?<bucketName>.+)\/o\/(?<objectName>.+)/", $uri, $match);

		return urldecode($match["objectName"]);
	}

	public function write(Entity $item, $content): Entity
	{
		$this->getBucket()->upload($content, [
			"name" => $item->getName(),
		]);

		return $item;
	}

	public function read(Entity $item)
	{
		return $this->getBucket()->object($item->getName())->downloadAsString();
	}

	public function delete(Entity $item): bool
	{
		try {
			$this->getBucket()->object($item->getName())->delete();

			return true;
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function getURI(Entity $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["selfLink"];
	}

	public function getFileSize(Entity $item): TFileSize
	{
		return new TFileSize($this->getBucket()->object($item->getName())->info()["size"]);
	}

	public function getContentType(Entity $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["contentType"];
	}
}
