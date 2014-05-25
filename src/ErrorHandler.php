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

		ini_set('display_errors', FALSE);
		ini_set('error_log', ERROR_LOG);

		set_error_handler(function ($message, $level = 0, $file = NULL, $line = NULL) {
			throw new \ErrorException($message, 0, $level, $file, $line);
		});

		set_exception_handler(function ($exception) {
			static::handle($exception);

			return TRUE;
		});

		register_shutdown_function(function () {
			$error = error_get_last();
			if ($error) {
				throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
			}
		});

		return TRUE;
	}

	static function log($message, $level = 0, $file = NULL, $line = NULL) {
		$log = new \Monolog\Logger('KatuLogger');
		$log->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$log->addError($message, array(
			'level' => $level,
			'file'  => $file,
			'line'  => $line,
		));

		return TRUE;
	}

	static function handle($e) {
		if (class_exists('\App\Extensions\ErrorHandler') && method_exists('\App\Extensions\ErrorHandler', 'resolveException')) {
			return \App\Extensions\ErrorHandler::resolveException($e);
		}

		return static::resolveException($e);
	}

	static function resolveException($e) {
		try {

			throw $e;

		} catch (Exceptions\NotFoundException $e) {

			Controller::renderNotFound();

		} catch (Exceptions\UnauthorizedException $e) {

			Controller::renderUnauthorized();

		} catch (Exceptions\UserErrorException $e) {

			Controller::renderError($e->getMessage());

		}
	}

}
