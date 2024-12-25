<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;

class FormElement extends ElementNode
{
	public function __construct(?string $method = null, ?string $action = null)
	{
		$this->setName("form");
		$this->setMethod($method);
	}

	public function setMethod(?string $method): FormElement
	{
		$this->setAttribute(new Attribute("method", $method));

		return $this;
	}

	public function setAction(?string $method): FormElement
	{
		$this->setAttribute(new Attribute("action", $method));

		return $this;
	}
}
