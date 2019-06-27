<?php

namespace Katu\Image\Filters;

class Greyscale extends \Katu\Image\Filter {

	public function apply($image) {
		$image->greyscale();

		return true;
	}

}
