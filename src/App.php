<?php

namespace Katu;

use Katu\Types\TClass;

class App
{
	public static $app = null;

	public static function getExceptionHandlerClass(): TClass
	{
		return new TClass("Katu\Exceptions\Handler");
	}

	public static function getControllerClass(): TClass
	{
		return new TClass("Katu\Controllers\Controller");
	}

	public static function getBaseDir(): \Katu\Files\File
	{
		return new \Katu\Files\File(realpath(__DIR__ . "/../../../../"));
	}

	public static function getFileDir(): \Katu\Files\File
	{
		return \Katu\Models\Presets\File::getDir();
	}

	public static function getTemporaryDir(): \Katu\Files\File
	{
		return new \Katu\Files\File(static::getBaseDir(), \Katu\Files\Temporary::DEFAULT_DIR);
	}

	public static function getPublicTemporaryDir(): \Katu\Files\File
	{
		try {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Config\Config::get("app", "tmp", "publicDir"));
		} catch (\Throwable $e) {
			return new \Katu\Files\File(static::getBaseDir(), \Katu\Files\Temporary::DEFAULT_PUBLIC_DIR_NAME);
		}
	}

	public static function getFileModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\File");
	}

	public static function get(): \Slim\App
	{
		if (!static::$app) {
			// Timezone.
			try {
				date_default_timezone_set(\Katu\Config\Config::get("app", "timezone"));
			} catch (\Throwable $e) {
				// Just use default timezone.
			}

			// Session.
			\Katu\Tools\Session\Session::setCookieParams();

			// Slim config.
			try {
				$config = \Katu\Config\Config::get("app", "slim");
			} catch (\Throwable $e) {
				$config = [];
			}

			// Error handler.
			$config["errorHandler"] = function () {
				$errorHandlerClass = static::getExceptionHandlerClass()->getName();

				return new $errorHandlerClass;
			};

			// Autoload.
			if (class_exists("\\App\\Classes\\Autoload")) {
				foreach ((array)\App\Classes\Autoload::getRegisterFunctions() as $registerFunction) {
					spl_autoload_register($registerFunction);
				}
			}

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
				foreach ((array)\Katu\Config\Config::get("routes") as $name => $route) {
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

			// Run the app.
			$app->run();
		} catch (\Throwable $e) {
			throw $e;
		}
	}
}
