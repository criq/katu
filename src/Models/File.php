<?php

namespace Katu\Models;

class File extends \Katu\Model {

	const TABLE = 'files';

	static function create() {
		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
		));
	}

}
