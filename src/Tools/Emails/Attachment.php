<?php

namespace Katu\Tools\Emails;

class Attachment
{
	protected $storageObject;
	protected $name;
	protected $contentId;

	public function __construct(\Katu\Storage\StorageObject $storageObject, ?string $name = null, ?string $contentId = null)
	{
		$this->setStorageObject($storageObject);
		$this->setName($name);
		$this->setContentId($contentId);
	}

	public function setStorageObject(\Katu\Storage\StorageObject $storageObject): Attachment
	{
		$this->storageObject = $storageObject;

		return $this;
	}

	public function getStorageObject(): \Katu\Storage\StorageObject
	{
		return $this->storageObject;
	}

	/**
	 * @deprecated Use getStorageObject() instead
	 */
	public function getEntity(): \Katu\Storage\StorageObject
	{
		return $this->getStorageObject();
	}

	public function setName(?string $name): Attachment
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setContentId(?string $contentId): Attachment
	{
		$this->contentId = $contentId;

		return $this;
	}

	public function getContentId(): ?string
	{
		return $this->contentId;
	}

	public function getResolvedName(): ?string
	{
		return $this->getName() ?: $this->getStorageObject()->getName();
	}

	public function getContentType(): ?string
	{
		return $this->getStorageObject()->getType();
	}

	public function getContents(): ?string
	{
		try {
			return $this->getStorageObject()->read();
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getEncodedContents(): ?string
	{
		return base64_encode($this->getContents());
	}
}
