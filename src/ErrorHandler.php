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
		set_exception_handler(array('static', 'exceptionHandler'));

		register_shutdown_function(function() {
			$error = error_get_last();

			call_user_func_array(array('static', 'errorHandler'), array($error['message'], $error['type'], $error['file'], $error['line']));
		});

		return TRUE;
	}

	static function errorHandler($message, $level = 0, $file = NULL, $line = NULL) {
		throw new \ErrorException($message, 0, $level, $file, $line);
	}

	static function exceptionHandler($exception) {
		return static::log($exception->getMessage());
	}

	static function log($message, $level = 0, $file = NULL, $line = NULL) {
		$log = new \Monolog\Logger('app');
		$log->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$log->addError($message, array(
			'level' => $level,
			'file'  => $file,
			'line'  => $line,
		));

		return TRUE;
	}

}
