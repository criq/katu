<?php

namespace Katu\File;

use \Katu\Classes\FileSystemPathSegments;

class System {

	static $names = [];

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
		return new FileSize(disk_total_space(\Katu\App::getBaseDir()));
	}

	static function getFreeDiskSpace() {
		return new FileSize(disk_free_space(\Katu\App::getBaseDir()));
	}

	static function getDiskUsage() {
		return 1 - (static::getFreeDiskSpace()->size / static::getDiskSpace()->size);
	}

	static function getSpaceAboveFreeTreshold($treshold) {
		return new FileSize(static::getDiskSpace()->size - (static::getDiskSpace()->size * $treshold) - static::getFreeDiskSpace()->size);
	}

}
