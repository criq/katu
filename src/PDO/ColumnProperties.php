<?php

namespace Katu\PDO;

class ColumnProperties
{
	public $default;
	public $filter;
	public $isAi;
	public $isMulti;
	public $isNull;
	public $isPrimary;
	public $isUnique;
	public $isUnsigned;
	public $key;
	public $length;
	public $name;
	public $options;
	public $type;

	public function __construct($description)
	{
		$this->name = $description['Field'];

		$numberTypes = implode('|', static::getNumericTypes());

		$numberTypeRegex = "(?<type>$numberTypes)";
		$numberLengthRegex = "(?<length>[0-9]+(,[0-9]+)?)";
		$numberRegex = "/^$numberTypeRegex(\($numberLengthRegex\))?\s*(?<isUnsigned>unsigned)?$/";

		if (preg_match($numberRegex, $description['Type'], $match)) {
			$this->type = (string)$match['type'];
			$this->length = $match['length'] ?? null;
			$this->filter = FILTER_SANITIZE_NUMBER_FLOAT;
			$this->isUnsigned = (bool)($match['isUnsigned'] ?? null);
		} elseif (preg_match('/^(?<type>char|varchar)\((?<length>[0-9]+)\)/', $description['Type'], $match)) {
			$this->type = (string)$match['type'];
			$this->length = (int)$match['length'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('/^(?<type>text|mediumtext|longtext)/', $description['Type'], $match)) {
			$this->type = (string)$match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('/^(?<type>enum)\((?<options>.*)\)/', $description['Type'], $match)) {
			$this->type = 'enum';
			$this->options = array_map(function ($i) {
				return (string)trim($i, '\'');
			}, explode(',', $match['options']));
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('/^(?<type>datetime|date)/', $description['Type'], $match)) {
			$this->type = $match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('/^(?<type>timestamp)/', $description['Type'], $match)) {
			$this->type = $match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		}

		$this->key = (string) ($description['Key']);
		$this->default = $description['Default'];

		if (is_null($this->isNull)) {
			$this->isNull = (bool)($description['Null'] != 'NO');
		}

		if (is_null($this->isPrimary)) {
			$this->isPrimary = (bool)($description['Key'] == 'PRI');
		}

		if (is_null($this->isUnique)) {
			$this->isUnique = (bool)($description['Key'] == 'UNI');
		}

		if (is_null($this->isMulti)) {
			$this->isMulti = (bool)($description['Key'] == 'MUL');
		}

		if (is_null($this->isUnsigned)) {
			$this->isUnsigned = (bool)(strpos($description['Type'], 'unsigned') !== false);
		}

		if (is_null($this->isAi)) {
			$this->isAi = (bool)($description['Extra'] == 'auto_increment');
		}
	}

	public static function getNumericTypes()
	{
		return [
			'bigint',
			'decimal',
			'double',
			'float',
			'int',
			'mediumint',
			'real',
			'smallint',
			'tinyint',
		];
	}
}
