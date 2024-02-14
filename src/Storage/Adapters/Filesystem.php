<?php

namespace Katu\Storage\Adapters;

use Katu\Storage\Entity;
use Katu\Storage\Storage;
use Katu\Tools\Package\Package;

class Filesystem extends Storage
{
	protected $root = "";

	public function __construct(string $root)
	{
		$this->setRoot($root);
	}

	public function getPackage(): Package
	{
	}

	public function setRoot(string $root): Filesystem
	{
		$this->root = $root;

		return $this;
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
