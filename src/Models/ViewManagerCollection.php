<?php

namespace Katu\Models;

use Katu\Types\TClass;
use Katu\Types\TIdentifier;

class ViewManagerCollection extends \ArrayObject
{
	public static function createDefault(): ViewManagerCollection
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(__CLASS__, __FUNCTION__), function () {
			$dir = new \Katu\Files\File(\App\App::getAppDir(), "Models");
			if ($dir->exists()) {
				$dir->includeAllPhpFiles();
			}

			return new static(array_values(array_filter(array_map(function (string $className) {
				if (is_subclass_of($className, "Katu\Models\ViewManager")) {
					return new TClass($className);
				}
			}, get_declared_classes()))));
		});
	}
}
