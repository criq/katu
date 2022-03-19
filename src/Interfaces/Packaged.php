<?php

namespace Katu\Interfaces;

use Katu\Types\TPackage;

interface Packaged
{
	public function getPackage(): TPackage;
	public static function createFromPackage(TPackage $package);
}
