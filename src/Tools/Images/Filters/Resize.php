<?php

namespace Katu\Tools\Images\Filters;

class Resize extends \Katu\Tools\Images\Filter {

	public function apply($image) {
		$image->resize($this->params['width'], $this->params['height'], function($constraint) {
			$constraint->aspectRatio();
		});

		return true;
	}

}
