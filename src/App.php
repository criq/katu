<?php

namespace Katu;

use DI\Factory\RequestedEntry;
use Katu\Tools\Views\ViewEngineInterface;
use Katu\Types\TClass;
use Katu\Types\TIdentifier;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

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
	public static function getLogger(TIdentifier $identifier): LoggerInterface
	{
		return new \Katu\Tools\Logs\Logger($identifier);
	}

	public static function getErrorHandler(): ?callable
	{
		return function (ServerRequestInterface $request, \Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails, ?LoggerInterface $logger = null ): ResponseInterface
		{
			$logger = $logger ?: static::getLogger(new TIdentifier("error"));
			$logger->error($exception);

			$response = static::$app->getResponseFactory()->createResponse();

			return $response->withStatus(500);
		};
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

			// Create the dependency injection container.
			$builder = new \DI\ContainerBuilder();
			$builder->addDefinitions([
				\Psr\Log\LoggerInterface::class => \DI\factory(function (TIdentifier $identifier) {
					return static::getLogger($identifier);
				}),
			]);

			// Create the app.
			static::$app = \DI\Bridge\Slim\Bridge::create($builder->build());

			// Add body parsing middleware.
			static::$app->addBodyParsingMiddleware();

			// Set up routes.
			foreach ((array)\Katu\Config\Config::get("routes") as $name => $route) {
				$pattern = $route->getPattern();
				if (!$pattern) {
					throw new \Katu\Exceptions\RouteException("Invalid pattern for route \"{$name}\".");
				}

				$callback = $route->getCallback();
				if (!$callback) {
					throw new \Katu\Exceptions\RouteException("Invalid callable for route \"{$name}\".");
				}

				$slimRoute = static::$app->map($route->getMethods(), $pattern, $callback);
				if (is_string($name) && trim($name)) {
					$slimRoute->setName($name);
				} elseif ($route->getName()) {
					$slimRoute->setName($route->getName());
				}
			}

			// Add Error Middleware.
			try {
				$displayErrorDetails = \Katu\Config\Config::get("app", "slim", "settings", "displayErrorDetails");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$displayErrorDetails = false;
			}

			$errorMiddleware = static::$app->addErrorMiddleware((bool)$displayErrorDetails, true, true);
			$errorMiddleware->setDefaultErrorHandler(static::getErrorHandler());
		}

		return static::$app;
	}

	public static function getContainer(): ContainerInterface
	{
		return static::get()->getContainer();
	}
}
