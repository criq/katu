<?php

namespace Katu\Errors;

class Handler
{
	const ERROR_LOG = 'error.log';
	const LOG_DIR   = 'logs';

	public function __invoke($request, $response, $exception)
	{
		return static::handleException($exception, $request, $response);
	}

	public static function getLogDir()
	{
		return new \Katu\Files\File(\Katu\App::getBaseDir(), static::LOG_DIR);
	}

	public static function getErrorLog()
	{
		return new \Katu\Files\File(static::getLogDir(), static::ERROR_LOG);
	}

	public static function init()
	{
		ini_set('display_errors', true);
		ini_set('error_log', (string)static::getErrorLog());

		set_error_handler(function ($code, $message, $file = null, $line = null, $context = null) {
			throw new \Exception(implode("; ", [
				$message,
				"file: " . $file,
				"line: " . $line,
				"context: " . @var_export($context, true),
			]), $code);
		});

		register_shutdown_function(function () {
			$error = error_get_last();
			if ($error) {
				throw new \Exception(implode("; ", [
					$error['message'],
					"file: " . $error['file'],
					"line: " . $error['line'],
				]), $error['type']);
			}
		});

		set_exception_handler(function ($exception) {
			static::handleException($exception);
		});
	}

	public static function getLogger()
	{
		$logger = new \Monolog\Logger('KatuLogger');
		$logger->pushHandler(new \Monolog\Handler\StreamHandler((string)static::getErrorLog()));

		return $logger;
	}

	public static function log($message, $code = 0, $file = null, $line = null)
	{
		return static::getLogger()->error($message, [
			'code' => $code,
			'file' => $file,
			'line' => $line,
		]);
	}

	public static function handleException($exception, $request = null, $response = null)
	{
		if (class_exists('\\App\\Extensions\\Errors\\Handler') && method_exists('\\App\\Extensions\\Errors\\Handler', 'resolveException')) {
			return \App\Extensions\Errors\Handler::resolveException($exception, $request, $response);
		}

		return static::resolveException($exception, $request, $response);
	}

	public static function resolveException($exception, $request = null, $response = null)
	{
		$controllerClass = \Katu\App::getControllerClass();

		try {
			throw $exception;
		} catch (\Katu\Exceptions\NotFoundException $exception) {
			$controllerClass::renderNotFound();
		} catch (\Katu\Exceptions\UnauthorizedException $exception) {
			$controllerClass::renderUnauthorized();
		} catch (\Katu\Exceptions\UserErrorException $exception) {
			$controllerClass::renderError($exception->getMessage());
		}
	}
}
