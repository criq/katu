<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\AdapterInterface;
use Katu\Storage\Item;
use Katu\Storage\Resource;

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
	}

	public function getSize(Item $item): int
	{
		preg_match("/https:\/\/www.googleapis.com\/storage\/v1\/b\/(?<bucketName>.+)\/o\/(?<objectName>.+)/", func_get_arg(0), $match);

		$info = $this->getBucket()->object(urldecode($match["objectName"]))->info();

		return $info["size"];
	}
}
