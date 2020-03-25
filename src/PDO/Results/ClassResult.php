<?php

namespace Katu\PDO\Results;

class ClassResult extends PaginatedResult
{
	public function __construct($pdo, $statement, $page, $class)
	{
		parent::__construct($pdo, $statement, $page);

		$this->class = $class;
	}

	public function getObjects($class = null)
	{
		if (!$class && $this->class) {
			$class = $this->class;
		}

		$this->setIteratorArray($class);

		return $this->iteratorArray;
	}

	public function getOne($class = null, $offset = 0)
	{
		if (!$class && $this->class) {
			$class = $this->class;
		}

		$objects = $this->getObjects();
		if (!isset($objects[$offset])) {
			return false;
		}

		$object = $objects[$offset];
		if ($object && method_exists($object, 'save')) {
			$object->save();
		}

		return $object;
	}

	public function getRandomOne($class = null)
	{
		if ($this->getCount()) {
			return $this->getOne($class, rand(0, $this->getCount() - 1));
		}

		return false;
	}

	public function getPropertyValues($property)
	{
		$values = array();

		foreach ($this as $object) {
			$values[] = $object->$property;
		}

		return $values;
	}

	/****************************************************************************
	 * ArrayAccess.
	 */
	public function setIteratorArray($class = null)
	{
		if (is_null($this->iteratorArray)) {
			if (!$class && $this->class) {
				$class = $this->class;
			}

			$this->iteratorArray = [];
			while ($object = $this->statement->fetchObject($class)) {
				$this->iteratorArray[] = $object;
			}
		}
	}
}
