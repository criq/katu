<?php

namespace Katu\Config;

class MySQLDatabaseConnectionConfig extends DatabaseConnectionConfig
{
	public function getSchema(): string
	{
		return "mysql";
	}

	public function getDriver(): string
	{
		return "pdo_mysql";
	}
}
