<?php

namespace Katu\Tools\HTML\Classes;

use Katu\Tools\HTML\Elements\OptionElement;

class SelectOption
{
	protected $text;
	protected $value;

	public function __construct(?string $value = null, ?string $text = null)
	{
		$this->setValue($value);
		$this->setText($text);
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

	public function setText(?string $text): SelectOption
	{
		$this->text = $text;

		return $this;
	}

	public function getText(): ?string
	{
		return $this->text;
	}

	public function getNode(?string $selectedValue = null): OptionElement
	{
		$resolvedText = $this->getText() ?: $this->getValue();

		return new OptionElement($this->getValue(), $resolvedText, $this->getValue() == $selectedValue);
	}
}
