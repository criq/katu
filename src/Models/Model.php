<?php

namespace Katu\Models;

use \Sexy\Sexy as SX;

class Model extends Base
{
	public function __toString() : string
	{
		return (string)$this->getId();
	}

	public static function insert(?array $values = [])
	{
		$connection = static::getConnection();

		$columns = array_map(function ($i) {
			return new \Katu\PDO\Name($i);
		}, array_keys($values));

		$placeholders = array_map(function ($i) {
			return ':' . $i;
		}, array_keys($values));

		$sql = " INSERT INTO " . static::getTable() . "
				( " . implode(", ", $columns) . " )
			VALUES ( " . implode(", ", $placeholders) . " ) ";

		$query = $connection->createQuery($sql, $values);
		$query->getResult();

		static::change();

		$primaryKey = $connection->getLastInsertId();
		if ($primaryKey) {
			return static::get($primaryKey);
		} else {
			throw new \Katu\Exceptions\NoPrimaryKeyReturnedException;
		}
	}

	public static function insertMultiple(?array $items = [])
	{
		$items = array_values($items);

		$columns = array_map(function ($i) {
			return new \Katu\PDO\Name($i);
		}, array_keys($items[0]));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ";

		$params = [];
		$sqlRows = [];
		foreach ($items as $row => $values) {
			$sqlRowParams = [];
			foreach ($values as $key => $value) {
				$paramKey = implode('_', [
					'row',
					$row,
					$key,
				]);
				$params[$paramKey] = $value;
				$sqlRowParams[] = ":" . $paramKey;
			}
			$sqlRows[] = " ( " . implode(', ', $sqlRowParams) . " ) ";
		}

		$sql .= implode(", ", $sqlRows);

		$query = static::getConnection()->createQuery($sql, $params);
		$query->getResult();

		static::change();

		return static::get(static::getConnection()->getLastInsertId());
	}

	public static function upsert(array $getByParams, array $insertParams = [], array $updateParams = [])
	{
		$object = static::getOneBy($getByParams);
		if ($object) {
			foreach ($updateParams as $name => $value) {
				$object->update($name, $value);
			}
			$object->save();
		} else {
			$object = static::insert(array_merge((array)$getByParams, (array)$insertParams, (array)$updateParams));
		}

		return $object;
	}

	public function update(string $property, $value = null)
	{
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
			}

			static::change();
		}

		return $this;
	}

	public function delete()
	{
		$sql = " DELETE FROM " . static::getTable() . " WHERE " . static::getPrimaryKeyColumnName() . " = :" . static::getPrimaryKeyColumnName();

		$query = static::getConnection()->createQuery($sql, [
			static::getPrimaryKeyColumnName() => $this->getId(),
		]);

		$res = $query->getResult();

		static::change();

		return $res;
	}

	public function save()
	{
		$columnsNames = array_map(function ($columnName) {
			return $columnName->getName();
		}, static::getTable()->getColumnNames());

		$values = [];
		foreach (get_object_vars($this) as $name => $value) {
			if (in_array($name, $columnsNames) && $name != static::getPrimaryKeyColumnName()) {
				$values[$name] = $value;
			}
		}

		$set = [];
		foreach ($values as $name => $value) {
			$set[] = (new \Katu\PDO\Name($name)) . " = :" . $name;
		}

		if ($set) {
			$sql = " UPDATE " . static::getTable() . " SET " . implode(", ", $set) . " WHERE ( " . $this->getPrimaryKeyColumnName() . " = :" . $this->getPrimaryKeyColumnName() . " ) ";

			$query = static::getConnection()->createQuery($sql, $values);
			$query->setParam(static::getPrimaryKeyColumnName(), $this->getId());
			$query->getResult();
		}

		static::change();

		return $this;
	}

	public static function change()
	{
		static::getTable()->touch();

		return true;
	}

	public static function getIdColumn() : \Katu\PDO\Column
	{
		return static::getColumn(static::getPrimaryKeyColumnName());
	}

	public static function getPrimaryKeyColumnName() : ?string
	{
		return static::getTable()->getPrimaryKeyColumnName();
	}

	public function getId() : ?string
	{
		try {
			return $this->{static::getPrimaryKeyColumnName()};
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function get($primaryKey)
	{
		return static::getOneBy([
			static::getPrimaryKeyColumnName() => $primaryKey,
		]);
	}

	public function exists()
	{
		return (bool)static::get($this->getId());
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
		$res = static::getConnection()->select($sql)->getResult();

		// Nothing, keep the slug.
		if (!$res->getCount()) {
			$this->update($column, $slug);
		// There are some, get a new slug.
		} else {
			$suffixes = [];
			foreach ($res->getItems() as $item) {
				preg_match('/' . $preg . '/', $item[$column], $match);
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

	/****************************************************************************
	 * FileAttachments.
	 */
	public function getFileAttachments()
	{
		$sql = SX::select()
			->select(\App\Models\FileAttachment::getTable())
			->from(\App\Models\FileAttachment::getTable())
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectModel'), static::getClass()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectId'), $this->getId()))
			;

		return \App\Models\FileAttachment::getBySql($sql);
	}

	public function getImageFileAttachments()
	{
		$sql = SX::select()
			->select(\App\Models\FileAttachment::getTable())
			->from(\App\Models\FileAttachment::getTable())
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectModel'), static::getClass()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectId'), $this->getId()))
			->joinColumns(\App\Models\FileAttachment::getColumn('fileId'), \App\Models\File::getIdColumn())
			->where(SX::cmpLike(\App\Models\File::getColumn('type'), 'image/%'))
			;

		return \App\Models\FileAttachment::getBySql($sql);
	}

	public function getImageFile()
	{
		$sql = SX::select()
			->select(\App\Models\File::getTable())
			->from(\App\Models\FileAttachment::getTable())
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectModel'), static::getClass()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn('objectId'), $this->getId()))
			->orderBy([
				SX::orderBy(\App\Models\FileAttachment::getColumn('position'))
			])
			->joinColumns(\App\Models\FileAttachment::getColumn('fileId'), \App\Models\File::getIdColumn())
			;

		return \App\Models\File::getBySql($sql)->getOne();
	}

	public function refreshFileAttachmentsFromFileIds(\App\Models\User $user, ?array $fileIds)
	{
		foreach ($this->getFileAttachments() as $fileAttachment) {
			$fileAttachment->delete();
		}

		$position = 1;
		foreach ($fileIds as $fileId) {
			$file = \App\Models\File::get($fileId);
			if ($file) {
				\App\Models\FileAttachment::upsert([
					'objectModel' => static::getClass(),
					'objectId' => $this->getId(),
					'fileId' => $file->getId(),
				], [
					'timeCreated' => new \App\Classes\DateTime,
					'creatorId' => $user->getId(),
				], [
					'position' => $position++,
				]);
			}
		}

		return $this;
	}
}
