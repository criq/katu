<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class AElement extends ElementNode
{
	public function __construct(?string $href = null, ?string $text = null)
	{
		$this->setName("a");
		$this->setAttribute(new Attribute("href", $href));
		$this->setNodes(new NodeCollection([
			new TextNode($text),
		]));
	}
}
