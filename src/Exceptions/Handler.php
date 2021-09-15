<?php

namespace Katu\Exceptions;

use Katu\Types\TIdentifier;

class Handler
{
	const ERROR_LOG = 'error.log';

	public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, \Throwable $exception)
	{
		return static::handleException($exception, $request, $response);
	}

	public static function getLogger()
	{
		return new \Katu\Tools\Logs\Logger(new TIdentifier('error'));
	}

	public static function init()
	{
		ini_set('display_errors', false);
		ini_set('error_log', (string)static::getLogger()->getFile());

		set_error_handler(function ($code, $message, $file = null, $line = null, $context = null) {
			throw new \Exception(implode("; ", [
				$message,
				"file: " . $file,
				"line: " . $line,
				"context: " . print_r($context, true),
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

	public static function log($error, $code = 0, $file = null, $line = null)
	{
		$data = [
			'error' => $error,
			'file' => $file,
			'line' => $line,
			'code' => $code,
		];

		return static::getLogger('error')->error($error, $data);
	}

	public static function handleException(\Throwable $exception, \Slim\Http\Request $request = null, \Slim\Http\Response $response = null)
	{
		$className = \Katu\App::getErrorHandlerClass()->getName();

		return $className::resolveException($exception, $request, $response);
	}

	public static function resolveException(\Throwable $exception, \Slim\Http\Request $request = null, \Slim\Http\Response $response = null)
	{
		$controllerClassName = \Katu\App::getControllerClass()->getName();
		$controller = new $controllerClassName(\Katu\App::get()->getContainer());

		try {
			throw $exception;
		} catch (\Katu\Exceptions\NotFoundException $exception) {
			return $controller->renderNotFound();
		} catch (\Katu\Exceptions\UnauthorizedException $exception) {
			return $controller->renderUnauthorized();
		} catch (\Katu\Exceptions\UserErrorException $exception) {
			return $controller->renderError($exception->getMessage());
		}
	}
}
