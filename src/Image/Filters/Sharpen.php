<?php

namespace Katu\Image\Filters;

class Sharpen extends \Katu\Image\Filter {

	public function apply($image) {
		$image->sharpen($this->params['level']);

		return true;
	}

}
