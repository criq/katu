<?php

namespace Katu\Tools\Factories;

use Sexy\Sexy as SX;

abstract class TableFactory extends Factory
{
	abstract public static function getTable(): \Katu\PDO\Table;

	public static function get($primaryKey)
	{
		$table = static::getTable();

		$sql = SX::select()
			->setGetFoundRows(false)
			->from($table)
			->where(SX::eq($table->getPrimaryKeyColumn(), $primaryKey))
			;

		return $table->getConnection()->createQuery($sql)
			->setFactory(new static)
			->getResult()
			->getOne()
			;
	}

	public static function getBySQL($sql): \Katu\PDO\Result
	{
		return static::getTable()->getConnection()->createQuery($sql)
			->setFactory(new static)
			->getResult()
			;
	}
}
