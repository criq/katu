<?php

namespace Katu\Models;

abstract class GeneralModel
{
	public static function getManager(): GeneralManager
	{
		var_dump(get_called_class());die;
	}

	public static function getConnection(): \Katu\PDO\Connection
	{
		return static::getManager()->getConnection();
	}

	public static function getTable(): \Katu\PDO\Table
	{
		return static::getManager()->getTable();
	}

	public static function getColumn($name): \Katu\PDO\Column
	{
		return static::getTable()->getColumn($name);
	}
}
