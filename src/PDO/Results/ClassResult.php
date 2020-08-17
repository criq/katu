<?php

namespace Katu\PDO\Results;

class ClassResult extends PaginatedResult
{
	public $className;

	public function __construct(\Katu\PDO\Connection $connection, \PDOStatement $statement, ?\Sexy\Page $page = null, ?\Katu\Tools\Classes\ClassName $className = null)
	{
		parent::__construct($connection, $statement, $page);

		$this->className = $className;
	}

	public function getObjects(\Katu\Tools\Classes\ClassName $className = null)
	{
		if (!$className && $this->className) {
			$className = $this->className;
		}

		$this->setIteratorArray($className);

		return $this->iteratorArray;
	}

	public function getOne(\Katu\Tools\Classes\ClassName $className = null, int $offset = 0)
	{
		if (!$className && $this->className) {
			$className = $this->className;
		}

		$objects = $this->getObjects($className);

		return $objects[$offset] ?? null;
	}

	public function getRandomOne(\Katu\Tools\Classes\ClassName $className = null)
	{
		if ($this->getCount()) {
			return $this->getOne($className, rand(0, $this->getCount() - 1));
		}

		return false;
	}

	public function getPropertyValues($property)
	{
		$values = [];

		foreach ($this as $object) {
			$values[] = $object->$property;
		}

		return $values;
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function setIteratorArray(\Katu\Tools\Classes\ClassName $className = null)
	{
		if (is_null($this->iteratorArray)) {
			if (!$className && $this->className) {
				$className = $this->className;
			}

			$this->iteratorArray = [];
			while ($object = $this->statement->fetchObject((string)$className)) {
				$this->iteratorArray[] = $object;
			}
		}
	}
}
