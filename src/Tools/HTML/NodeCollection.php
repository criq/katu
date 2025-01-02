<?php

namespace Katu\Tools\HTML;

class NodeCollection extends \ArrayObject implements HTMLInterface
{
	public function __toString(): string
	{
		return (string)$this->getHTML();
	}

	public function getHTML(): HTML
	{
		return new HTML(implode(array_map(function (Node $node) {
			return $node->getHTML();
		}, $this->getArrayCopy())));
	}

	public function addNode(Node $node): NodeCollection
	{
		$this[] = $node;

		return $this;
	}
}
