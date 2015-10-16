<?php

namespace Katu\Models;

class ObjectSetting extends \Katu\Model {

	static function create($creator, $object, $basket, $name) {
		if (!static::checkCrudParams($creator, $object, $basket, $name)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert([
			'timeCreated'                 => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'                   => (int)    ($creator->getId()),
			static::OBJECT_COLUMN_ID_NAME => (int)    ($object->getId()),
			'name'                        => (string) (trim($name)),
		]);
	}

	static function make($creator, $object, $basket, $name) {
		return static::getOneOrCreateWithList([
			static::OBJECT_COLUMN_ID_NAME => (int)    ($object->getId()),
			'name'                        => (string) (trim($name)),
		], $creator, $object, $basket, $name);
	}

	static function checkCrudParams($creator, $object, $basket, $name) {
		if (!$creator || !($creator instanceof User)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid creator."))
				->addErrorName('creator')
				;
		}

		if (!$object || !($object instanceof \Katu\Model)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid object."))
				->addErrorName('object')
				;
		}

		if (!trim($name)) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
				->addErrorName('name')
				;
		}

		return true;
	}

}
