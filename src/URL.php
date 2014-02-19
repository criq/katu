<?php

namespace Elementary;

class URL {

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return trim($i, '/');
		}, func_get_args()));
	}

}
