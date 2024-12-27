<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class TextareaElement extends InputElement
{
	public function __construct(?string $name = null, ?string $value = null, ?string $id = null)
	{
		$this->setName("textarea");
		$this->setAttribute(new Attribute("name", $name));
		$this->setAttribute(new Attribute("id", $id));
		$this->setNodes(new NodeCollection([
			new TextNode($value),
		]));
	}

	public function setRows(?int $rows = null): TextareaElement
	{
		$this->setAttribute(new Attribute("rows", $rows));

		return $this;
	}
}
