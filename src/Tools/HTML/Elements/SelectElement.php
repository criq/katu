<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;

class SelectElement extends ElementNode
{
	public function __construct(?string $name = null, ?string $id = null, ?NodeCollection $nodes = null)
	{
		$this->setName("select");
		$this->setAttribute(new Attribute("name", $name));
		$this->setAttribute(new Attribute("id", $id));
		$this->setNodes($nodes);
	}

	public function setAutofocus(): SelectElement
	{
		$this->setAttribute(new Attribute("autofocus"));

		return $this;
	}
}
