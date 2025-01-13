<?php

namespace Katu\Tools\HTML;

class SpaceNode extends TextNode
{
	public function __construct()
	{
		$this->setText(" ");
	}
}
