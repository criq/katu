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

		user_error('Undeclared class method ' . $name . '.');
	}

	static function getPDO() {
		if (!defined('static::DATABASE')) {
			throw new Exception("Undefined database.");
		}

		return PDO\Connection::getInstance(static::DATABASE);
	}

	static function getTable() {
		if (!defined('static::TABLE')) {
			throw new Exception("Undefined table.");
		}

		return static::TABLE;
	}

	static function getClass() {
		return get_called_class();
	}

	static function getColumns() {
		$columns = array();

		foreach (static::getPDO()->query(" SHOW COLUMNS FROM " . static::getTable())->fetch_all() as $row) {
			$columns[$row['Field']] = new \Katu\PDO\Column($row);
		}

		return $columns;
	}

	static function getColumn($name) {
		$columns = static::getColumns();
		if (!isset($columns[$name])) {
			throw new Exception("Invalid column " . $name . ".");
		}

		return $columns[$name];
	}

	static function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i->name;
		}, static::getColumns()));
	}

	static function insert($params = array()) {
		$query = static::getPDO()->createQuery();

		$columns = array_keys($params);
		$values  = array_map(function($i) {
			return ':' . $i;
		}, array_keys($params));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ( " . implode(", ", $values) . " ) ";

		$query->setSQL($sql);
		$query->setParams($params);
		$query->getResult();

		return static::get(static::getPDO()->getLastInsertId());
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

			$pk = static::getIDColumnName();
			$columns = static::getColumnNames();
			$properties = array();

			foreach (get_object_vars($this) as $property => $value) {
				if (in_array($property, $columns) && $property != $pk) {
					$properties[$property] = $value;
				}
			}

			if ($properties) {
				static::getPDO()->update(static::getTable(), $properties, array(
				static::getIDColumnName() => $this->getID(),
				));
			}

			$this->__updated = FALSE;
		}

		return TRUE;
	}

	public function delete() {
		return static::getPDO()->delete(static::getTable(), array(
			$this->getIDColumnName() => $this->getID(),
		));
	}

	static function getIDColumnName() {
		foreach (static::getPDO()->createQuery(" DESCRIBE " . static::getTable())->getResult()->getAssoc() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
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

	static function getBy($params = array(), $meta = array()) {
		$query = static::getPDO()->createQuery();

		$sql = " SELECT SQL_CALC_FOUND_ROWS * FROM " . static::getTable() . " WHERE ( 1 ) ";

		foreach (static::filterParams($params) as $param => $value) {
			$sql .= " AND ( " . $param . " = :" . $param . " ) ";

			$query->setParam($param, $value);
		}

		foreach ((array) $meta as $_meta) {
			if ($_meta instanceof PDO\Meta\Page) {
				$sql .= " LIMIT :offset, :limit ";

				$query->setParam('offset', $_meta->getOffset(), \PDO::PARAM_INT);
				$query->setParam('limit', $_meta->getLimit(), \PDO::PARAM_INT);
				$query->setMeta($_meta);
			}
		}

		$query->setSQL($sql);

		return $query->getClassResult(static::getClass());
	}

	static function get($primaryKey) {
		return static::getOneBy(array(static::getIDColumnName() => $primaryKey));
	}

	public function getID() {
		return $this->{static::getIDColumnName()};
	}

	static function getOneBy() {
		return call_user_func_array(array('static', 'getBy'), func_get_args())->getOne();
	}

	static function getAll($meta = array()) {
		return static::getBy(array(), $meta);
	}

	static function getByQuery($sql) {
		return static::getPDO()->queryClass(static::getClass(), $sql);
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

	static function getIDProperties() {
		return array_values(array_filter(array_map(function($i) {
			return preg_match('#^(?<property>[a-z_]+)_id$#', $i) ? $i : NULL;
		}, static::getColumnNames())));
	}

	public function getBoundObject($model) {
		$ns_model = '\\App\\Models\\' . $model;
		if (!class_exists($ns_model)) {
			return FALSE;
		}

		foreach (static::getIDProperties() as $property) {
			$_model = '\\App\\Models\\' . implode(array_map('ucfirst', explode('_', substr($property, 0, -3))));
			if ($_model && $ns_model == $_model) {
				$object = $_model::get($this->{$property});
				if ($object) {
					return $object;
				}
			}
		}

		return FALSE;
	}

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return FALSE;
	}

	static function getColumnUniqueID($column) {
		$columns = static::getColumns();
		if (!$columns[$column]->length) {
			throw new Exception("Unable to get column length.");
		}

		do {

			$string = \Katu\Utils\Random::getIDString($columns[$column]->length);

		} while (static::getOneBy(array($column => $string)));

		return $string;
	}

}
