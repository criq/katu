<?php

namespace Katu\Utils;

class FS {

	static function joinPaths() {
		return implode('/', array_map(function($i){
			return rtrim($i, '/');
		}, func_get_args()));
	}

	static function getTree($dir) {
		if (!function_exists('__scandirr')) {

			function __scandirr($dir, &$files = array()) {
				foreach (scandir($dir) as $file) {
					$path = $dir . '/' . $file;
					if ($file != '.' && $file != '..') {
						if (is_dir($path)) {
							$files[] = realpath($path);
							$files[] = __scandirr($path, $files);
						} else {
							$files[] = realpath($path);
						}
					}
				}
			}

		}

		__scandirr($dir, $files);

		return array_values(array_filter($files));
	}

	static function getSize($path) {
		clearstatcache();

		return filesize($path);
	}

}
