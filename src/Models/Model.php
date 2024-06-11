<?php

namespace Katu\Models;

use App\Models\Users\User;
use Katu\PDO\Column;
use Katu\Tools\Calendar\Timeout;
use Katu\Tools\Options\Option;
use Katu\Tools\Options\OptionCollection;
use Katu\Types\TIdentifier;
use Sexy\Sexy as SX;

class Model extends Base
{
	protected $_storedId;

	public function __toString(): string
	{
		return (string)$this->getId();
	}

	public function beforePersistCallback(): Model
	{
		return $this;
	}

	public function afterPersistCallback(): Model
	{
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function beforeInsertCallback(): Model
	{
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function afterInsertCallback(): Model
	{
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function beforeUpdateCallback(): Model
	{
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function afterUpdateCallback(): Model
	{
		return $this;
	}

	public function beforeDeleteCallback(): Model
	{
		return $this;
	}

	public function afterDeleteCallback(): Model
	{
		return $this;
	}

	public function beforeAnyCallback(): Model
	{
		return $this;
	}

	public function afterAnyCallback(): Model
	{
		return $this;
	}

	/************************************************************************* */

	public function getColumnValues(): ColumnValueCollection
	{
		$res = new ColumnValueCollection;
		foreach (static::getTable()->getColumns() as $column) {
			$res[] = new ColumnValue($column, $this->{$column->getName()->getPlain()});
		}

		return $res;
	}

	public function persistInsert(): Model
	{
		$connection = static::getConnection();
		$table = static::getTable();

		$columnValues = $this->getColumnValues();

		$sql = " INSERT INTO {$table} ( {$columnValues->getColumnsString()} ) VALUES ( {$columnValues->getParamsString()} ) ";
		$query = $connection->createQuery($sql, $columnValues->getStatementParams());
		$query->getResult();

		$id = $connection->getLastInsertId();
		if (!$id) {
			throw (new \Katu\Exceptions\NoPrimaryKeyReturnedException)->setContext([
				"sql" => $query->getStatementDump()->getSentSQL(),
			]);
		}

		$this->setId($id);

		return $this;
	}

	public function persistUpdate(): Model
	{
		$connection = static::getConnection();
		$table = static::getTable();

		$columnValues = $this->getColumnValues();

		$idColumn = static::getIdColumn();
		$idColumnParamName = "__PRIMARY_KEY__";
		$sql = " UPDATE {$table} SET {$columnValues->getSetString()} WHERE {$idColumn} = :{$idColumnParamName} ";
		$params = array_merge($columnValues->getStatementParams(), [
			$idColumnParamName => $this->getStoredId(),
		]);
		$query = $connection->createQuery($sql, $params);
		$query->getResult();

		return $this;
	}

	public function persistWithoutCallbacks(): Model
	{
		is_null($this->getId()) ? $this->persistInsert() : $this->persistUpdate();

		return $this;
	}

	public function persist(): Model
	{
		$this->beforePersistCallback();
		$this->beforeAnyCallback();

		$this->persistWithoutCallbacks();

		$this->afterPersistCallback();
		$this->afterAnyCallback();

		return $this;
	}

	/**
	 * @deprecated
	 */
	public static function insert(?array $values = [], $saveWithCallback = true)
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

		if ($saveWithCallback) {
			$object
				->afterInsertCallback()
				->afterAnyCallback()
				;
		}

		return $object;
	}

	/**
	 * @deprecated
	 */
	public static function upsert(array $getByParams, array $insertParams = [], array $updateParams = [], $saveWithCallback = true)
	{
		$object = static::getOneBy($getByParams);
		if ($object) {
			foreach ($updateParams as $name => $value) {
				$object->$name = $value;
			}
			if ($saveWithCallback) {
				$object->saveWithCallback();
			} else {
				$object->saveWithoutCallback();
			}
		} else {
			$params = array_merge((array)$getByParams, (array)$insertParams, (array)$updateParams);

			$object = static::insert($params, $saveWithCallback);
		}

		return $object;
	}

	/**
	 * @deprecated
	 */
	public function save(): Model
	{
		return $this->saveWithCallback();
	}

	public function saveWithoutCallback(): Model
	{
		$plainColumnsNames = static::getTable()->getColumnNames()->getPlain();

		$values = [];
		foreach (get_object_vars($this) as $name => $value) {
			if (in_array($name, $plainColumnsNames) && $name != $this->getIdColumn()->getName()->getPlain()) {
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
				WHERE ( {$this->getIdColumn()} = :{$this->getIdColumn()->getName()->getPlain()} ) ";

			$query = static::getConnection()->createQuery($sql, $values);
			$query->setParam($this->getIdColumn()->getName()->getPlain(), $this->getId());
			$query->getResult();
		}

		return $this;
	}

	public function saveWithCallback(): Model
	{
		return $this
			->beforeUpdateCallback()
			->saveWithoutCallback()
			->afterUpdateCallback()
			->afterAnyCallback()
			;
	}

	public function delete(): bool
	{
		$this->beforeDeleteCallback();

		$sql = " DELETE FROM " . static::getTable() . "
			WHERE {$this->getIdColumn()} = :{$this->getIdColumn()->getName()->getPlain()}";

		$query = static::getConnection()->createQuery($sql, [
			$this->getIdColumn()->getName()->getPlain() => $this->getId(),
		]);

		$res = $query->getResult();

		$this->afterDeleteCallback();
		$this->afterAnyCallback();

		return !$res->hasError();
	}

	/**
	 * @deprecated
	 */
	public static function getPrimaryKeyColumn(): ?Column
	{
		return static::getIdColumn();
	}

	public static function getIdColumn(): ?\Katu\PDO\Column
	{
		$columnClassName = static::getColumnClass()->getName();

		return new $columnClassName(static::getTable(), static::getTable()->getPrimaryKeyColumn()->getName());
	}

	public function setId(?string $id)
	{
		$this->setStoredId($this->getId());

		$this->{static::getIdColumn()->getName()->getPlain()} = $id;

		return $this;
	}

	public function getId(): ?string
	{
		try {
			return $this->{static::getIdColumn()->getName()->getPlain()};
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function setStoredId(?string $id)
	{
		$this->_storedId = $id;

		return $this;
	}

	public function getStoredId(): ?string
	{
		return $this->_storedId ?: $this->getId();
	}

	public static function get(?string $id)
	{
		if (is_null($id)) {
			return null;
		}

		return static::getOneBy([
			static::getIdColumn()->getName()->getPlain() => $id,
		]);
	}

	public static function getFromRuntime(?string $id)
	{
		return \Katu\Cache\Runtime::get(new TIdentifier(static::class, __FUNCTION__, $id), function () use ($id) {
			return static::get($id);
		});
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
				$this->persist();

				return $string;
			}
		}
	}

	public function setUniqueColumnSlug(Column $column, array $source, bool $force = false, array $constraints = [])
	{
		// Generate slug.
		$slug = (new \Katu\Types\TString(trim(implode(" ", (array)$source))))->getForURL(new OptionCollection([
			new Option("MAX_LENGTH", 245),
		]));

		// If there already is a slug, keep it.
		if (!$force && $this->{$column->getName()->getPlain()}) {
			return true;
		}

		// If it"s the same, keep it.
		if (!$force && $slug == $this->{$column->getName()->getPlain()}) {
			return true;
		}

		$preg = "^{$slug}(\-([0-9]+))?$";

		// Select all already used slugs.
		$sql = SX::select($column)
			->from(static::getTable())
			->where(SX::cmpNotEq(static::getIdColumn(), $this->getId()))
			->where(SX::cmpRegexp($column, $preg))
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
				preg_match("/" . $preg . "/", $item[$column->getName()->getPlain()], $match);
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

		$this->persist();

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

		return !static::getBySQL($sql)->getTotal();
	}

	/****************************************************************************
	 * FileAttachments.
	 */
	public function getFileAttachments()
	{
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		$sql = SX::select()
			->select($fileAttachmentClass::getTable())
			->from($fileAttachmentClass::getTable())
			->where(SX::eq($fileAttachmentClass::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq($fileAttachmentClass::getColumn("objectId"), $this->getId()))
			;

		return $fileAttachmentClass::getBySQL($sql);
	}

	public function getImageFileAttachments()
	{
		$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\File::class);
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		$sql = SX::select()
			->select($fileAttachmentClass::getTable())
			->from($fileAttachmentClass::getTable())
			->where(SX::eq($fileAttachmentClass::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq($fileAttachmentClass::getColumn("objectId"), $this->getId()))
			->joinColumns($fileAttachmentClass::getColumn("fileId"), $fileClass::getIdColumn())
			->where(SX::cmpLike($fileClass::getColumn("type"), "image/%"))
			;

		return $fileAttachmentClass::getBySQL($sql);
	}

	public function getImageFile(): ?\Katu\Models\Presets\File
	{
		$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\File::class);
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		$sql = SX::select()
			->setGetFoundRows(false)
			->select($fileClass::getTable())
			->from($fileAttachmentClass::getTable())
			->where(SX::eq($fileAttachmentClass::getColumn("objectModel"), static::getClass()->getName()))
			->where(SX::eq($fileAttachmentClass::getColumn("objectId"), $this->getId()))
			->orderBy(SX::orderBy($fileAttachmentClass::getColumn("position")))
			->joinColumns($fileAttachmentClass::getColumn("fileId"), $fileClass::getIdColumn())
			;
		// echo $sql;die;

		return $fileClass::getBySQL($sql)->getOne();
	}

	public function getCachedImageFile(?Timeout $timeout = null): ?\Katu\Models\Presets\File
	{
		$timeout = $timeout ?: new Timeout("1 day");

		return \Katu\Cache\General::get(new TIdentifier(__CLASS__, __FUNCTION__, $this->getId()), $timeout, function () {
			return $this->getImageFile();
		});
	}

	public function refreshFileAttachmentsFromFileIds(User $user, ?array $fileIds)
	{
		$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\File::class);
		$fileAttachmentClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileAttachment::class);

		foreach ($this->getFileAttachments() as $fileAttachment) {
			$fileAttachment->delete();
		}

		$position = 1;
		foreach ($fileIds as $fileId) {
			$file = $fileClass::get($fileId);
			if ($file) {
				$fileAttachmentClass::upsert([
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
