<?php

namespace Katu\Errors;

class Handler {

	const LOG_DIR   = 'logs';
	const ERROR_LOG = 'error.log';

	static function init() {
		// Constants.
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', \Katu\Tools\Files\File::joinPaths(BASE_DIR, static::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', \Katu\Tools\Files\File::joinPaths(LOG_PATH, static::ERROR_LOG));
		}

		ini_set('display_errors', true);
		ini_set('error_log', ERROR_LOG);

		set_error_handler(function($code, $message, $file = null, $line = null) {
			throw new \Katu\Exceptions\Exception($message . " in " . $file . ", line " . $line, $code);
		});

		set_exception_handler(function($exception) {
			static::handleException($exception);
			return true;
		});

		register_shutdown_function(function () {
			$error = error_get_last();
			if ($error) {
				throw new \Katu\Exceptions\ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
			}
		});

		return true;
	}

	static function log($message, $code = 0, $file = null, $line = null) {
		$log = new \Monolog\Logger('KatuLogger');
		$log->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$log->addError($message, array(
			'code' => $code,
			'file' => $file,
			'line' => $line,
		));

		return true;
	}

	static function handleException($e) {
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
