<?php

namespace Katu\Tools\Images\Filters;

class Insert extends \Katu\Tools\Images\Filter {

	public function apply($image) {
		$image->insert(
			(new \Katu\Files\File($this->params['source']))->getPath(),
			isset($this->params['position']) ? $this->params['position'] : null,
			isset($this->params['x']) ? $this->params['x'] : null,
			isset($this->params['y']) ? $this->params['y'] : null
		);

		return true;
	}

}
