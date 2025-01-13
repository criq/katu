<?php

namespace Katu\Tools\HTML;

interface HTMLInterface
{
	public function getHTML(): HTML;
	public function __toString(): string;
}
