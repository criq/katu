<?php

namespace Jabli\Utils;

class FS {

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return rtrim($i, '/');
		}, func_get_args()));
	}

}
