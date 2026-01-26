<?php

namespace Katu\Models\Presets;

use Katu\Tools\Users\UserInterface;

/**
 * @deprecated Use FileAttachmentInterface instead. This class is kept for backward compatibility.
 */
abstract class FileAttachment extends \Katu\Models\Model implements FileAttachmentInterface
{
	const TABLE = "file_attachments";

	public static function create(UserInterface $creator, \Katu\Models\Model $object, \Katu\Models\Presets\FileInterface $file) : FileAttachmentInterface
	{
		return static::insert([
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"creatorId" => $creator ? $creator->getId() : null,
			"objectModel" => $object->getClass()->getName(),
			"objectId" => $object->getId(),
			"fileId" => $file->getId(),
		]);
	}

	public static function make(UserInterface $creator, \Katu\Models\Model $object, \Katu\Models\Presets\FileInterface $file) : FileAttachmentInterface
	{
		return static::upsert([
			"objectModel" => $object->getClass()->getName(),
			"objectId" => $object->getId(),
			"fileId" => $file->getId(),
		], [
			"timeCreated" => new \Katu\Tools\Calendar\Time,
			"creatorId" => $creator ? $creator->getId() : null,
		]);
	}

	public function getObject()
	{
		return $this->objectModel::get($this->objectId);
	}

	public function getFile(): FileInterface
	{
		$fileClass = \App\App::getContainer()->get(\Katu\Models\Presets\FileInterface::class);

		return $fileClass::get($this->fileId);
	}
}
