<?php

namespace Katu;

class App
{
	public static $app = null;

	public static function getExtendedClassName($appClassName, $fallbackClassName) : \Katu\Tools\Classes\ClassName
	{
		return class_exists((string)$appClassName) ? $appClassName : $fallbackClassName;
	}

	public static function getControllerClassName() : \Katu\Tools\Classes\ClassName
	{
		return static::getExtendedClassName(new \Katu\Tools\Classes\ClassName('App', 'Extensions', 'Controllers', 'Controller'), new \Katu\Tools\Classes\ClassName('Katu', 'Controllers', 'Controller'));
	}

	public static function getViewClassName() : \Katu\Tools\Classes\ClassName
	{
		return static::getExtendedClassName(new \Katu\Tools\Classes\ClassName('App', 'Extensions', 'Views', 'View'), new \Katu\Tools\Classes\ClassName('Katu', 'Views', 'View'));
	}

	public static function getErrorHandlerClassName() : \Katu\Tools\Classes\ClassName
	{
		return static::getExtendedClassName(new \Katu\Tools\Classes\ClassName('App', 'Extensions', 'Errors', 'Handler'), new \Katu\Tools\Classes\ClassName('Katu', 'Errors', 'Handler'));
	}

	public static function getBaseDir()
	{
		return new \Katu\Files\File(realpath(__DIR__ . '/../../../../'));
	}

	public static function getFileDir()
	{
		return \Katu\Models\Presets\File::getDir();
	}

	public static function getTemporaryDir()
	{
		try {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Config\Config::get('app', 'tmp', 'dir'));
		} catch (\Throwable $e) {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Files\Temporary::DEFAULT_DIR);
		}
	}

	public static function getPublicTemporaryDir()
	{
		try {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Config\Config::get('app', 'tmp', 'publicDir'));
		} catch (\Throwable $e) {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Files\Temporary::DEFAULT_PUBLIC_DIR_NAME);
		}
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
				$errorHandlerClass = (string)static::getErrorHandlerClassName();

				return new $errorHandlerClass;
			};

			static::$app = new \Slim\App($config);
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
