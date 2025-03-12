<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class ContrastFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->contrast($this->params["level"]);

		return true;
	}
}
