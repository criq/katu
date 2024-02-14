<?php

namespace Katu\Tools\Emails;

class Attachment
{
	protected $entity;
	protected $name;
	protected $cid;

	public function __construct(\Katu\Storage\Entity $entity, ?string $name = null, ?string $cid = null)
	{
		$this->setEntity($entity);
		$this->setName($name);
		$this->setCID($cid);
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

	public function setCID(): ?string
	{
		return $this->cid;
	}
}
