<?php

namespace Katu\Tools\Emails;

class Attachment
{
	protected $entity;
	protected $name;
	protected $contentId;

	public function __construct(\Katu\Storage\Entity $entity, ?string $name = null, ?string $contentId = null)
	{
		$this->setEntity($entity);
		$this->setName($name);
		$this->setContentId($contentId);
	}

	public function setEntity(\Katu\Storage\Entity $entity): Attachment
	{
		$this->entity = $entity;

		return $this;
	}

	public function getEntity(): \Katu\Storage\Entity
	{
		return $this->entity;
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
		return $this->getName() ?: $this->getEntity()->getFileName();
	}

	public function getContentType(): ?string
	{
		return $this->getEntity()->getContentType();
	}

	public function getContents(): ?string
	{
		return $this->getEntity()->getContents();
	}

	public function getEncodedContents(): ?string
	{
		return base64_encode($this->getContents());
	}
}
