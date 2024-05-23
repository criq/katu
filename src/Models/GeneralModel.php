<?php

namespace Katu\Models;

abstract class GeneralModel
{
	public static function getManager(): GeneralManager
	{
		$managerClassName = get_called_class() . "Manager";

		return new $managerClassName;
	}

	public static function getConnection(): \Katu\PDO\Connection
	{
		return static::getManager()->getConnection();
	}

	public static function hasConnection(): bool
	{
		try {
			return (bool)static::getConnection();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
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
