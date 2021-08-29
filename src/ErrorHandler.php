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
			define('LOG_PATH', Utils\FileSystem::joinPaths(BASE_DIR, static::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', Utils\FileSystem::joinPaths(LOG_PATH, static::ERROR_LOG));
		}

		ini_set('display_errors', false);
		ini_set('error_log', ERROR_LOG);

		set_exception_handler(function ($exception) {
			static::handle($exception);

			return true;
		});

		set_error_handler(function ($message, $level = 0, $file = null, $line = null) {
			throw new \ErrorException($message, 0, $level, $file, $line);
		});

		register_shutdown_function(function() {
			$error = error_get_last();
			if ($error) {
				throw new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
			}
		});

		return true;
	}

	static function log($message, $level = 0, $file = null, $line = null) {
		$log = new \Monolog\Logger('KatuLogger');
		$log->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$log->addError($message, array(
			'level' => $level,
			'file'  => $file,
			'line'  => $line,
		));

		return true;
	}

	static function handle($e) {
		if (class_exists('\App\Extensions\ErrorHandler') && method_exists('\App\Extensions\ErrorHandler', 'resolveException')) {
			return \App\Extensions\ErrorHandler::resolveException($e);
		}

		return static::resolveException($e);
	}

	static function resolveException($e) {
		$controllerClass = \Katu\App::getControllerClass();

		try {
			throw $e;
		} catch (Exceptions\NotFoundException $e) {
			$controllerClass::renderNotFound();
		} catch (Exceptions\UnauthorizedException $e) {
			$controllerClass::renderUnauthorized();
		} catch (Exceptions\UserErrorException $e) {
			$controllerClass::renderError($e->getMessage());
		}
	}

}
