<?php

namespace Katu;

use \Katu\PDO\Meta\Select;
use \Katu\PDO\Meta\GroupBy;
use \Katu\PDO\Meta\OrderBy;
use \Katu\PDO\Meta\Page;

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

	static function getPDO() {
		if (!defined('static::DATABASE')) {
			throw new \Exception("Undefined database.");
		}

		return PDO\Connection::getInstance(static::DATABASE);
	}

	static function getTable() {
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return static::TABLE;
	}

	static function getClass() {
		return get_called_class();
	}

	static function getColumns() {
		$columns = array();

		foreach (static::getPDO()->createQuery(" DESCRIBE " . static::getTable())->getResult() as $row) {
			$columns[$row['Field']] = new \Katu\PDO\Column($row);
		}

		return $columns;
	}

	static function getColumn($name) {
		$columns = static::getColumns();
		if (!isset($columns[$name])) {
			throw new \Exception("Invalid column " . $name . ".");
		}

		return $columns[$name];
	}

	static function getColumnNames() {
		return array_values(array_map(function($i) {
			return $i->name;
		}, static::getColumns()));
	}

	static function query($sql = NULL, $params = array(), $setStaticClass = TRUE) {
		$query = static::getPDO()->createQuery($sql, $params);
		if ($setStaticClass) {
			$query->setClass(static::getClass());
		}

		return $query;
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

			$columns = static::getColumnNames();

			$params = array();
			foreach (get_object_vars($this) as $param => $value) {
				if (in_array($param, $columns) && $param != static::getIDColumnName()) {
					$params[$param] = $value;
				}
			}

			$set = array();
			foreach ($params as $param => $value) {
				$set[] = $param . " = :" . $param;
			}

			if ($set) {

				$query = static::getPDO()->createQuery();

				$sql = " UPDATE " . static::getTable() . " SET " . implode(", ", $set) . " WHERE ( " . $this->getIDColumnName() . " = :" . $this->getIDColumnName() . " ) ";

				$query->setSQL($sql);
				$query->setParams($params);
				$query->setParam(static::getIDColumnName(), $this->getID());
				$query->getResult();

			}

			$this->__updated = FALSE;
		}

		return TRUE;
	}

	public function delete() {
		$query = static::getPDO()->createQuery();

		$sql = " DELETE FROM " . static::getTable() . " WHERE " . static::getIDColumnName() . " = :" . static::getIDColumnName();

		$query->setSQL($sql);
		$query->setParam(static::getIDColumnName(), $this->getID());

		return $query->getResult();
	}

	static function getIDColumnName() {
		foreach (static::getPDO()->createQuery(" DESCRIBE " . static::getTable())->getResult() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return FALSE;
	}

	public function getID() {
		return $this->{static::getIDColumnName()};
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
		$query->setClass(static::getClass());

		$metaSelectUsed = FALSE;

		$sql = " SELECT SQL_CALC_FOUND_ROWS ";

		foreach ((array) $meta as $_meta) {

			if ($_meta instanceof Select) {
				$sql .= $_meta->getSelect();
				$metaSelectUsed = TRUE;
			}

		}

		if (!$metaSelectUsed) {
			$sql .= " * ";
		}

		$sql .= " FROM " . static::getTable() . " WHERE ( 1 ) ";

		foreach (static::filterParams($params) as $param => $value) {

			if ($value instanceof PDO\Expressions\Expression) {

				$sql .= " AND ( " . $value->getWhereConditionSQL($param) . " ) ";
				$query->setParam($param, $value->getValue());

			} else {

				$sql .= " AND ( " . $param . " = :" . $param . " ) ";
				$query->setParam($param, $value);

			}

		}

		foreach ((array) $meta as $_meta) {

			if ($_meta instanceof GroupBy) {
				$sql .= " GROUP BY " . $_meta->getGroupBy();
			}

			if ($_meta instanceof OrderBy) {
				$sql .= " ORDER BY " . $_meta->getOrderBy();
			}

			if ($_meta instanceof Page) {
				$sql .= " LIMIT :offset, :limit ";

				$query->setParam('offset', $_meta->getOffset(), \PDO::PARAM_INT);
				$query->setParam('limit', $_meta->getLimit(), \PDO::PARAM_INT);
				$query->setPage($_meta);
			}

		}

		$query->setSQL($sql);

		return $query->getResult();
	}

	static function get($primaryKey) {
		return static::getOneBy(array(static::getIDColumnName() => $primaryKey));
	}

	static function getOneBy() {
		return call_user_func_array(array('static', 'getBy'), array_merge(func_get_args(), array(array(new Page(1, 1)))))->getOne();
	}

	static function getAll($meta = array()) {
		return static::getBy(array(), $meta);
	}

	static function getByQuery($sql, $params = array()) {
		$query = static::getPDO()->createQuery($sql, $params);
		$query->setClass(static::getClass());

		return $query->getResult();
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
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : NULL;
		}, static::getColumnNames())));
	}

	public function getBoundObject($model) {
		$nsModel = '\\App\\Models\\' . $model;
		if (!class_exists($nsModel)) {
			return FALSE;
		}

		foreach (static::getIDProperties() as $property) {
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
			throw new \Exception("Unable to get column length.");
		}

		do {

			$string = \Katu\Utils\Random::getIDString($columns[$column]->length);

		} while (static::getOneBy(array($column => $string)));

		return $string;
	}

}
