<?php

namespace Katu\Utils;

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

	static function getPathForName($name) {
		$name = is_array($name) ? $name : [$name];
		$segments = [];

		// URL.
		foreach ($name as $namePart) {

			try {
				$parts = (new \Katu\Types\TUrl($namePart))->getParts();
				$segments = array_merge($segments, [$parts['scheme']], array_reverse(explode('.', $parts['host'])), [$parts['path']], [$parts['query']]);
			} catch (\Exception $e) {
				$segments[] = $namePart;
			}

		}

		// Filter out slashes.
		$segments = array_map(function($i) {
			if (is_string($i)) {
				return trim($i, '/');
			}

			return $i;
		}, $segments);

		// Filter empty ones.
		$segments = array_values(array_filter($segments));

		// Explode "/" hashes into segments.
		$_segments = [];
		foreach ($segments as $segment) {
			if (is_string($segment)) {
				foreach (explode('/', trim($segment, '/')) as $e) {
					$_segments[] = $e;
				}
			} else {
				$_segments[] = sha1(serialize($segment));
			}
		}

		$segments = $_segments;

		// Sanitize.
		foreach ($segments as &$segment) {
			$segment = ltrim($segment, '.');
			$segment = preg_replace('#[^a-z0-9\.\-_]#i', '_', $segment);
		}

		// Segments into folders.
		foreach ($segments as &$segment) {

			// Hashes.
			if (preg_match('#^[0-9a-f]{40}$#', $segment)) {
				$segment = implode('/', [
					substr($segment, 0, 2),
					substr($segment, 2, 2),
					substr($segment, 4, 2),
					$segment,
				]);

			// Any other string.
			} else {
				$segment = implode('/', [
					mb_strtoupper(substr($segment, 0, 1)),
					$segment,
				]);
			}

		}

		// Attach hashed hidden file name at the end.
		$segments[] = '.' . sha1(serialize($name));

		$path = implode('/', $segments);

		return $path;
	}

}
