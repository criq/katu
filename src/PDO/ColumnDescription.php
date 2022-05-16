<?php

namespace Katu\PDO;

class ColumnDescription
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

	public static function createFromResponse(array $response): ColumnDescription
	{
		$object = new static;
		$object->name = $response["Field"];

		$numberTypes = implode("|", static::getNumericTypes());

		$numberTypeRegex = "(?<type>$numberTypes)";
		$numberLengthRegex = "(?<length>[0-9]+(,[0-9]+)?)";
		$numberRegex = "/^$numberTypeRegex(\($numberLengthRegex\))?\s*(?<isUnsigned>unsigned)?$/";

		if (preg_match($numberRegex, $response["Type"], $match)) {
			$object->type = (string)$match["type"];
			$object->length = $match["length"] ?? null;
			$object->filter = FILTER_SANITIZE_NUMBER_FLOAT;
			$object->isUnsigned = (bool)($match["isUnsigned"] ?? null);
		} elseif (preg_match("/^(?<type>char|varchar)\((?<length>[0-9]+)\)/", $response["Type"], $match)) {
			$object->type = (string)$match["type"];
			$object->length = (int)$match["length"];
			$object->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match("/^(?<type>text|mediumtext|longtext)/", $response["Type"], $match)) {
			$object->type = (string)$match["type"];
			$object->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match("/^(?<type>enum)\((?<options>.*)\)/", $response["Type"], $match)) {
			$object->type = "enum";
			$object->options = array_map(function ($i) {
				return (string)trim($i, "\"");
			}, explode(",", $match["options"]));
			$object->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match("/^(?<type>datetime|date)/", $response["Type"], $match)) {
			$object->type = $match["type"];
			$object->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match("/^(?<type>timestamp)/", $response["Type"], $match)) {
			$object->type = $match["type"];
			$object->filter = FILTER_SANITIZE_STRING;
		}

		$object->key = (string) ($response["Key"]);
		$object->default = $response["Default"];

		if (is_null($object->isNull)) {
			$object->isNull = (bool)($response["Null"] != "NO");
		}

		if (is_null($object->isPrimary)) {
			$object->isPrimary = (bool)($response["Key"] == "PRI");
		}

		if (is_null($object->isUnique)) {
			$object->isUnique = (bool)($response["Key"] == "UNI");
		}

		if (is_null($object->isMulti)) {
			$object->isMulti = (bool)($response["Key"] == "MUL");
		}

		if (is_null($object->isUnsigned)) {
			$object->isUnsigned = (bool)(strpos($response["Type"], "unsigned") !== false);
		}

		if (is_null($object->isAi)) {
			$object->isAi = (bool)($response["Extra"] == "auto_increment");
		}

		return $object;
	}

	public static function getNumericTypes(): array
	{
		return [
			"bigint",
			"decimal",
			"double",
			"float",
			"int",
			"mediumint",
			"real",
			"smallint",
			"tinyint",
		];
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getOptions(): array
	{
		return array_map(function(string $option) {
			return trim($option, "'");
		}, (array)$this->options);
	}
}
