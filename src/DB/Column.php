<?php

namespace Jabli\DB;

class Column {

	const KEY_PRIMARY = 'primary';
	const KEY_UNIQUE  = 'unique';

	private $source;
	public $name;
	public $type;
	public $length;
	public $key;
	public $options = array();
	public $default;
	public $is_null;
	public $is_primary;
	public $is_unique;
	public $is_multi;
	public $is_unsigned;
	public $is_ai;

	public function __construct($row) {
		$this->source = $row;

		$this->name = $row['Field'];

		if (preg_match('#^(?<type>tinyint|smallint|mediumint|int|bigint|char|varchar)\((?<length>[0-9]+)\)#', $row['Type'], $match)) {
			$this->type = (string) $match['type'];
			$this->length = (int) $match['length'];
		} elseif (preg_match('#^(?<type>enum)\((?<options>.*)\)#', $row['Type'], $match)) {
			$this->type = 'enum';
			$this->options = array_map(function($i) {
				return (string) trim($i, '\'');
			}, explode(',', $match['options']));
		} elseif (preg_match('#^(?<type>datetime|date)#', $row['Type'], $match)) {
			$this->type = $match['type'];
		} elseif (preg_match('#^(?<type>timestamp)#', $row['Type'], $match)) {
			$this->type = $match['type'];
		}

		$this->key = (string) ($row['Key']);
		$this->default = $row['Default'];

		$this->is_null = (bool) ($row['Null'] != 'NO');
		$this->is_primary = (bool) ($row['Key'] == 'PRI');
		$this->is_unique = (bool) ($row['Key'] == 'UNI');
		$this->is_multi = (bool) ($row['Key'] == 'MUL');
		$this->is_unsigned = (bool) (strpos($row['Type'], 'unsigned') !== FALSE);
		$this->is_ai = (bool) ($row['Extra'] == 'auto_increment');
	}

}
