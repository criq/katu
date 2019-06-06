<?php

namespace Katu\Errors;

class Handler {

	const LOG_DIR   = 'logs';
	const ERROR_LOG = 'error.log';

	public function __invoke($request, $response, $exception) {
		return static::handleException($exception, $request, $response);
	}

	static function init() {
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', \Katu\Files\File::joinPaths(BASE_DIR, static::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', \Katu\Files\File::joinPaths(LOG_PATH, static::ERROR_LOG));
		}

		ini_set('display_errors', true);
		ini_set('error_log', ERROR_LOG);

		set_error_handler(function($code, $message, $file = null, $line = null, $context = null) {
			throw new \Exception(implode("; ", [
				$message,
				"file: " . $file,
				"line: " . $line,
				"context: " . @var_export($context, true),
			]), $code);
		});

		register_shutdown_function(function() {
			$error = error_get_last();
			if ($error) {
				throw new \Exception(implode("; ", [
					$error['message'],
					"file: " . $error['file'],
					"line: " . $error['line'],
				]), $error['type']);
			}
		});

		set_exception_handler(function($exception) {
			static::handleException($exception);
		});
	}

	static function log($message, $code = 0, $file = null, $line = null) {
		$log = new \Monolog\Logger('KatuLogger');
		$log->pushHandler(new \Monolog\Handler\StreamHandler(ERROR_LOG));
		$log->addError($message, [
			'code' => $code,
			'file' => $file,
			'line' => $line,
		]);

		return true;
	}

	static function handleException($exception, $request = null, $response = null) {
		if (class_exists('\\App\\Extensions\\Errors\\Handler') && method_exists('\\App\\Extensions\\Errors\\Handler', 'resolveException')) {
			return \App\Extensions\Errors\Handler::resolveException($exception, $request, $response);
		}

		return static::resolveException($exception, $request, $response);
	}

	static function resolveException($exception, $request = null, $response = null) {
		$controllerClass = \Katu\App::getControllerClass();

		try {
			throw $exception;
		} catch (Exceptions\NotFoundException $exception) {
			$controllerClass::renderNotFound();
		} catch (Exceptions\UnauthorizedException $exception) {
			$controllerClass::renderUnauthorized();
		} catch (Exceptions\UserErrorException $exception) {
			$controllerClass::renderError($exception->getMessage());
		}
	}

}
