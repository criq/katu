<?php

namespace Katu\Tools\HTML;

class TextNode extends Node
{
	protected $text;

	public function __construct(?string $text = null, ?NodeCollection $nodes = null)
	{
		$this->setText($text);
		$this->setNodes($nodes);
	}

	public function __toString(): string
	{
		return (string)$this->getHTML();
	}

	public function getHTML(): HTML
	{
		return new HTML(implode([
			$this->getText(),
			...$this->getNodes()->getArrayCopy(),
		]));
	}

	public function setText(?string $text): TextNode
	{
		$this->text = $text;

		return $this;
	}

	public function getText(): ?string
	{
		return $this->text;
	}
}
