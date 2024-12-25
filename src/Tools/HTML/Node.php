<?php

namespace Katu\Tools\HTML;

abstract class Node implements StringableInterface
{
	protected $nodes;

	public function setNodes(?NodeCollection $nodes = null): Node
	{
		$this->nodes = $nodes;

		return $this;
	}

	public function addNode(Node $node): Node
	{
		$this->getNodes()->addNode($node);

		return $this;
	}

	public function getNodes(): NodeCollection
	{
		if (is_null($this->nodes)) {
			$this->nodes = new NodeCollection;
		}

		return $this->nodes;
	}
}
