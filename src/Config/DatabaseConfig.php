<?php

namespace Katu\Config;

abstract class DatabaseConfig extends \Katu\Config\Config
{
	abstract public function getDatabaseConnectionConfigs(): DatabaseConnectionConfigCollection;
}
