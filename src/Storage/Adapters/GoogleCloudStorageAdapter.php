<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\AdapterInterface;
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

	public function write(string $name, $content): Resource
	{
		$res = $this->getBucket()->upload($content, [
			"name" => $name,
		]);

		return new Resource($res->info()["selfLink"]);
	}

	public function read(string $name)
	{
	}

	public function getSize(string $uri): int
	{
		preg_match("/https:\/\/www.googleapis.com\/storage\/v1\/b\/(?<bucketName>.+)\/o\/(?<objectName>.+)/", func_get_arg(0), $match);

		$info = $this->getBucket()->object(urldecode($match["objectName"]))->info();

		return $info["size"];
	}
}
