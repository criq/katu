<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class PElement extends ElementNode
{
	public function __construct(?string $text = null)
	{
		$this->setName("p");

		if ($text) {
			$this->setNodes(new NodeCollection([
				new TextNode($text),
			]));
		}
	}
}
