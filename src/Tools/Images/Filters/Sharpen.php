<?php

namespace Katu\Tools\Images\Filters;

class Sharpen extends \Katu\Tools\Images\Filter
{
	public function apply($image)
	{
		$image->sharpen($this->params["level"]);

		return true;
	}
}
