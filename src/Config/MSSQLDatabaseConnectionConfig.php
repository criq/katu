<?php

namespace Katu\Config;

class MSSQLDatabaseConnectionConfig extends DatabaseConnectionConfig
{
	public function getSchema(): string
	{
		return "dblib";
	}

	public function getDriver(): string
	{
		return "pdo_dblib";
	}
}
