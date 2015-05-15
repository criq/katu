<?php

namespace Katu\Models;

class FileAttachment extends \Katu\Model {

	const TABLE = 'file_attachments';

	static function create($creator, $object, $file) {
		if (!static::checkCrudParams($creator, $object, $file)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDbDateTimeFormat()),
			'creatorId'   => (int)    ($creator ? $creator->id : null),
			'objectModel' => (string) ($object->getClass()),
			'objectId'    => (int)    ($object->getId()),
			'fileId'      => (int)    ($file->id),
		));
	}

	static function make($creator, $object, $file) {
		if (!static::checkCrudParams($creator, $object, $file)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid arguments.");
		}

		return static::getOneOrCreateWithList(array(
			'objectModel' => (string) ($object->getClass()),
			'objectId'    => (int)    ($object->getId()),
			'fileId'      => (int)    ($file->id),
		), $creator, $object, $file);
	}

	static function checkCrudParams($creator, $object, $file) {
		if ($creator && !($creator instanceof \App\Models\Creator)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid file attachment creator.", 'file');
		}
		if (!is_a($object, '\Katu\Model')) {
			throw new \Katu\Exceptions\ArgumentErrorException("Object is not a model.", 'object');
		}
		if (!$file || !($file instanceof \App\Models\File)) {
			throw new \Katu\Exceptions\ArgumentErrorException("Invalid file.", 'file');
		}

		return true;
	}

	public function getObject() {
		$class = $this->objectModel;

		return $class::get($this->objectId);
	}

}
