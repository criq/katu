<?php

namespace Katu\Config;

use Katu\Tools\Routing\RouteCollection;

abstract class RouterConfig
{
	abstract public function getRoutes(): RouteCollection;
}
