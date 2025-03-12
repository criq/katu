<?php

namespace Katu\Tools\Images\Filters;

use Intervention\Image\Image;

class InsertFilter extends \Katu\Tools\Images\Filter
{
	public function apply(Image $image): bool
	{
		$file = new \Katu\Files\File(\App\App::getBaseDir(), $this->params["source"]);

		$image->insert(
			$file->getPath(),
			isset($this->params["position"]) ? $this->params["position"] : null,
			isset($this->params["x"]) ? $this->params["x"] : null,
			isset($this->params["y"]) ? $this->params["y"] : null
		);

		return true;
	}
}
