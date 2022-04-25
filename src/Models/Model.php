<?php

namespace Katu\Models;

use Katu\PDO\Column;
use Sexy\Sexy as SX;

class Model extends Base
{
	public function __toString(): string
	{
		return (string)$this->getId();
	}

	/****************************************************************************
	 * CRUD.
	 */
	public static function insert(?array $values = [])
	{
		$connection = static::getConnection();

		$columns = array_map(function ($i) {
			return new \Katu\PDO\Name($i);
		}, array_keys($values));

		$placeholders = array_map(function ($i) {
			return ":{$i}";
		}, array_keys($values));

		$sql = " INSERT INTO " . static::getTable() . "
				( " . implode(", ", $columns) . " )
			VALUES ( " . implode(", ", $placeholders) . " ) ";

		$query = $connection->createQuery($sql, $values);
		$query->getResult();

		$primaryKey = $connection->getLastInsertId();
		if ($primaryKey) {
			$object = static::get($primaryKey);
		} else {
			throw (new \Katu\Exceptions\NoPrimaryKeyReturnedException)->setContext([
				"sql" => $query->getStatementDump()->getSentSQL(),
			]);
		}

		$object->afterInsertCallback();
		static::afterAnyCallback();

		return $object;
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

	public function save()
	{
		return $this->saveWithCallback();
	}

	public function saveWithoutCallback()
	{
		$plainColumnsNames = static::getTable()->getColumnNames()->getPlain();

		$values = [];
		foreach (get_object_vars($this) as $name => $value) {
			if (in_array($name, $plainColumnsNames) && $name != $this->getPrimaryKeyColumn()->getName()->getPlain()) {
				$values[$name] = $value;
			}
		}

		$set = [];
		foreach ($values as $name => $value) {
			$set[] = (new \Katu\PDO\Name($name)) . " = :" . $name;
		}

		if ($set) {
			$sql = " UPDATE " . static::getTable() . "
				SET " . implode(", ", $set) . "
				WHERE ( {$this->getPrimaryKeyColumn()} = :{$this->getPrimaryKeyColumn()->getName()->getPlain()} ) ";

			$query = static::getConnection()->createQuery($sql, $values);
			$query->setParam($this->getPrimaryKeyColumn()->getName()->getPlain(), $this->getId());
			$query->getResult();
		}

		return $this;
	}

	public function saveWithCallback()
	{
		$this->saveWithoutCallback();
		$this->afterUpdateCallback();
		static::afterAnyCallback();

		return $this;
	}

	public function delete(): bool
	{
		$this->beforeDeleteCallback();

		$sql = " DELETE FROM " . static::getTable() . "
			WHERE {$this->getPrimaryKeyColumn()} = :{$this->getPrimaryKeyColumn()->getName()->getPlain()}";

		$query = static::getConnection()->createQuery($sql, [
			$this->getPrimaryKeyColumn()->getName()->getPlain() => $this->getId(),
		]);

		$res = $query->getResult();

		$this->afterDeleteCallback();

		static::afterAnyCallback();

		return !$res->hasError();
	}

	/****************************************************************************
	 * Callbacks.
	 */
	public function afterInsertCallback(): bool
	{
		return true;
	}

	public function afterUpdateCallback(): bool
	{
		return true;
	}

	public function beforeDeleteCallback(): bool
	{
		return true;
	}

	public function afterDeleteCallback(): bool
	{
		return true;
	}

	public static function afterAnyCallback(): bool
	{
		return true;
	}

	/****************************************************************************
	 * Properties.
	 */
	public static function getPrimaryKeyColumn(): ?Column
	{
		return static::getTable()->getPrimaryKeyColumn();
	}

	public static function getIdColumn(): ?\Katu\PDO\Column
	{
		return static::getPrimaryKeyColumn();
	}

	public function getId(): ?string
	{
		try {
			return $this->{static::getIdColumn()->getName()->getPlain()};
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function get(?string $primaryKey)
	{
		return static::getOneBy([
			static::getPrimaryKeyColumn()->getName()->getPlain() => $primaryKey,
		]);
	}

	public function exists(): bool
	{
		return (bool)static::get($this->getId());
	}

	public function setUniqueColumnValue(Column $column, ?string $chars = null, ?int $length = null)
	{
		if (is_null($chars)) {
			$chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";
		}

		if (is_string($column)) {
			$column = static::getColumn($column);
		}

		if (is_null($length)) {
			$length = $column->getDescription()->length;
		}

		while (true) {
			$string = \Katu\Tools\Random\Generator::getFromChars($chars, $length);
			if (!static::getBy([
				$column->getName()->getPlain() => $string,
			])->getTotal()) {
				$this->{$column->getName()->getPlain()} = $string;
				$this->save();

				return $string;
			}
		}
	}

	public function setUniqueColumnSlug(Column $column, array $source, bool $force = false, array $constraints = [])
	{
		// Generate slug.
		$slug = (new \Katu\Types\TString(trim(implode(" ", (array)$source))))->getForUrl([
			"maxLength" => 245,
		]);

		// If there already is a slug, keep it.
		if (!$force && $this->$column) {
			return true;
		}

		// If it"s the same, keep it.
		if (!$force && $slug == $this->$column) {
			return true;
		}

		$preg = "^$slug(\-([0-9]+))?$";

		// Select all already used slugs.
		$sql = SX::select(static::getColumn($column))
			->from(static::getTable())
			->where(SX::cmpNotEq(static::getIdColumn(), $this->getId()))
			->where(SX::cmpRegexp(static::getColumn($column), $preg))
			->addExpressions($constraints)
			;
		$res = static::getConnection()->select($sql)->getResult();

		// Nothing, keep the slug.
		if (!$res->getTotal()) {
			$this->{$column->getName()->getPlain()} = $slug;
		// There are some, get a new slug.
		} else {
			$suffixes = [];
			foreach ($res->getItems() as $item) {
				preg_match("/" . $preg . "/", $item[$column], $match);
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

			$this->{$column->getName()->getPlain()} = implode("-", array_filter([
				$slug,
				$proposedSuffix,
			]));
		}

		$this->save();

		return true;
	}

	public static function checkUniqueColumnValue($whereExpressions, $excludeObject = null)
	{
		$sql = SX::select(static::getTable())
			->from(static::getTable())
			->addExpressions([
				"where" => $whereExpressions,
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
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectId"), $this->getId()))
			;

		return \App\Models\FileAttachment::getBySql($sql);
	}

	public function getImageFileAttachments()
	{
		$sql = SX::select()
			->select(\App\Models\FileAttachment::getTable())
			->from(\App\Models\FileAttachment::getTable())
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectId"), $this->getId()))
			->joinColumns(\App\Models\FileAttachment::getColumn("fileId"), \App\Models\File::getIdColumn())
			->where(SX::cmpLike(\App\Models\File::getColumn("type"), "image/%"))
			;

		return \App\Models\FileAttachment::getBySql($sql);
	}

	public function getImageFile()
	{
		$sql = SX::select()
			->select(\App\Models\File::getTable())
			->from(\App\Models\FileAttachment::getTable())
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq(\App\Models\FileAttachment::getColumn("objectId"), $this->getId()))
			->orderBy(SX::orderBy(\App\Models\FileAttachment::getColumn("position")))
			->joinColumns(\App\Models\FileAttachment::getColumn("fileId"), \App\Models\File::getIdColumn())
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
					"objectModel" => static::getClass()->getName(),
					"objectId" => $this->getId(),
					"fileId" => $file->getId(),
				], [
					"timeCreated" => new \Katu\Tools\Calendar\Time,
					"creatorId" => $user->getId(),
				], [
					"position" => $position++,
				]);
			}
		}

		return $this;
	}
}
