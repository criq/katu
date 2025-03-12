<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class ResizeFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$image->resize($this->params["width"], $this->params["height"], function ($constraint) {
			$constraint->aspectRatio();
			if ($this->params["dontUpsize"] ?? null) {
				$constraint->upsize();
			}
		});

		return true;
	}
}
