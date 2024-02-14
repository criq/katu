<?php

namespace Katu\Storage;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;

abstract class Storage implements PackagedInterface
{
	abstract public function deleteByPath(string $path): bool;
	abstract public function deleteEntity(Entity $entity): bool;
	abstract public function getName(): string;
	abstract public function listEntities(): iterable;
	abstract public function readEntity(Entity $entity);
	abstract public function readPath(string $path);
	abstract public function writeToEntity(Entity $entity, $contents): Entity;
	abstract public function writeToPath(string $path, $contents): Entity;

	public static function createFromPackage(Package $package): Storage
	{
		$className = TClass::createFromPortableName($package->getPayload()["class"])->getName();

		return $className::createFromPackage($package);
	}
}
