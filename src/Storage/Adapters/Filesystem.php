<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Entity;
use Katu\Storage\Storage;
use Katu\Tools\Package\Package;
use Katu\Types\TClass;

class Filesystem extends Storage
{
	protected $root = "";

	public function __construct(string $root)
	{
		$this->setRoot($root);
	}

	public static function createFromPackage(Package $package): Storage
	{
		return new static($package->getPayload()["root"]);
	}

	public function getPackage(): Package
	{
		return new Package([
			"class" => (new TClass($this))->getPortableName(),
			"root" => $this->getRoot(),
		]);
	}

	public function setRoot(string $root): Filesystem
	{
		$this->root = $root;

		return $this;
	}

	public function getRoot(): string
	{
		return $this->root;
	}

	public function getName(): string
	{
	}

	public function listEntities(): iterable
	{
	}

	public function readPath(string $path)
	{
	}

	public function readEntity(Entity $entity)
	{
		return file_get_contents($entity->getURI());
	}

	public function writeToEntity(Entity $entity, $contents): Entity
	{
	}

	public function writeToPath(string $path, $contents): Entity
	{
	}

	public function deleteByPath(string $path): bool
	{
	}

	public function deleteEntity(Entity $entity): bool
	{
	}
}
