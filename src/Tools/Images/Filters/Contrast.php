<?php

namespace Katu\Tools\Images\Filters;

class Contrast extends \Katu\Tools\Images\Filter
{
	public function apply($image)
	{
		$image->contrast($this->params['level']);

		return true;
	}
}
