<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;

class UlElement extends ElementNode
{
	public function __construct(?array $classes = null, ?NodeCollection $nodes = null)
	{
		$this->setName("ul");
		$this->setClasses($classes);
		$this->setNodes($nodes);
	}
}
