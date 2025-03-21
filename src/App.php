<?php

namespace Katu;

use Katu\Files\File;
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

	public static function getPublicDir(): File
	{
		return new File(static::getBaseDir(), "public");
	}

	public static function getPublicTemporaryDir(): File
	{
		return new File(static::getPublicDir(), "tmp");
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
	 * Run.
	 */
	public static function getInstance(): \Slim\App
	{
		if (!static::$instance) {
			// Create the dependency injection container.
			$builder = new \DI\ContainerBuilder;

			if (class_exists("\App\Config\AppConfig")) {
				$appDefinitions = (new \App\Config\AppConfig)->getDIDefinitions();
			} else {
				$appDefinitions = [];
			}

			$builder->addDefinitions(array_merge([
				\Psr\Log\LoggerInterface::class => \DI\factory(function (TIdentifier $identifier) {
					return static::getLogger($identifier);
				}),

				\Katu\Config\AppConfig::class => \Katu\Config\AppConfig::class,
				\Katu\Config\CookieConfig::class => \Katu\Config\CookieConfig::class,
				\Katu\Config\EncryptionConfig::class => \Katu\Config\EncryptionConfig::class,
				\Katu\Config\EnvConfig::class => \Katu\Config\EnvConfig::class,
				\Katu\Config\RedisConfig::class => \Katu\Config\RedisConfig::class,
				\Katu\Config\ThirdParty\Google\SecretManagerConfig::class => \Katu\Config\ThirdParty\Google\SecretManagerConfig::class,
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
			], $appDefinitions));

			// Create the app.
			static::$instance = \DI\Bridge\Slim\Bridge::create($builder->build());

			// Setup timezone.
			try {
				$class = static::getContainer()->get(\Katu\Tools\Calendar\Time::class);
				date_default_timezone_set((new $class)->getTimezone()->getName());
			} catch (\Throwable $e) {
				// Just use default timezone.
			}

			// Setup autoload.
			foreach (static::getAutoloadRegisterFunctions() as $registerFunction) {
				spl_autoload_register($registerFunction);
			}

			// Add body parsing middleware.
			static::$instance->addBodyParsingMiddleware();

			// Set up routes.
			foreach ((new \App\Config\RouterConfig)->getRoutes() as $name => $route) {
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

			$errorMiddleware = static::$instance->addErrorMiddleware((bool)false, true, true);
			$errorMiddleware->setDefaultErrorHandler(static::getErrorHandler());
		}

		return static::$instance;
	}

	public static function getContainer(): ContainerInterface
	{
		return static::getInstance()->getContainer();
	}

	public static function getAppConfig(): \Katu\Config\AppConfig
	{
		$class = static::getContainer()->get(\Katu\Config\AppConfig::class);

		return new $class;
	}

	public static function getEnvConfig(): \Katu\Config\EnvConfig
	{
		$class = static::getContainer()->get(\Katu\Config\EnvConfig::class);

		return new $class;
	}

	public static function getTimeConfig(): \Katu\Config\TimeConfig
	{
		$class = static::getContainer()->get(\Katu\Config\TimeConfig::class);

		return new $class;
	}

	public static function getCookieConfig(): \Katu\Config\CookieConfig
	{
		$class = static::getContainer()->get(\Katu\Config\CookieConfig::class);

		return new $class;
	}

	public static function getEncryptionConfig(): \Katu\Config\EncryptionConfig
	{
		$class = static::getContainer()->get(\Katu\Config\EncryptionConfig::class);

		return new $class;
	}

	public static function getSecretManagerConfig(): \Katu\Config\ThirdParty\Google\SecretManagerConfig
	{
		$class = static::getContainer()->get(\Katu\Config\ThirdParty\Google\SecretManagerConfig::class);

		return new $class;
	}

	public static function getRedisConfig(): \Katu\Config\RedisConfig
	{
		$class = static::getContainer()->get(\Katu\Config\RedisConfig::class);

		return new $class;
	}
}
