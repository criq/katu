<?php

namespace Katu;

use \App\Models\File;
use \App\Models\FileAttachment;
use \Sexy\Select;
use \Sexy\CmpIn;
use \Sexy\OrderBy;
use \Sexy\Page;
use \Sexy\Keyword;
use \Sexy\Expression;

class ReadOnlyModel {

	public function __toString() {
		return (string) $this->getId();
	}

	public function __call($name, $args) {
		// Bind getter.
		if (preg_match('#^get(?<property>[a-z]+)$#i', $name, $match) && count($args) == 0) {
			return $this->getBoundObject($match['property']);
		}

		trigger_error('Undeclared class method ' . $name . '.');
	}

	static function getAppModels() {
		$dir = BASE_DIR . '/app/Models/';
		$ns = '\\App\\Models';

		$models = [];

		foreach (scandir($dir) as $file) {
			$path = $dir . $file;
			if (is_file($path)) {
				$pathinfo = pathinfo($file);
				$model = $ns . '\\' . $pathinfo['filename'];
				if (class_exists($model)) {
					$models[] = ltrim($model, '\\');
				}
			}
		}

		natsort($models);

		return $models;
	}

	static function getClass() {
		return get_called_class();
	}

	static function getAppClass() {
		return implode(array_slice(explode('\\', static::getClass()), -1, 1));
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

	static function createQuery() {
		// Sql expression.
		if (
			count(func_get_args()) == 1
			&& func_get_arg(0) instanceof Expression
		) {

			$query = static::getPdo()->createClassQueryFromSql(static::getClass(), func_get_arg(0));

		// Raw sql and bind values.
		} elseif (
			count(func_get_args()) == 2
		) {

			$query = static::getPdo()->createClassQuery(static::getClass(), func_get_arg(0), func_get_arg(1));

		// Raw sql.
		} elseif (
			count(func_get_args()) == 1
		) {

			$query = static::getPdo()->createClassQuery(static::getClass(), func_get_arg(0));

		} else {

			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments passed to query.");

		}

		return $query;
	}

	static function getIdColumn() {
		return static::getColumn(static::getIdColumnName());
	}

	static function getIdColumnName() {
		foreach (static::getPdo()->createQuery(" DESCRIBE " . static::getTable())->getResult() as $row) {
			if (isset($row['Key']) && $row['Key'] == 'PRI') {
				return $row['Field'];
			}
		}

		return false;
	}

	public function getId() {
		return $this->{static::getIdColumnName()};
	}

	static function filterParams($params) {
		$_params = [];

		foreach ($params as $param => $value) {
			if (is_string($param)) {
				$_params[$param] = $value;
			}
		}

		return $_params;
	}

	static function getBy($params = [], $expressions = []) {
		$pdo = static::getPdo();
		$query = $pdo->createQuery();
		$query->setClass(static::getClass());

		$sql = new Select();
		$sql->addExpressions($expressions);
		$sql->from(static::getTable());

		foreach ($params as $name => $value) {
			if ($value instanceof Expression) {
				$sql->where($value);
			} else {
				$sql->whereEq(static::getColumn($name), $value);
			}
		}

		$query->setFromSql($sql);

		return $query->getResult();
	}

	static function get($primaryKey) {
		return static::getOneBy([
			static::getIdColumnName() => $primaryKey,
		]);
	}

	static function getOneBy() {
		return call_user_func_array(['static', 'getBy'], array_merge(func_get_args(), [[new Page(1, 1)]]))->getOne();
	}

	static function getAll($options = []) {
		return static::getBy([], $options);
	}

	static function getOneOrCreateWithArray($getBy, $array = []) {
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
			$object = call_user_func_array(['static', 'create'], array_slice(func_get_args(), 1));
		}

		return $object;
	}

	static function getFromAssoc($array) {
		if (!$array) {
			return false;
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
			return preg_match('#^(?<property>[a-zA-Z_]+)_?[Ii][Dd]$#', $i) ? $i : null;
		}, static::getTable()->getColumnNames())));
	}

	public function getBoundObject($model) {
		$nsModel = '\\App\\Models\\' . $model;
		if (!class_exists($nsModel)) {
			return null;
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

		return null;
	}

	static function getPropertyName($property) {
		$properties = array_merge(array_keys(get_class_vars(get_called_class())), static::getTable()->getColumnNames());

		foreach ($properties as $_property) {
			if (strtolower($_property) === strtolower($property)) {
				return $_property;
			}
		}

		return false;
	}

	public function getFileAttachments($properties = [], $options = []) {
		$properties['objectModel'] = $this->getClass();
		$properties['objectId']    = $this->getId();

		return FileAttachment::getBy($properties, $options);
	}

	public function getImageFileAttachments($properties = [], $expressions = []) {
		$sql = (new Select(FileAttachment::getTable()))
			->from(FileAttachment::getTable())
			->joinColumns(FileAttachment::getColumn('fileId'), File::getColumn('id'))
			->whereIn(File::getColumn('type'), ['image/jpeg', 'image/png', 'image/gif'])
			->whereEq(FileAttachment::getColumn('objectModel'), (string) $this->getClass())
			->whereEq(FileAttachment::getColumn('objectId'), (int) $this->getId())
			->orderBy(new OrderBy(FileAttachment::getColumn('timeCreated'), new Keyword('DESC')))
			;

		$sql->addExpressions($expressions);

		return FileAttachment::createQuery($sql)->getResult();
	}

	public function getImageFile() {
		$imageAttachments = $this->getImageFileAttachments();
		if ($imageAttachments->getTotal()) {
			return $imageAttachments[0]->getFile();
		}

		return false;
	}

	public function getImagePath() {
		$file = $this->getImageFile();
		if ($file) {
			return $file->getPath();
		}

		return false;
	}

	public function hasImage() {
		$path = $this->getImagePath();

		return $path && file_exists($path);
	}

	static function materialize($materializedTable) {
		$pdo = static::getPdo();

		// Delete original table.
		try {
			$sql = " DROP TABLE `" . $materializedTable->name . "` ";
			$pdo->createQuery($sql)->getResult();
		} catch (\Exception $e) {

		}

		// Create table and copy the data.
		$sql = " CREATE TABLE `" . $materializedTable->name . "` AS SELECT * FROM `" . static::getTable()->name . "`";
		$pdo->createQuery($sql)->getResult();

		// Disable NULL.

		// Create indices.
		foreach ($materializedTable->getColumns() as $column) {
			if (in_array($column->getProperties()->type, ['int', 'double', 'char', 'varchar'])) {
				$sql = " ALTER TABLE `" . $materializedTable->name . "` ADD INDEX (`" . $column->name . "`) ";
				$pdo->createQuery($sql)->getResult();
			}
		}

		return true;
	}

}
