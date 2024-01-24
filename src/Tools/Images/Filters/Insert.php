<?php

namespace Katu\Tools\Images\Filters;

class Insert extends \Katu\Tools\Images\Filter
{
	public function apply($image)
	{
		var_dump("A");die;

		$file = new \Katu\Files\File(\App\App::getBaseDir(), $this->params["source"]);
		var_dump($file);die;

		$image->insert(
			(new \Katu\Files\File(\App\App::getBaseDir(), $this->params["source"]))->getPath(),
			isset($this->params["position"]) ? $this->params["position"] : null,
			isset($this->params["x"]) ? $this->params["x"] : null,
			isset($this->params["y"]) ? $this->params["y"] : null
		);

		return true;
	}
}
