<?php

namespace Katu\Doctrine;

class Entity {

	static function getPropertyNames() {
		return array_keys(get_class_vars(get_called_class()));
	}

	static function getPropertyName($name) {
		foreach (static::getPropertyNames() as $property) {
			if (strtolower($name) == strtolower($property)) {
				return $property;
			}
		}

		return FALSE;
	}

	public function __call($name, $args) {
		// Property getter.
		if (in_array($name, static::getPropertyNames()) && !$args) {
			return $this->$name;
		}

		// Property setter
		if (preg_match('#^set(?<property>.+)$#', $name, $match) && count($args == 1)) {
			$property = self::getPropertyName($match['property']);
			if ($property) {
				return $this->$property = $args[0];
			}
		}

		throw new \Exception("Invalid method " . $name . ".");
	}

	static function getDB($name = NULL) {
		if (is_null($name)) {
			$name = static::DATABASE;
		}

		if (!isset($GLOBALS['doctrine.em'][$name])) {
			$config = new \Doctrine\ORM\Configuration;

			$cache = new \Doctrine\Common\Cache\ApcCache;

			$driverImpl = $config->newDefaultAnnotationDriver(BASE_DIR);
			$config->setMetadataDriverImpl($driverImpl);
			$config->setMetadataCacheImpl($cache);
			$config->setQueryCacheImpl($cache);
			$config->setProxyDir(TMP_PATH);
			$config->setProxyNamespace('DoctrineProxy');

			$config->setAutoGenerateProxyClasses(FALSE);

			$GLOBALS['doctrine.em'][$name] = \Doctrine\ORM\EntityManager::create(\Katu\Config::getDB($name)->getPDOArray(), $config);
		}

		return $GLOBALS['doctrine.em'][$name];
	}

	static function select($alias) {
		$query = new QueryBuilder(static::getDB()->createQueryBuilder(), get_called_class(), $alias);

		return $query;
	}

	static function getTable() {
		return static::getDB()->getRepository(get_called_class());
	}

	static function create($properties) {
		$object = new static;
		foreach ($properties as $property => $value) {
			$f = 'set' . $property;
			$object->$f($value);
		}

		$em = static::getDB();

		$em->persist($object);
		$em->flush();

		return $object;
	}

	public function save() {
		$em = static::getDB();

		$em->merge($this);
		$em->flush();

		return TRUE;
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
