<?php

namespace Katu\Image\Filters;

class Blur extends \Katu\Image\Filter {

	public function apply($image) {
		$image->blur($this->params['level']);

		return true;
	}

}
