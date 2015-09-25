<?php

namespace Katu\Utils;

use \Katu\Classes\FileSystemPathSegment;
use \Katu\Classes\FileSystemPathSegments;

class FileSystem {

	static $names = [];

	static function joinPaths() {
		return implode('/', array_map(function($i) {

			return rtrim(implode('.', (array) $i), '/');

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

	static function getMime($path) {
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $path);
		finfo_close($finfo);

		return $mime;
	}

	static function getPathForName($nameParts, $options = []) {
		return Cache::getFromMemory(['fileSystemName', $nameParts], function($nameParts, $options) {

			$nameParts = is_array($nameParts) ? $nameParts : [$nameParts];
			$nameParts = array_values(array_filter($nameParts));

			$segments = new FileSystemPathSegments();

			// Special treatment of URLs.
			foreach ($nameParts as $namePart) {

				try {
					$urlParts = (new \Katu\Types\TUrl($namePart))->getParts();
					$segments->add('!' . $urlParts['scheme']);
					foreach (array_reverse(explode('.', $urlParts['host'])) as $segment) {
						$segments->add('!' . $segment);
					}
					$segments->add('!' . $urlParts['path']);
					$segments->add($urlParts['query']);
				} catch (\Exception $e) {
					$segments->add($namePart);
				}

			}

			// Get path segments.
			$segments = $segments->getPathSegments();

			// Attach hashed hidden file name at the end.
			$segments[] = '.' . sha1(serialize($segments));

			return implode('/', $segments);

		}, $nameParts, $options);
	}

	static function touch($path) {
		@mkdir(dirname($path), 0777, true);

		return touch($path);
	}

	static function getDirs($parentDir) {
		$dirs = [];

		foreach (scandir($parentDir) as $file) {
			if (!in_array($file, ['.', '..'])) {
				$path = static::joinPaths($parentDir, $file);
				if (is_dir($path)) {
					$dirs[] = $path;
				}
			}
		}

		return $dirs;
	}

	static function deleteDir($dir) {
		$it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($files as $file) {
			if ($file->isDir()) {
				rmdir($file->getRealPath());
			} else {
				unlink($file->getRealPath());
			}
		}

		rmdir($dir);
	}

	static function getDiskSpace() {
		return new FileSize(disk_total_space(BASE_DIR));
	}

	static function getFreeDiskSpace() {
		return new FileSize(disk_free_space(BASE_DIR));
	}

	static function getDiskUsage() {
		return 1 - (static::getFreeDiskSpace()->size / static::getDiskSpace()->size);
	}

	static function getSpaceAboveFreeTreshold($treshold) {
		return new FileSize(static::getDiskSpace()->size - (static::getDiskSpace()->size * $treshold) - static::getFreeDiskSpace()->size);
	}

}
