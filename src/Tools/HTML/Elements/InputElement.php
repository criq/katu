<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;

class InputElement extends ElementNode
{
	public function __construct(?string $type = null, ?string $name = null, ?string $value = null, ?string $id = null)
	{
		$this->setName("input");
		$this->setAttribute(new Attribute("type", $type));
		$this->setAttribute(new Attribute("name", $name));
		$this->setAttribute(new Attribute("value", $value));
		$this->setAttribute(new Attribute("id", $id));
	}

	public function setPlaceholder(?string $placeholder): InputElement
	{
		$this->setAttribute(new Attribute("placeholder", $placeholder));

		return $this;
	}
}
