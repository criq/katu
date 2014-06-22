<?php

namespace Katu;

class Model {

	protected $__updated = FALSE;
	static $__definition = array();

	public function __call($name, $args) {
		// Setter.
		if (preg_match('#^set(?<property>[a-z]+)$#i', $name, $match) && count($args) == 1) {
			$property = $this->getPropertyName($match['property']);
			$value    = $args[0];

			if ($property && $this->update($property, $value)) {
				return TRUE;
			}
		}

		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			$object = $this->getBoundObject($match['property']);
			if ($object) {
				return $object;
			}
		}

		trigger_error('Undeclared class method ' . $name . '.');
	}

	static function getClass() {
		return get_called_class();
	}

	static function getPdo() {
		if (!defined('static::DATABASE')) {
			throw new \Exception("Undefined database.");
		}

		return Pdo\Connection::getInstance(static::DATABASE);
	}

	static function getTable() {
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return new Pdo\Table(static::getPdo(), static::TABLE);
	}

	static function getColumn($name) {
		return new Pdo\Column(static::getTable(), $name);
	}

	static function query($sql = NULL, $params = array(), $setStaticClass = TRUE) {
		$query = static::getPdo()->createQuery($sql, $params);
		if ($setStaticClass) {
			$query->setClass(static::getClass());
		}

		return $query;
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
				$query->setBindValue(static::getIdColumnName(), $this->getID());
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
		$query->setBindValue(static::getIdColumnName(), $this->getID());

		return $query->getResult();
	}

	static function getIdColumnName() {
		foreach (static::getPdo()->createQuery(" DESCRIBE " . static::getTable())->getResult() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	public function getID() {
		return $this->{static::getIdColumnName()};
	}

	static function filterParams($params) {
		$_params = array();

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$_params[$param] = $value;
			}
		}

		return $_params;
	}

	static function getBy($params = array(), $options = array()) {
		$pdo = static::getPdo();
		$query = $pdo->createQuery();
		$query->setClass(static::getClass());

		$sql = new Pdo\Expressions\Select();
		$sql->setOptions($options);
		$sql->from(static::getTable());

		foreach ($params as $name => $value) {
			if ($value instanceof Pdo\Expression) {
				$sql->where($value);
			} else {
				$sql->where(new Pdo\Expressions\CmpEq(static::getColumn($name), $value));
			}
		}

		$query->setFromSql($sql);

		return $query->getResult();
	}

	static function get($primaryKey) {
		return static::getOneBy(array(static::getIdColumnName() => $primaryKey));
	}

	static function getOneBy() {
		return call_user_func_array(array('static', 'getBy'), array_merge(func_get_args(), array(array(new Pdo\Expressions\Page(1, 1)))))->getOne();
	}

	static function getAll($options = array()) {
		return static::getBy(array(), $options);
	}

	static function getOneOrCreateWithArray($getBy, $array = array()) {
		$object = static::getOneBy($getBy);
		if (!$object) {
			$properties = array_merge($getBy, $array);
			$object = static::create($properties);
		}

		return $object;
	}

	static function getOneOrCreateWithList($getBy) {
		$object = static::getOneBy($getBy);
		if (!$object) {
			$object = call_user_func_array(array('static', 'create'), array_slice(func_get_args(), 1));
		}

		return $object;
	}

	static function getFromAssoc($array) {
		if (!$array) {
			return FALSE;
		}

		$class = static::getClass();
		$object = new $class;

		foreach ($array as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}

	static function getIdProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : NULL;
		}, static::getTable()->getColumnNames())));
	}

	public function getBoundObject($model) {
		$nsModel = '\\App\\Models\\' . $model;
		if (!class_exists($nsModel)) {
			return FALSE;
		}

		foreach (static::getIdProperties() as $property) {
			$proposedModel = '\\App\\Models\\' . ucfirst(preg_replace('#^(.+)_?[Ii][Dd]$#', '\\1', $property));
			if ($proposedModel && $nsModel == $proposedModel) {
				$object = $proposedModel::get($this->{$property});
				if ($object) {
					return $object;
				}
			}
		}

		return FALSE;
	}

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return FALSE;
	}

	static function getColumnUniqueID($column) {
		$columns = static::getColumnDescriptions();
		if (!$columns[$column]->length) {
			throw new \Exception("Unable to get column length.");
		}

		do {

			$string = \Katu\Utils\Random::getIDString($columns[$column]->length);

		} while (static::getOneBy(array($column => $string)));

		return $string;
	}

}
