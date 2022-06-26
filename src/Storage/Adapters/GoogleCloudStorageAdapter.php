<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\AdapterInterface;
use Katu\Storage\Item;

class GoogleCloudStorageAdapter implements AdapterInterface
{
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

	public function write(Item $item, $content): Item
	{
		$this->getBucket()->upload($content, [
			"name" => $item->getName(),
		]);

		return $item;
	}

	public function read(Item $item)
	{
		return $this->getBucket()->object($item->getName())->downloadAsString();
	}

	public function getURI(Item $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["selfLink"];
	}

	public function getSize(Item $item): int
	{
		return $this->getBucket()->object($item->getName())->info()["size"];
	}

	public function getContentType(Item $item): string
	{
		return $this->getBucket()->object($item->getName())->info()["contentType"];
	}
}
