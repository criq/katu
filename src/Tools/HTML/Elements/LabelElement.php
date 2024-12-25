<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class LabelElement extends ElementNode
{
	public function __construct(?string $for = null, ?string $text = null)
	{
		$this->setName("label");
		$this->setAttribute(new Attribute("for", $for));
		$this->setNodes(new NodeCollection([
			new TextNode($text),
		]));
	}
}
