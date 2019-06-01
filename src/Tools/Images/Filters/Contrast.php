<?php

namespace Katu\Image\Filters;

class Contrast extends \Katu\Image\Filter {

	public function apply($image) {
		$image->contrast($this->params['level']);

		return true;
	}

}
