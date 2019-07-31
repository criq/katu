<?php

namespace Katu\Tools\Images\Filters;

class Greyscale extends \Katu\Tools\Images\Filter {

	public function apply($image) {
		$image->greyscale();

		return true;
	}

}
