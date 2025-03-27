<?php

namespace Katu\Tools\HTML;

class ElementNode extends Node
{
	protected $attributes;
	protected $name;

	public function __construct(string $name, ?AttributeCollection $attributes = null, ?NodeCollection $nodes = null)
	{
		$this->setAttributes($attributes);
		$this->setName($name);
		$this->setNodes($nodes);
	}

	public function __toString(): string
	{
		return (string)$this->getHTML();
	}

	public function getHTML(): HTML
	{
		return new HTML($this->getIsPairElement() ? "<{$this->getName()} {$this->getAttributes()->getHTML()}>{$this->getNodes()->getHTML()}</{$this->getName()}>" : "<{$this->getName()} {$this->getAttributes()->getHTML()}>");
	}

	public function getIsPairElement(): bool
	{
		return !in_array($this->getName(), [
			"area",
			"base",
			"br",
			"col",
			"embed",
			"hr",
			"img",
			"input",
			"link",
			"meta",
			"param",
			"source",
			"track",
			"wbr",
		]);
	}

	public function setName(string $name): ElementNode
	{
		$this->name = $name;

		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setAttribute(Attribute $attribute): ElementNode
	{
		$this->getAttributes()->addAttribute($attribute);

		return $this;
	}

	public function setAttributes(?AttributeCollection $attributes): ElementNode
	{
		$this->attributes = $attributes;

		return $this;
	}

	public function addAttributes(?AttributeCollection $attributes): ElementNode
	{
		if ($attributes) {
			array_map(function (Attribute $attribute) {
				$this->getAttributes()->addAttribute($attribute);
			}, $attributes->getArrayCopy());
		}

		return $this;
	}

	public function getAttributes(): AttributeCollection
	{
		if (is_null($this->attributes)) {
			$this->attributes = new AttributeCollection;
		}

		return $this->attributes;
	}

	public function getAttribute(string $name): ?Attribute
	{
		return $this->getAttributes()->getAttribute($name);
	}

	public function setClasses(?array $classes = null): ElementNode
	{
		$this->setAttribute(new Attribute("class", implode(" ", array_filter(array_unique((array)$classes)))));

		return $this;
	}

	public function addClass(?string $class): ElementNode
	{
		$this->setClasses([
			...$this->getClasses(),
			$class,
		]);

		return $this;
	}

	public function addClasses(?array $classes): ElementNode
	{
		array_walk($classes, function (string $class) {
			$this->addClass($class);
		});

		return $this;
	}

	public function getClasses(): array
	{
		return explode(" ", $this->getAttribute("class") ? $this->getAttribute("class")->getValue() : null);
	}
}
