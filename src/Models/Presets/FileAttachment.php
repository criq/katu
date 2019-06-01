<?php

namespace Katu\Models\Presets;

class FileAttachment extends \Katu\Models\Model {

	const TABLE = 'file_attachments';

	static function create($creator, $object, $file) {
		if (!static::checkCrudParams($creator, $object, $file)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Tools\DateTime\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'   => (int)    ($creator ? $creator->getId() : null),
			'objectModel' => (string) ($object->getClass()),
			'objectId'    => (int)    ($object->getId()),
			'fileId'      => (int)    ($file->getId()),
		));
	}

	static function make($creator, $object, $file) {
		if (!static::checkCrudParams($creator, $object, $file)) {
			throw new \Katu\Exceptions\InputErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'objectModel' => (string) ($object->getClass()),
			'objectId'    => (int)    ($object->getId()),
			'fileId'      => (int)    ($file->getId()),
		), $creator, $object, $file);
	}

	static function checkCrudParams($creator, $object, $file) {
		if (!is_a($object, '\Katu\Model')) {
			throw (new \Katu\Exceptions\InputErrorException("Object is not a model."))
				->addErrorName('object')
				;
		}
		if (!$file) {
			throw (new \Katu\Exceptions\InputErrorException("Invalid file."))
				->addErrorName('file')
				;
		}

		return true;
	}

	public function getObject() {
		$class = $this->objectModel;

		return $class::get($this->objectId);
	}

}
