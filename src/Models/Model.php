<?php

namespace Katu\Models;

use \App\Models\File;
use \App\Models\FileAttachment;
use \Sexy\Sexy as SX;

class Model extends Base
{
	const CACHE_IN_MEMORY_BY_PRIMARY_KEY = false;

	protected $__updated = false;

	public function __call($name, $args)
	{
		// Setter.
		if (preg_match('#^set(?<property>[a-z0-9]+)$#i', $name, $match) && count($args) == 1) {
			$property = $this->getPropertyName($match['property']);

			// Not found, maybe just added.
			if (!$property) {
				\Katu\Cache\General::clearMemory();
				$property = $this->getPropertyName($match['property']);
			}

			$value = $args[0];

			if ($property && $this->update($property, $value)) {
				return true;
			}
		}

		return parent::__call($name, $args);
	}

	public static function insert($bindValues = [])
	{
		$query = static::getConnection()->createQuery();

		$columns = array_map(function ($i) {
			return new \Katu\PDO\Name($i);
		}, array_keys($bindValues));
		$values  = array_map(function ($i) {
			return ':' . $i;
		}, array_keys($bindValues));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ( " . implode(", ", $values) . " ) ";

		$query->setSql($sql);
		$query->setBindValues($bindValues);
		$query->getResult();

		static::change();

		$primaryKey = static::getConnection()->getLastInsertId();
		if ($primaryKey) {
			return static::get($primaryKey);
		} else {
			throw new \Katu\Exceptions\NoPrimaryKeyReturnedException;
		}
	}

	public static function insertMultiple($items = [])
	{
		$items = array_values($items);

		$query = static::getConnection()->createQuery();

		$columns = array_map(function ($i) {
			return new \Katu\PDO\Name($i);
		}, array_keys($items[0]));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ";

		$bindValues = [];
		$sqlRows = [];
		foreach ($items as $row => $values) {
			$sqlRowParams = [];
			foreach ($values as $key => $value) {
				$bindValueKey = implode('_', [
					'row',
					$row,
					$key,
				]);
				$bindValues[$bindValueKey] = $value;
				$sqlRowParams[] = ":" . $bindValueKey;
			}
			$sqlRows[] = " ( " . implode(', ', $sqlRowParams) . " ) ";
		}

		$sql .= implode(", ", $sqlRows);

		$query->setSql($sql);
		$query->setBindValues($bindValues);
		$query->getResult();

		static::change();

		return static::get(static::getConnection()->getLastInsertId());
	}

	public static function upsert($getByParams, $insertParams = [], $updateParams = [])
	{
		$object = static::getOneBy($getByParams);
		if ($object) {
			foreach ($updateParams as $name => $value) {
				$object->update($name, $value);
			}
			$object->save();
		} else {
			$object = static::insert(array_merge((array) $getByParams, (array) $insertParams, (array) $updateParams));
		}

		return $object;
	}

	public function update($property, $value = null)
	{
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
				$this->__updated = true;
			}

			static::change();

			return true;
		}

		return false;
	}

	public function delete()
	{
		$query = static::getConnection()->createQuery();

		// Delete file attachments.
		if (class_exists('\\App\\Models\\FileAttachment')) {
			foreach ($this->getFileAttachments() as $fileAttachment) {
				$fileAttachment->delete();
			}
		}

		$sql = " DELETE FROM " . static::getTable() . " WHERE " . static::getIdColumnName() . " = :" . static::getIdColumnName();

		$query->setSql($sql);
		$query->setBindValue(static::getIdColumnName(), $this->getId());

		$res = $query->getResult();

		static::change();

		return $res;
	}

	public function save()
	{
		if ($this->isUpdated()) {
			$columnsNames = array_map(function ($columnName) {
				return $columnName->getName();
			}, static::getTable()->getColumnNames());

			$bindValues = [];
			foreach (get_object_vars($this) as $name => $value) {
				if (in_array($name, $columnsNames) && $name != static::getIdColumnName()) {
					$bindValues[$name] = $value;
				}
			}

			$set = [];
			foreach ($bindValues as $name => $value) {
				$set[] = (new \Katu\PDO\Name($name)) . " = :" . $name;
			}

			if ($set) {
				$query = static::getConnection()->createQuery();

				$sql = " UPDATE " . static::getTable() . " SET " . implode(", ", $set) . " WHERE ( " . $this->getIdColumnName() . " = :" . $this->getIdColumnName() . " ) ";

				$query->setSql($sql);
				$query->setBindValues($bindValues);
				$query->setBindValue(static::getIdColumnName(), $this->getId());
				$query->getResult();
			}

			static::change();

			$this->__updated = false;
		}

		return true;
	}

	public static function change()
	{
		static::getTable()->touch();

		return null;
	}

	public function isUpdated()
	{
		return (bool) $this->__updated;
	}

	public static function getAppModels()
	{
		$dir = \Katu\App::getBaseDir() . '/app/Models/';
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

	public static function getIdColumn()
	{
		return static::getColumn(static::getIdColumnName());
	}

	public static function getIdColumnName()
	{
		$table = static::getTable();

		return \Katu\Cache\General::get(['databases', $table->getConnection()->name, 'tables', 'idColumn', $table->name->name], 86400, function () use ($table) {
			foreach ($table->getConnection()->createQuery(" DESCRIBE " . $table)->getResult() as $row) {
				if (isset($row['Key']) && $row['Key'] == 'PRI') {
					return $row['Field'];
				}
			}

			return false;
		});
	}

	public function getId()
	{
		return $this->{static::getIdColumnName()};
	}

	public function getTransmittableId()
	{
		return base64_encode(\Katu\Files\Formats\JSON::encodeStandard([
			'class' => $this->getClass(),
			'id'    => $this->getId(),
		]));
	}

	public static function getFromTransmittableId($transmittableId)
	{
		try {
			$array = \Katu\Files\Formats\JSON::decodeAsArray(base64_decode($transmittableId));
			$class = '\\' . ltrim($array['class'], '\\');

			return $class::get($array['id']);
		} catch (\Exception $e) {
			return false;
		}
	}

	public static function get($primaryKey)
	{
		$callback = function ($class, $primaryKey) {
			return $class::getOneBy([
				$class::getIdColumnName() => $primaryKey,
			]);
		};

		if (static::CACHE_IN_MEMORY_BY_PRIMARY_KEY) {
			return \Katu\Cache\General::get(['model', 'get'], 86400, $callback, static::getClass(), $primaryKey);
		} else {
			return call_user_func_array($callback, [static::getClass(), $primaryKey]);
		}
	}

	public function exists()
	{
		return (bool) static::get($this->getId());
	}

	public static function getOneOrCreateWithArray($getBy, $array = [])
	{
		$object = static::getOneBy($getBy);
		if (!$object) {
			$properties = array_merge($getBy, $array);
			$object = static::insert($properties);
		}

		return $object;
	}

	public static function getOneOrCreateWithList($getBy)
	{
		$object = static::getOneBy($getBy);
		if (!$object) {
			$object = call_user_func_array(['static', 'create'], array_slice(func_get_args(), 1));
		}

		return $object;
	}

	public function setUniqueColumnValue($column, $chars = null, $length = null)
	{
		if (is_null($chars)) {
			$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		}

		if (is_string($column)) {
			$column = static::getColumn($column);
		}

		if (is_null($length)) {
			$length = $column->getProperties()->length;
		}

		while (true) {
			$string = \Katu\Tools\Random\Generator::getFromChars($chars, $length);
			if (!static::getBy([$column->name->name => $string])->getTotal()) {
				$this->update($column->name->name, $string);
				$this->save();

				return $string;
			}
		}
	}

	public function setUniqueColumnSlug($column, $source, $force = false, $constraints = [])
	{
		// Generate slug.
		$slug = (new \Katu\Types\TString(trim(implode(' ', (array) $source))))->getForUrl([
			'maxLength' => 245,
		]);

		// If there already is a slug, keep it.
		if (!$force && $this->$column) {
			return true;
		}

		// If it's the same, keep it.
		if (!$force && $slug == $this->$column) {
			return true;
		}

		$preg = '^' . $slug . '(\-([0-9]+))?$';

		// Select all already used slugs.
		$sql = SX::select(static::getColumn($column))
			->from(static::getTable())
			->where(SX::cmpNotEq(static::getIdColumn(), $this->getId()))
			->where(SX::cmpRegexp(static::getColumn($column), $preg))
			->addExpressions($constraints)
			;
		$res = static::getConnection()->createQueryFromSql($sql)->getResult();

		// Nothing, keep the slug.
		if (!$res->getCount()) {
			$this->update($column, $slug);
		// There are some, get a new slug.
		} else {
			$suffixes = [];
			foreach ($res->getArray() as $item) {
				preg_match('#' . $preg . '#', $item[$column], $match);
				if (!isset($match[2])) {
					$suffixes[] = 0;
				} else {
					$suffixes[] = (int) $match[2];
				}
			}

			// Sort ascending.
			natsort($suffixes);

			// Find a free suffix;
			$proposedSuffix = 0;
			while (in_array($proposedSuffix, $suffixes)) {
				$proposedSuffix++;
			}

			$this->update($column, implode('-', array_filter([
				$slug,
				$proposedSuffix,
			])));
		}

		$this->save();

		return true;
	}

	public static function checkUniqueColumnValue($whereExpressions, $excludeObject = null)
	{
		$sql = SX::select(static::getTable())
			->from(static::getTable())
			->addExpressions([
				'where' => $whereExpressions,
			])
			;

		if (!is_null($excludeObject)) {
			$sql->where(SX::cmpNotEq(static::getIdColumn(), $excludeObject->getId()));
		}

		return !static::getBySql($sql)->getTotal();
	}

	public function getFileAttachments($params = [], $expressions = [])
	{
		$params['objectModel'] = $this->getClass();
		$params['objectId']    = $this->getId();

		if (!isset($expressions['orderBy'])) {
			$expressions['orderBy'] = \Katu\Models\Presets\FileAttachment::getColumn('position');
		}

		return \Katu\Models\Presets\FileAttachment::getBy($params, $expressions);
	}

	public function refreshFileAttachmentsFromFileIds($user, $fileIds)
	{
		$this->getFileAttachments()->each('delete');

		foreach ((array) $fileIds as $key => $fileId) {
			$file = \Katu\Models\Presets\File::get($fileId);
			if ($file) {
				$fileAttachment = $file->attachTo($user, $this);
				$fileAttachment->update('position', $key + 1);
				$fileAttachment->save();
			}
		}

		return true;
	}

	public function refreshFileAttachmentPositions()
	{
		$position = 0;

		// Refresh the ones with position.
		foreach ($this->getFileAttachments([
			SX::cmpNotEq(\Katu\Models\Presets\FileAttachment::getColumn('position'), 0),
		], [
			'orderBy' => \Katu\Models\Presets\FileAttachment::getColumn('position'),
		]) as $fileAttachment) {
			$fileAttachment->setPosition(++$position);
			$fileAttachment->save();
		}

		// Refresh the ones without position.
		foreach ($this->getFileAttachments([
			SX::eq(\Katu\Models\Presets\FileAttachment::getColumn('position'), 0),
		], [
			'orderBy' => \Katu\Models\Presets\FileAttachment::getColumn('timeCreated'),
		]) as $fileAttachment) {
			$fileAttachment->setPosition(++$position);
			$fileAttachment->save();
		}

		return true;
	}

	public function getImageFileAttachments($expressions = [])
	{
		$sql = SX::select(\Katu\Models\Presets\FileAttachment::getTable())
			->from(\Katu\Models\Presets\FileAttachment::getTable())
			->joinColumns(\Katu\Models\Presets\FileAttachment::getColumn('fileId'), \Katu\Models\Presets\File::getColumn('id'))
			->whereIn(\Katu\Models\Presets\File::getColumn('type'), [
				'image/jpeg',
				'image/png',
				'image/gif',
			])
			->whereEq(\Katu\Models\Presets\FileAttachment::getColumn('objectModel'), (string) $this->getClass())
			->whereEq(\Katu\Models\Presets\FileAttachment::getColumn('objectId'), (int) $this->getId())
			->orderBy([
				SX::orderBy(\Katu\Models\Presets\FileAttachment::getColumn('position')),
				SX::orderBy(\Katu\Models\Presets\FileAttachment::getColumn('timeCreated'), SX::kw('desc')),
			])
			->addExpressions($expressions)
			;

		return \Katu\Models\Presets\FileAttachment::getBySql($sql);
	}

	public function getImageFile()
	{
		$imageAttachments = $this->getImageFileAttachments();
		if ($imageAttachments->getTotal()) {
			return $imageAttachments[0]->getFile();
		}

		return false;
	}

	public function getImagePath()
	{
		$file = $this->getImageFile();

		// Is file.
		if ($file instanceof \Katu\Models\Presets\File) {
			return $file->getPath();

		// Is URL.
		} elseif (\Katu\Types\TURL::isValid($file)) {
			return $file;
		}

		return false;
	}

	public function hasImage()
	{
		$path = $this->getImagePath();

		return $path && file_exists($path);
	}
}
