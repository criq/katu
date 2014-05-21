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

		set_error_handler(array('static', 'errorHandler'));

		register_shutdown_function(function() {
			$error = error_get_last();

			call_user_func_array(array('static', 'errorHandler'), array($error['message'], $error['type'], $error['file'], $error['line']));
		});

		return TRUE;
	}

	static function errorHandler($message, $level, $file, $line) {
		if ($message) {
			file_put_contents(ERROR_LOG, implode(array(
				'[' . Utils\DateTime::get()->getDBDatetimeFormat() . ']',
				' Severity: ' . $level,
				'; Message: ' . $message,
				'; File: ' . $file,
				'; Line: ' . $line,
				"\r\n",
			)), FILE_APPEND);
		}

		return TRUE;
	}

	static function errorLog($message, $level = 0, $file = NULL, $line = NULL) {
		return static::errorHandler($message, $level, $file, $line);
	}

}
