<?php

namespace Katu;

class Model extends ReadOnlyModel {

	protected $__updated = FALSE;

	public function __call($name, $args) {
		// Setter.
		if (preg_match('#^set(?<property>[a-z]+)$#i', $name, $match) && count($args) == 1) {
			$property = $this->getPropertyName($match['property']);
			$value    = $args[0];

			if ($property && $this->update($property, $value)) {
				return TRUE;
			}
		}

		return parent::__call($name, $args);
	}

	static function insert($bindValues = array()) {
		$query = static::getPdo()->createQuery();

		$columns = array_keys($bindValues);
		$values  = array_map(function($i) {
			return ':' . $i;
		}, array_keys($bindValues));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ( " . implode(", ", $values) . " ) ";

		$query->setSql($sql);
		$query->setBindValues($bindValues);
		$query->getResult();

		return static::get(static::getPdo()->getLastInsertId());
	}

	public function update($property, $value) {
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
				$this->__updated = TRUE;
			}

			return TRUE;
		}

		return FALSE;
	}

	public function save() {
		if ($this->__updated) {

			$columns = static::getTable()->getColumnNames();

			$bindValues = array();
			foreach (get_object_vars($this) as $name => $value) {
				if (in_array($name, $columns) && $name != static::getIdColumnName()) {
					$bindValues[$name] = $value;
				}
			}

			$set = array();
			foreach ($bindValues as $name => $value) {
				$set[] = $name . " = :" . $name;
			}

			if ($set) {

				$query = static::getPdo()->createQuery();

				$sql = " UPDATE " . static::getTable() . " SET " . implode(", ", $set) . " WHERE ( " . $this->getIdColumnName() . " = :" . $this->getIdColumnName() . " ) ";

				$query->setSql($sql);
				$query->setBindValues($bindValues);
				$query->setBindValue(static::getIdColumnName(), $this->getId());
				$query->getResult();

			}

			$this->__updated = FALSE;
		}

		return TRUE;
	}

	public function delete() {
		$query = static::getPdo()->createQuery();

		$sql = " DELETE FROM " . static::getTable() . " WHERE " . static::getIdColumnName() . " = :" . static::getIdColumnName();

		$query->setSql($sql);
		$query->setBindValue(static::getIdColumnName(), $this->getId());

		return $query->getResult();
	}

}
