<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class SharpenFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->sharpen($this->params["level"]);

		return true;
	}
}
