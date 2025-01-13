<?php

namespace Katu\Tools\HTML\Elements;

use Katu\Tools\HTML\Attribute;
use Katu\Tools\HTML\ElementNode;
use Katu\Tools\HTML\NodeCollection;
use Katu\Tools\HTML\TextNode;

class OptionElement extends ElementNode
{
	public function __construct(?string $value = null, ?string $text = null, ?bool $selected = null)
	{
		$this->setName("option");
		$this->setAttribute(new Attribute("value", $value));
		if (mb_strlen($text)) {
			$this->setNodes(new NodeCollection([
				new TextNode($text),
			]));
		}
		if ($selected) {
			$this->setAttribute(new Attribute("selected"));
		}
	}
}
