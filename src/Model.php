<?php

namespace Katu;

class Model extends ReadOnlyModel {

	protected $__updated = false;

	public function __call($name, $args) {
		// Setter.
		if (preg_match('#^set(?<property>[a-z]+)$#i', $name, $match) && count($args) == 1) {
			$property = $this->getPropertyName($match['property']);
			$value    = $args[0];

			if ($property && $this->update($property, $value)) {
				return true;
			}
		}

		return parent::__call($name, $args);
	}

	static function insert($bindValues = []) {
		$query = static::getPdo()->createQuery();

		$columns = array_map(function($i) {
			return "`" . $i . "`";
		}, array_keys($bindValues));
		$values  = array_map(function($i) {
			return ':' . $i;
		}, array_keys($bindValues));

		$sql = " INSERT INTO " . static::getTable() . " ( " . implode(", ", $columns) . " ) VALUES ( " . implode(", ", $values) . " ) ";

		$query->setSql($sql);
		$query->setBindValues($bindValues);
		$query->getResult();

		return static::get(static::getPdo()->getLastInsertId());
	}

	static function upsert($bindValues) {
		$object = static::getOneBy($bindValues);
		if (!$object) {
			$object = static::insert($bindValues);
		}

		return $object;
	}

	public function update($property, $value) {
		if (property_exists($this, $property)) {
			if ($this->$property !== $value) {
				$this->$property = $value;
				$this->__updated = true;
			}

			return true;
		}

		return false;
	}

	public function save() {
		if ($this->__updated) {

			$columns = static::getTable()->getColumnNames();

			$bindValues = [];
			foreach (get_object_vars($this) as $name => $value) {
				if (in_array($name, $columns) && $name != static::getIdColumnName()) {
					$bindValues[$name] = $value;
				}
			}

			$set = [];
			foreach ($bindValues as $name => $value) {
				$set[] = $name . " = :" . $name;
			}

			if ($set) {

				$query = static::getPdo()->createQuery();

				$sql = " UPDATE " . static::getTable() . " SET " . implode(", ", $set) . " WHERE ( " . $this->getIdColumnName() . " = :" . $this->getIdColumnName() . " ) ";

				$query->setSql($sql);
				$query->setBindValues($bindValues);
				$query->setBindValue(static::getIdColumnName(), $this->getId());
				$query->getResult();

			}

			$this->__updated = false;
		}

		return true;
	}

	public function delete() {
		$query = static::getPdo()->createQuery();

		// Delete file attachments.
		if (class_exists('\App\Models\FileAttachment')) {
			foreach ($this->getFileAttachments() as $fileAttachment) {
				$fileAttachment->delete();
			}
		}

		$sql = " DELETE FROM " . static::getTable() . " WHERE " . static::getIdColumnName() . " = :" . static::getIdColumnName();

		$query->setSql($sql);
		$query->setBindValue(static::getIdColumnName(), $this->getId());

		return $query->getResult();
	}

	public function setUniqueColumnValue($column, $chars = null, $length = null) {
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
			$string = \Katu\Utils\Random::getFromChars($chars, $length);
			if (!static::getBy([$column->name => $string])->getTotal()) {
				$this->update($column->name, $string);
				$this->save();

				return true;
			}
		}
	}

	public function setUniqueColumnSlug($column, $source, $force = false) {
		// Generate slug.
		$slug = (new \Katu\Types\TString($source))->getForUrl([
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
		$sql = (new \Sexy\Select(static::getColumn($column)))
			->from(static::getTable())
			->where(new \Sexy\CmpNotEq(static::getIdColumn(), $this->getId()))
			->where(new \Sexy\CmpRegexp(static::getColumn($column), $preg))
			;
		$res = static::getPdo()->createQueryFromSql($sql)->getResult();

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

}
