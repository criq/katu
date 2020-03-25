<?php

// namespace Katu\Models\Presets;

// abstract class ObjectSetting extends \Katu\Models\Model
// {
// 	public static function create($creator, $object, $name)
// 	{
// 		if (!static::checkCrudParams($creator, $object, $name)) {
// 			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
// 		}

// 		return static::insert([
// 			'timeCreated'                 => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
// 			'creatorId'                   => (int)    ($creator->getId()),
// 			static::OBJECT_COLUMN_ID_NAME => (int)    ($object->getId()),
// 			'name'                        => (string) (trim($name)),
// 		]);
// 	}

// 	static function make($creator, $object, $name) {
// 		return static::getOneOrCreateWithList([
// 			static::OBJECT_COLUMN_ID_NAME => (int)    ($object->getId()),
// 			'name'                        => (string) (trim($name)),
// 		], $creator, $object, $name);
// 	}

// 	static function checkCrudParams($creator, $object, $name) {
// 		if (!$creator || !($creator instanceof User)) {
// 			throw (new \Katu\Exceptions\InputErrorException("Invalid creator."))
// 				->addErrorName('creator')
// 				;
// 		}

// 		if (!$object || !($object instanceof \Katu\Model)) {
// 			throw (new \Katu\Exceptions\InputErrorException("Invalid object."))
// 				->addErrorName('object')
// 				;
// 		}

// 		if (!trim($name)) {
// 			throw (new \Katu\Exceptions\InputErrorException("Invalid name."))
// 				->addErrorName('name')
// 				;
// 		}

// 		return true;
// 	}

// }
