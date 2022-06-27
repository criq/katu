<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\AdapterInterface;
use Katu\Storage\StorageItem;
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

	public static function getNameFromURI(string $uri): string
	{
		preg_match("/https:\/\/www.googleapis.com\/storage\/v1\/b\/(?<bucketName>.+)\/o\/(?<objectName>.+)/", $uri, $match);

		return urldecode($match["objectName"]);
	}

	public function write(StorageItem $item, $content): StorageItem
	{
		$this->getBucket()->upload($content, [
			"name" => $item->getName(),
		]);

		return $item;
	}

	public function read(StorageItem $item)
	{
		return $this->getBucket()->object($item->getName())->downloadAsString();
	}

	public function getURI(StorageItem $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["selfLink"];
	}

	public function getFileSize(StorageItem $item): TFileSize
	{
		return new TFileSize($this->getBucket()->object($item->getName())->info()["size"]);
	}

	public function getContentType(StorageItem $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["contentType"];
	}
}
