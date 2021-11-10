<?php

namespace Katu\PDO;

class Column extends \Sexy\Expression
{
	public $name;
	public $table;

	public function __construct(TableBase $table, Name $name)
	{
		$this->table = $table;
		$this->name = $name;
	}

	public function __toString(): string
	{
		return $this->getSql();
	}

	public function getName(): Name
	{
		return $this->name;
	}

	public function getTable(): TableBase
	{
		return $this->table;
	}

	public function getProperties(): ColumnProperties
	{
		return new ColumnProperties($this->getTable()->getColumnDescription($this->getName()));
	}

	public function getSql(&$context = [])
	{
		return implode('.', [
			$this->getTable()->getSql($context),
			$this->getName() == '*' ? '*' : $this->getName(),
		]);
	}
}
