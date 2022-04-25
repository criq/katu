<?php

namespace Katu\PDO;

class Column extends \Sexy\Expression
{
	public $name;
	public $table;

	public function __construct(Table $table, Name $name)
	{
		$this->setTable($table);
		$this->setName($name);
	}

	public function __toString(): string
	{
		return $this->getSql();
	}

	public function setTable(Table $value): Column
	{
		$this->table = $value;

		return $this;
	}

	public function getTable(): Table
	{
		return $this->table;
	}

	public function setName(Name $value): Column
	{
		$this->name = $value;

		return $this;
	}

	public function getName(): Name
	{
		return $this->name;
	}

	public function getDescription(): ColumnDescription
	{
		return $this->getTable()->getColumnDescriptions()->filterByName($this->getName()->getPlain())[0];
	}

	public function getSql(&$context = [])
	{
		return implode(".", [
			$this->getTable()->getSql($context),
			$this->getName() == "*" ? "*" : $this->getName(),
		]);
	}
}
