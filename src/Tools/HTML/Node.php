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

	public function addNodes(NodeCollection $nodes): Node
	{
		array_walk($nodes->getArrayCopy(), function (Node $node) {
			$this->addNode($node);
		});

		return $this;
	}

	public function getNodes(): NodeCollection
	{
		if (is_null($this->nodes)) {
			$this->nodes = new NodeCollection;
		}

		return $this->nodes;
	}

	public function getMarkup(): \Twig\Markup
	{
		return new \Twig\Markup((string)$this, "UTF-8");
	}
}
