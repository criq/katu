<?php

namespace Katu\Storage;

use Katu\Tools\Package\Package;
use Katu\Tools\Package\PackagedInterface;
use Katu\Types\TClass;

abstract class Storage implements PackagedInterface
{
	abstract public function delete(Entity $item): bool;
	abstract public function getName(): string;
	abstract public function listEntities(): iterable;
	abstract public function read(Entity $item);
	abstract public function write(Entity $item, $content): Entity;

	public static function createFromPackage(Package $package): Storage
	{
		$className = TClass::createFromPortableName($package->getPayload()["class"])->getName();

		return $className::createFromPackage($package);
	}
}
