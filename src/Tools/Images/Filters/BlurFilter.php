<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class BlurFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->blur($this->params["level"]);

		return true;
	}
}
