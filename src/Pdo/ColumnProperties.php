<?php

namespace Katu\Pdo;

class ColumnProperties {

	public $name;
	public $type;
	public $length;
	public $filter;
	public $options;
	public $key;
	public $default;
	public $isNull;
	public $isPrimary;
	public $isUnique;
	public $isMulti;
	public $isUnsigned;
	public $isAi;

	public function __construct($description) {
		$this->name = $description['Field'];

		if (preg_match('#^(?<type>tinyint|smallint|mediumint|int|bigint)\((?<length>[0-9]+)\)#', $description['Type'], $match)) {
			$this->type = (string) $match['type'];
			$this->length = (int) $match['length'];
			$this->filter = FILTER_SANITIZE_NUMBER_INT;
		} elseif (preg_match('#^(?<type>float|double|real|decimal)\((?<length>[0-9]+,[0-9]+)\)#', $description['Type'], $match)) {
			$this->type = (string) $match['type'];
			$this->length = $match['length'];
			$this->filter = FILTER_SANITIZE_NUMBER_FLOAT;
		} elseif (preg_match('#^(?<type>char|varchar)\((?<length>[0-9]+)\)#', $description['Type'], $match)) {
			$this->type = (string) $match['type'];
			$this->length = (int) $match['length'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('#^(?<type>text|mediumtext|longtext)#', $description['Type'], $match)) {
			$this->type = (string) $match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('#^(?<type>enum)\((?<options>.*)\)#', $description['Type'], $match)) {
			$this->type = 'enum';
			$this->options = array_map(function($i) {
				return (string) trim($i, '\'');
			}, explode(',', $match['options']));
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('#^(?<type>datetime|date)#', $description['Type'], $match)) {
			$this->type = $match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		} elseif (preg_match('#^(?<type>timestamp)#', $description['Type'], $match)) {
			$this->type = $match['type'];
			$this->filter = FILTER_SANITIZE_STRING;
		}

		$this->key = (string) ($description['Key']);
		$this->default = $description['Default'];

		$this->isNull     = (bool) ($description['Null'] != 'NO');
		$this->isPrimary  = (bool) ($description['Key'] == 'PRI');
		$this->isUnique   = (bool) ($description['Key'] == 'UNI');
		$this->isMulti    = (bool) ($description['Key'] == 'MUL');
		$this->isUnsigned = (bool) (strpos($description['Type'], 'unsigned') !== false);
		$this->isAi       = (bool) ($description['Extra'] == 'auto_increment');
	}

}