<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class GreyscaleFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->greyscale();

		return true;
	}
}
