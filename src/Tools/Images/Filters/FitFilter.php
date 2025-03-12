<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class FitFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->fit($this->params["width"], $this->params["height"], function ($constraint) {
			$constraint->aspectRatio();
		});

		return true;
	}
}
