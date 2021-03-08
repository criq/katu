<?php

namespace Katu\Models\Presets;

class FileAttachment extends \Katu\Models\Model
{
	const TABLE = 'file_attachments';

	public static function getFileClass()
	{
		return new \ReflectionClass("Katu\Models\Presets\File");
	}

	public static function create(\Katu\Models\Presets\User $creator, \Katu\Models\Model $object, \Katu\Models\Presets\File $file) : FileAttachment
	{
		return static::insert([
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'creatorId' => $creator ? $creator->getId() : null,
			'objectModel' => $object->getClass(),
			'objectId' => $object->getId(),
			'fileId' => $file->getId(),
		]);
	}

	public static function make(\Katu\Models\Presets\User $creator, \Katu\Models\Model $object, \Katu\Models\Presets\File $file) : FileAttachment
	{
		return static::upsert([
			'objectModel' => $object->getClass(),
			'objectId' => $object->getId(),
			'fileId' => $file->getId(),
		], [
			'timeCreated' => new \Katu\Tools\DateTime\DateTime,
			'creatorId' => $creator ? $creator->getId() : null,
		]);
	}

	public function getObject()
	{
		return $this->objectModel::get($this->objectId);
	}

	public function getFile()
	{
		return static::getFileClass()->getName()::get($this->fileId);
	}
}
