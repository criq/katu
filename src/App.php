<?php

namespace Katu;

use Katu\Files\File;
use Katu\Files\FileCollection;
use Katu\Tools\Session\Session;
use Katu\Types\TIdentifier;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class App
{
	private static $instance = null;

	private function __construct()
	{
	}

	private function __clone()
	{
	}

	/****************************************************************************
	 * Paths.
	 */
	public static function getBaseDir(): File
	{
		return new File(realpath(__DIR__ . "/../../../../"));
	}

	public static function getEnvFiles(): FileCollection
	{
		return new FileCollection([
			new File(static::getBaseDir(), ".env"),
		]);
	}

	public static function getLogsDir(): File
	{
		return new File(static::getBaseDir(), "logs");
	}

	public static function getFileDir(): File
	{
		return new File(static::getBaseDir(), "files");
	}

	public static function getTemporaryDir(): File
	{
		return new File(static::getBaseDir(), "tmp");
	}

	public static function getPublicTemporaryDir(): File
	{
		return new File(static::getBaseDir(), "public", "tmp");
	}

	public static function getAppDir(): File
	{
		return new File(static::getBaseDir(), "app");
	}

	public static function getConfigDir(): File
	{
		return new File(static::getAppDir(), "Config");
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

			$response = static::$instance->getResponseFactory()->createResponse();

			return $response->withStatus(500);
		};
	}

	/****************************************************************************
	 * Autoload.
	 */
	public static function getAutoloadRegisterFunctions(): array
	{
		return [];
	}

	/****************************************************************************
	 * DI.
	 */
	public static function getDIDefinitions(): array
	{
		return [];
	}

	/****************************************************************************
	 * Run.
	 */
	public static function get(): \Slim\App
	{
		if (!static::$instance) {
			// Create the dependency injection container.
			$builder = new \DI\ContainerBuilder;
			$builder->addDefinitions(array_merge([
				\Psr\Log\LoggerInterface::class => \DI\factory(function (TIdentifier $identifier) {
					return static::getLogger($identifier);
				}),
				\Katu\Models\Presets\AccessToken::class => \Katu\Models\Presets\AccessToken::class,
				\Katu\Models\Presets\EmailAddress::class => \Katu\Models\Presets\EmailAddress::class,
				\Katu\Models\Presets\File::class => \Katu\Models\Presets\File::class,
				\Katu\Models\Presets\FileAttachment::class => \Katu\Models\Presets\FileAttachment::class,
				\Katu\Models\Presets\Role::class => \Katu\Models\Presets\Role::class,
				\Katu\Models\Presets\RolePermission::class => \Katu\Models\Presets\RolePermission::class,
				\Katu\Models\Presets\Setting::class => \Katu\Models\Presets\Setting::class,
				\Katu\Models\Presets\User::class => \Katu\Models\Presets\User::class,
				\Katu\Models\Presets\UserPermission::class => \Katu\Models\Presets\UserPermission::class,
				\Katu\Models\Presets\UserRole::class => \Katu\Models\Presets\UserRole::class,
				\Katu\Models\Presets\UserService::class => \Katu\Models\Presets\UserService::class,
				\Katu\Models\Presets\UserSetting::class => \Katu\Models\Presets\UserSetting::class,
				\Katu\Storage\Entity::class => \Katu\Storage\Entity::class,
				\Katu\Tools\Calendar\Day::class => \Katu\Tools\Calendar\Day::class,
				\Katu\Tools\Calendar\DayCollection::class => \Katu\Tools\Calendar\DayCollection::class,
				\Katu\Tools\Calendar\Interval::class => \Katu\Tools\Calendar\Interval::class,
				\Katu\Tools\Calendar\IntervalCollection::class => \Katu\Tools\Calendar\IntervalCollection::class,
				\Katu\Tools\Calendar\Month::class => \Katu\Tools\Calendar\Month::class,
				\Katu\Tools\Calendar\MonthCollection::class => \Katu\Tools\Calendar\MonthCollection::class,
				\Katu\Tools\Calendar\Seconds::class => \Katu\Tools\Calendar\Seconds::class,
				\Katu\Tools\Calendar\Time::class => \Katu\Tools\Calendar\Time::class,
				\Katu\Tools\Calendar\TimeCollection::class => \Katu\Tools\Calendar\TimeCollection::class,
				\Katu\Tools\Calendar\Timeout::class => \Katu\Tools\Calendar\Timeout::class,
				\Katu\Tools\Calendar\Week::class => \Katu\Tools\Calendar\Week::class,
				\Katu\Tools\Calendar\WeekCollection::class => \Katu\Tools\Calendar\WeekCollection::class,
				\Katu\Tools\Calendar\Year::class => \Katu\Tools\Calendar\Year::class,
			], static::getDIDefinitions()));

			// Create the app.
			static::$instance = \DI\Bridge\Slim\Bridge::create($builder->build());

			// Load .env
			try {
				$dotenv = \Dotenv\Dotenv::createImmutable(array_map(function (File $file) {
					return (string)$file->getDir();
				}, static::getEnvFiles()->getArrayCopy()));
				$dotenv->load();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			// Setup timezone.
			try {
				date_default_timezone_set(\Katu\Config\Config::get("app", "timezone"));
			} catch (\Throwable $e) {
				// Just use default timezone.
			}

			// Setup autoload.
			foreach (static::getAutoloadRegisterFunctions() as $registerFunction) {
				spl_autoload_register($registerFunction);
			}

			// Setup session.
			Session::setCookieParams();

			// Add body parsing middleware.
			static::$instance->addBodyParsingMiddleware();

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

				$slimRoute = static::$instance->map($route->getMethods(), $pattern, $callback);
				if (is_string($name) && trim($name)) {
					$slimRoute->setName($name);
				} elseif ($route->getName()) {
					$slimRoute->setName($route->getName());
				}
			}

			// Setup Error Middleware.
			try {
				$displayErrorDetails = \Katu\Config\Config::get("app", "slim", "settings", "displayErrorDetails");
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				$displayErrorDetails = false;
			}

			$errorMiddleware = static::$instance->addErrorMiddleware((bool)$displayErrorDetails, true, true);
			$errorMiddleware->setDefaultErrorHandler(static::getErrorHandler());
		}

		return static::$instance;
	}

	public static function getContainer(): ContainerInterface
	{
		return static::get()->getContainer();
	}
}
