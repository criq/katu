<?php

namespace Katu\Utils;

use \Katu\Classes\FileSystemPathSegment;
use \Katu\Classes\FileSystemPathSegments;

class FileSystem {

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

	static function getPathForName($nameParts) {
		$nameParts = is_array($nameParts) ? $nameParts : [$nameParts];
		$segments = new FileSystemPathSegments();

		// Special treatment of URLs.
		foreach ($nameParts as $namePart) {

			try {
				$urlParts = (new \Katu\Types\TUrl($namePart))->getParts();
				$segments->add((new FileSystemPathSegment($urlParts['scheme']))->disablePrefixFolder());
				foreach (array_reverse(explode('.', $urlParts['host'])) as $segment) {
					$segments->add((new FileSystemPathSegment($segment))->disablePrefixFolder());
				}
				$segments->add(new FileSystemPathSegment($urlParts['path']));
				$segments->add(new FileSystemPathSegment($urlParts['query']));
			} catch (\Exception $e) {
				if ($namePart instanceof FileSystemPathSegment) {
					$segments->add($namePart);
				} else {
					$segments->add(new FileSystemPathSegment($namePart));
				}
			}

		}

		// Get path segments.
		$segments = $segments->getPathSegments();

		// Attach hashed hidden file name at the end.
		$segments[] = '.' . sha1(serialize($segments));

		$path = implode('/', $segments);

		return $path;
	}

	static function touch($path) {
		@mkdir(dirname($path), 0777, true);

		return touch($path);
	}

}
