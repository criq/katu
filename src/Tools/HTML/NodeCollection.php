<?php

namespace Katu\Tools\HTML;

class NodeCollection extends \ArrayObject implements StringableInterface
{
	public function __toString(): string
	{
		return implode($this->getArrayCopy());
	}

	public function addNode(Node $node): NodeCollection
	{
		$this[] = $node;

		return $this;
	}
}
