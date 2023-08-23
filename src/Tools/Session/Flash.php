<?php

namespace Katu\Tools\Session;

use Katu\Tools\Strings\Code;
use Katu\Types\TClass;

abstract class Flash
{
	public function getClassCode(): Code
	{
		return new Code((new TClass($this))->getShortName());
	}
}
