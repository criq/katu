<?php

namespace Katu\Tools\Images\Filters;

class Blur extends \Katu\Tools\Images\Filter {

	public function apply($image) {
		$image->blur($this->params['level']);

		return true;
	}

}
