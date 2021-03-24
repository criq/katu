<?php

namespace Katu\Interfaces;

interface Packaged
{
	public function getPackage() : \Katu\Types\TPackage;
	public static function createFromPackage(\Katu\Types\TPackage $package);
}
