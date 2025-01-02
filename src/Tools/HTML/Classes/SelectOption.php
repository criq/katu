<?php

namespace Katu\Tools\HTML\Classes;

use Katu\Tools\HTML\Elements\OptionElement;

class SelectOption
{
	protected $text;
	protected $value;

	public function __construct(string $text, ?string $value = null)
	{
		$this->setText($text);
		$this->setValue($value);
	}

	public function setText(string $text): SelectOption
	{
		$this->text = $text;

		return $this;
	}

	public function getText(): string
	{
		return $this->text;
	}

	public function setValue(?string $value = null): SelectOption
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): ?string
	{
		return $this->value;
	}

	public function getNode(): OptionElement
	{
		return new OptionElement($this->getValue(), $this->getText());
	}
}
