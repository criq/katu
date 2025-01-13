<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class H1Element extends ElementNode
{
	public function __construct(?array $classes = null, ?string $text = null, ?NodeCollection $nodes = null)
	{
		$this->setName("h1");
		$this->setClasses($classes);
		if (mb_strlen($text)) {
			$this->addNodes(new NodeCollection([
				new TextNode($text),
			]));
		}
		if ($nodes) {
			$this->addNodes($nodes);
		}
	}
}
