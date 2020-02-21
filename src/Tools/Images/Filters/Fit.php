<?php

namespace Katu\Tools\Images\Filters;

class Fit extends \Katu\Tools\Images\Filter
{
	public function apply($image)
	{
		$image->fit($this->params['width'], $this->params['height'], function ($constraint) {
			$constraint->aspectRatio();
		});

		return true;
	}
}
