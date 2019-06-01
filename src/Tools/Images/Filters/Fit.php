<?php

namespace Katu\Image\Filters;

class Fit extends \Katu\Image\Filter {

	public function apply($image) {
		$image->fit($this->params['width'], $this->params['height'], function($constraint) {
			$constraint->aspectRatio();
		});

		return true;
	}

}
