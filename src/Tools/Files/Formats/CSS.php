<?php

namespace Katu\Tools\Files\Formats;

class CSS {

	static function implode() {
		try {
			$css_files = array();

			$path = FileSystem::joinPaths(BASE_DIR, \Katu\Config::getApp('css', 'path'));
			if (!file_exists($path)) {
				throw new \Exception("Invalid CSS path.");
			}

			// Get directories.
			foreach (scandir($path) as $file) {
				if ($file != '.' && $file != '..') {
					$subdir_path = realpath(FileSystem::joinPaths($path, $file));
					if (is_dir($subdir_path)) {
						foreach (scandir($subdir_path) as $css_file) {
							if ($css_file != '.' && $css_file != '..' && preg_match('#\.css$#', $css_file)) {
								$css_files[] = realpath(FileSystem::joinPaths($subdir_path, $css_file));
							}
						}
					}
				}
			}

			$css = null;

			// Implode.
			foreach ($css_files as $css_file) {
				$css .= file_get_contents($css_file);
			}

			$imploded_path = FileSystem::joinPaths(BASE_DIR, \Katu\Config::getApp('css', 'path'), \Katu\Config::getApp('css', 'filename'));
			file_put_contents($imploded_path, "\n" . $css . "\n");

		} catch (\Exception $e) {

			return false;
		}

		return true;
	}

}
