<?php

namespace Katu\Doctrine;

class Entity {

	static function getPropertyNames() {
		return array_keys(get_class_vars(get_called_class()));
	}

	public function __call($name, $args) {
		// Property getter.
		if (in_array($name, self::getPropertyNames()) && !$args) {
			return $this->$name;
		}
	}

	static function getDB($name = NULL) {
		if (is_null($name)) {
			$name = static::DATABASE;
		}

		$pdo    = \Katu\Config::getDB($name)->getPDOArray();
		$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(), TRUE);
		$em     = \Doctrine\ORM\EntityManager::create($pdo, $config);

		return $em;
	}

	static function select($alias) {
		$query = new QueryBuilder(self::getDB()->createQueryBuilder(), $alias);

		return $query;
	}

	static function getTable() {
		return self::getDB()->getRepository(get_called_class());
	}

	static function find() {
		$table = static::getTable();

		return call_user_func_array(array($table, 'find'), func_get_args());
	}

	static function findBy() {
		$table = static::getTable();

		return call_user_func_array(array($table, 'findBy'), func_get_args());
	}

	static function findOneBy() {
		$table = static::getTable();

		return call_user_func_array(array($table, 'findOneBy'), func_get_args());
	}

	static function findAll() {
		$table = static::getTable();

		return call_user_func_array(array($table, 'findAll'), func_get_args());
	}

}
