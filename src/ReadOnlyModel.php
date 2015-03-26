<?php

namespace Katu;

use \App\Models\File;
use \App\Models\FileAttachment;
use \Sexy\Select;
use \Sexy\CmpEq;
use \Sexy\CmpNotEq;
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

	static function getTableName() {
		if (!defined('static::TABLE')) {
			throw new \Exception("Undefined table.");
		}

		return static::TABLE;
	}

	static function getTable() {
		return new Pdo\Table(static::getPdo(), static::getTableName());
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

	static function transaction($callback) {
		return call_user_func_array([static::getPdo(), 'transaction'], func_get_args());
	}

	static function getIdColumn() {
		return static::getColumn(static::getIdColumnName());
	}

	static function getIdColumnName() {
		$table = static::getTable();

		return \Katu\Utils\Cache::getRuntime(['databases', $table->pdo->name, 'tables', 'idColumn', $table->name], function() use($table) {
			foreach ($table->pdo->createQuery(" DESCRIBE " . $table)->getResult() as $row) {
				if (isset($row['Key']) && $row['Key'] == 'PRI') {
					return $row['Field'];
				}
			}

			return false;
		});
	}

	public function getId() {
		return $this->{static::getIdColumnName()};
	}

	public function exists() {
		return (bool) static::get($this->getId());
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

	static function getBy($params = [], $expressions = [], $options = []) {
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

		if (isset($options['setOptGetTotalRows'])) {
			$sql->setOptGetTotalRows($options['setOptGetTotalRows']);
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
		$args = array_merge(func_get_args(), [['page' => new Page(1, 1)]], [['setOptGetTotalRows' => false]]);

		return call_user_func_array(['static', 'getBy'], $args)->getOne();
	}

	static function getAll($expressions = []) {
		return static::getBy([], $expressions);
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

	public function getFileAttachments($params = [], $expressions = []) {
		$params['objectModel'] = $this->getClass();
		$params['objectId']    = $this->getId();

		if (!isset($expressions['orderBy'])) {
			$expressions['orderBy'] = FileAttachment::getColumn('position');
		}

		return FileAttachment::getBy($params, $expressions);
	}

	public function refreshFileAttachmentPositions() {
		$position = 0;

		// Refresh the ones with position.
		foreach ($this->getFileAttachments([
			new CmpNotEq(FileAttachment::getColumn('position'), 0),
		], [
			'orderBy' => FileAttachment::getColumn('position'),
		]) as $fileAttachment) {
			$fileAttachment->setPosition(++$position);
			$fileAttachment->save();
		}

		// Refresh the ones without position.
		foreach ($this->getFileAttachments([
			new CmpEq(FileAttachment::getColumn('position'), 0),
		], [
			'orderBy' => FileAttachment::getColumn('timeCreated'),
		]) as $fileAttachment) {
			$fileAttachment->setPosition(++$position);
			$fileAttachment->save();
		}

		return true;
	}

	public function getImageFileAttachments($expressions = []) {
		$sql = (new Select(FileAttachment::getTable()))
			->from(FileAttachment::getTable())
			->joinColumns(FileAttachment::getColumn('fileId'), File::getColumn('id'))
			->whereIn(File::getColumn('type'), ['image/jpeg', 'image/png', 'image/gif'])
			->whereEq(FileAttachment::getColumn('objectModel'), (string) $this->getClass())
			->whereEq(FileAttachment::getColumn('objectId'), (int) $this->getId())
			->orderBy([
				new OrderBy(FileAttachment::getColumn('position')),
				new OrderBy(FileAttachment::getColumn('timeCreated'), new Keyword('desc')),
			])
			->addExpressions($expressions)
			;

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

}
