<?php

namespace Katu\Tools\Package;

interface PackagedInterface
{
	public function getPackage(): Package;
	public static function createFromPackage(Package $package);
}
