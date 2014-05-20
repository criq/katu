<?php

namespace Katu;

class ErrorHandler {

	const LOG_DIR   = 'logs';
	const ERROR_LOG = 'error.log';

	static function init() {
		// Constants.
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', Utils\FS::joinPaths(BASE_DIR, static::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', Utils\FS::joinPaths(LOG_PATH, static::ERROR_LOG));
		}



		// Error handler.
		$errorHandler = function($message, $level, $file, $line) {
			if ($message) {
				file_put_contents(static::ERROR_LOG, implode(array(
					'[' . \Katu\Utils\DateTime::get()->getDBDatetimeFormat() . ']',
					' Severity: ' . $level,
					'; Message: ' . $message,
					'; File: ' . $file,
					'; Line: ' . $line,
					"\r\n",
				)), FILE_APPEND);
			}
		};

		set_error_handler($errorHandler);

		register_shutdown_function(function() use($errorHandler) {
			$error = error_get_last();

			return $errorHandler($error['message'], $error['type'], $error['file'], $error['line']);
		});

		return TRUE;
	}

}
