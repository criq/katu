<?php

namespace Katu;

class App
{
	public static $app = null;

	public static function getExtendedClass($appClassName, $fallbackName)
	{
		return class_exists($appClassName) ? $appClassName : $fallbackName;
	}

	public static function getControllerClass()
	{
		return static::getExtendedClass('\\App\\Extensions\\Controllers\\Controller', '\\Katu\\Controllers\\Controller');
	}

	public static function getViewClass()
	{
		return static::getExtendedClass('\\App\\Extensions\\Views\\View', '\\Katu\\Views\\View');
	}

	public static function getErrorHandlerClass()
	{
		return static::getExtendedClass('\\App\\Extensions\\Errors\\Handler', '\\Katu\\Errors\\Handler');
	}

	public static function getBaseDir()
	{
		return new \Katu\Files\File(realpath(__DIR__ . '/../../../../'));
	}

	public static function getFileDir()
	{
		return new \Katu\Files\File(static::getBaseDir(), 'files');
	}

	public static function getTmpDir()
	{
		return new \Katu\Files\File(static::getBaseDir(), 'tmp');
	}

	public static function init()
	{
		// Timezone.
		try {
			date_default_timezone_set(\Katu\Config\Config::get('app', 'timezone'));
		} catch (\Throwable $e) {
			// Just use default timezone.
		}

		// Session.
		\Katu\Tools\Session\Session::setCookieParams();

		return true;
	}

	public static function get()
	{
		if (!static::$app) {
			static::init();

			try {
				$config = \Katu\Config\Config::get('app', 'slim');
			} catch (\Throwable $e) {
				$config = [];
			}

			$config['errorHandler'] = function ($c) {
				$errorHandlerClass = static::getErrorHandlerClass();
				return new $errorHandlerClass;
			};

			// TODO - je zapotřebí?
			/*
			$config['settings']['logger'] = [
				'name' => 'KatuLogger',
				'level' => \Monolog\Logger::DEBUG,
				'path' => ERROR_LOG,
			];
			*/

			static::$app = new \Slim\App($config);
			#static::$app->add(new \Psr7Middlewares\Middleware\TrailingSlash(false));

			// Add error middleware.
			// TODO - updatovat
			#static::$app->add(new \Katu\Middleware\Error);

			// Add profiler middleware.
			// TODO - updatovat
			#$app->add(new Middleware\Profiler());
		}

		return static::$app;
	}

	public static function run()
	{
		$app = static::get();

		try {
			try {
				// Set up routes.
				foreach ((array)\Katu\Config\Config::get('routes') as $name => $route) {
					$pattern  = $route->getPattern();
					if (!$pattern) {
						throw new \Katu\Exceptions\RouteException("Invalid pattern for route " . $name . ".");
					}

					$callable = $route->getCallable();
					if (!$callable) {
						throw new \Katu\Exceptions\RouteException("Invalid callable for route " . $name . ".");
					}

					$slimRoute = $app->map($route->getMethods(), $pattern, $callable);
					if (is_string($name) && trim($name)) {
						$slimRoute->setName($name);
					} elseif ($route->name) {
						$slimRoute->setName($route->name);
					}
				}
			} catch (\Katu\Exceptions\RouteException $e) {
				throw $e;
			} catch (\Throwable $e) {
				// Nothing to do, no custom routes defined.
			}

			// Autoload.
			if (class_exists('\\App\\Extensions\\Autoload')) {
				foreach ((array)\App\Extensions\Autoload::getRegisterFunctions() as $registerFunction) {
					spl_autoload_register($registerFunction);
				}
			}

			// Run the app.
			$app->run();
		} catch (\Throwable $e) {
			throw $e;
		}
	}

	public static function getRequest() : \Slim\Http\Request
	{
		return static::get()->getContainer()->request;
	}

	public static function isProfilerOn() : bool
	{
		return \Katu\Cache\Runtime::get('profiler.on', function () {
			try {
				return \Katu\Config\Config::get('app', 'profiler');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				return false;
			}
		});
	}
}
