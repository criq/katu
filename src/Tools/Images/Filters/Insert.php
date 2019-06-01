<?php

namespace Katu\Image\Filters;

class Insert extends \Katu\Image\Filter {

	public function apply($image) {
		$image->insert(
			(new \Katu\Utils\File($this->params['source']))->getPath(),
			isset($this->params['position']) ? $this->params['position'] : null,
			isset($this->params['x']) ? $this->params['x'] : null,
			isset($this->params['y']) ? $this->params['y'] : null
		);

		return true;
	}

}
