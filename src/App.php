<?php

namespace Katu;

use Katu\Types\TClass;
use Katu\Types\TIdentifier;

class App
{
	public static $app = null;

	/****************************************************************************
	 * Paths.
	 */
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

	/****************************************************************************
	 * Classes.
	 */
	public static function getExceptionHandlerClass(): TClass
	{
		return new TClass("Katu\Exceptions\Handler");
	}

	public static function getControllerClass(): TClass
	{
		return new TClass("Katu\Controllers\Controller");
	}

	public static function getLoggerClass(): TClass
	{
		return new TClass("\Katu\Tools\Logs\Logger");
	}

	public static function getLogger(TIdentifier $identifier): \Katu\Tools\Logs\Logger
	{
		$loggerClassName = static::getLoggerClass()->getName();

		return new $loggerClassName($identifier);
	}

	/****************************************************************************
	 * Models.
	 */
	public static function getAccessTokenModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\AccessToken");
	}

	public static function getEmailAddressModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\EmailAddress");
	}

	public static function getFileModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\File");
	}

	public static function getFileAttachmentModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\FileAttachment");
	}

	public static function getSettingModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\Setting");
	}

	public static function getRoleModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\Role");
	}

	public static function getRolePermissionModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\RolePermission");
	}

	public static function getUserModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\User");
	}

	public static function getUserPermissionModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\UserPermission");
	}

	public static function getUserRoleModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\UserRole");
	}

	public static function getUserServiceModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\UserService");
	}

	public static function getUserSettingModelClass(): TClass
	{
		return new TClass("Katu\Models\Presets\UserSetting");
	}

	/****************************************************************************
	 * Autoload.
	 */
	public static function getAutoloadRegisterFunctions(): array
	{
		return [];
	}

	/****************************************************************************
	 * Run.
	 */
	public static function get(): \Slim\App
	{
		if (!static::$app) {
			// Timezone.
			try {
				date_default_timezone_set(\Katu\Config\Config::get("app", "timezone"));
			} catch (\Throwable $e) {
				// Just use default timezone.
			}

			// Autoload.
			foreach (static::getAutoloadRegisterFunctions() as $registerFunction) {
				spl_autoload_register($registerFunction);
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
			$config["errorHandler"] = function (\Slim\Container $container) {
				return function () {
					return static::getExceptionHandlerClass()->getName()::resolveException(func_get_arg(2), func_get_arg(0), func_get_arg(1));
				};
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
				foreach ((array)\Katu\Config\Config::get("routes") as $name => $route) {
					$pattern  = $route->getPattern();
					if (!$pattern) {
						throw new \Katu\Exceptions\RouteException("Invalid pattern for route \"{$name}\".");
					}

					$callable = $route->getCallable();
					if (!$callable) {
						throw new \Katu\Exceptions\RouteException("Invalid callable for route \"{$name}\".");
					}

					$slimRoute = $app->map($route->getMethods(), $pattern, $callable);
					if (is_string($name) && trim($name)) {
						$slimRoute->setName($name);
					} elseif ($route->name) {
						$slimRoute->setName($route->getName());
					}
				}
			} catch (\Katu\Exceptions\RouteException $e) {
				throw $e;
			}

			// Run the app.
			$app->run();
		} catch (\Throwable $e) {
			throw $e;
		}
	}
}
