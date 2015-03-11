<?php

namespace Katu;

class App {

	static function init() {
		// Constants.
		if (!defined('BASE_DIR')) {
			define('BASE_DIR', realpath(__DIR__ . '/../../../../'));
		}
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', Utils\FileSystem::joinPaths(BASE_DIR, ErrorHandler::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', Utils\FileSystem::joinPaths(LOG_PATH, ErrorHandler::ERROR_LOG));
		}
		if (!defined('FILE_DIR')) {
			define('FILE_DIR', 'files');
		}
		if (!defined('FILE_PATH')) {
			define('FILE_PATH', rtrim(BASE_DIR) . '/' . FILE_DIR . '/');
		}
		if (!defined('TMP_DIR')) {
			define('TMP_DIR', 'tmp');
		}
		if (!defined('TMP_PATH')) {
			define('TMP_PATH', rtrim(BASE_DIR) . '/' . TMP_DIR . '/');
		}

		// Timezone.
		try {
			date_default_timezone_set(Config::getApp('timezone'));
		} catch (\Exception $e) {
			// Just use default timezone.
		}

		// Session.
		\Katu\Session::setCookieParams();

		return true;
	}

	static function isDev() {
		return Config::get('app', 'slim', 'mode') == 'development';
	}

	static function isTest() {
		return Config::get('app', 'slim', 'mode') == 'testing';
	}

	static function isProd() {
		return Config::get('app', 'slim', 'mode') == 'production';
	}

	static function isProfilerOn() {
		return \Katu\Utils\Cache::getRuntime('profiler.on', function() {
			try {
				var_dump("A");
				return \Katu\Config::get('app', 'profiler');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				return false;
			}
		});
	}

	static function get() {
		$app = \Slim\Slim::getInstance();
		if (!$app) {

			self::init();

			try {
				$config = Config::getApp('slim');
			} catch (\Exception $e) {
				$config = array();
			}

			// Logger.
			$config['log.writer'] = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
				'handlers' => array(
					new \Monolog\Handler\StreamHandler(ERROR_LOG),
				),
			));

			// Create app.
			$app = new \Slim\Slim($config);

			// Add error middleware.
			$app->add(new Middleware\ErrorMiddleware());

			// Add profiler middleware.
			$app->add(new Middleware\ProfilerMiddleware());

			// Default content-type header for debugging, will be probably overwritten by app.
			header('Content-Type: text/html; charset=UTF-8');
			$app->response->headers->set('Content-Type', 'text/html; charset=UTF-8');

		}

		return $app;
	}

	static function getPdo($name = null) {
		$names = array_keys(Config::getDb());

		if ($name) {
			if (!in_array($name, $names)) {
				throw new Exceptions\DatabaseConnectionException("Invalid database connection name.");
			}

			return Pdo\Connection::getInstance($name);
		} else {
			if (count($names) > 1) {
				throw new Exceptions\DatabaseConnectionException("Ambiguous database connection name.");
			}
		}

		return Pdo\Connection::getInstance($names[0]);
	}

	static function run() {
		self::init();

		$app = self::get();

		// Redirect to canonical host.
		try {
			if ($app->request->getMethod() == 'GET' && Config::get('app', 'redirectToCanonicalHost')) {
				$currentUrl    = Utils\Url::getCurrent();
				$currentHost   = $currentUrl->getHost();
				$canonicalUrl  = new Types\TUrl(Config::get('app', 'baseUrl'));
				$canonicalHost = $canonicalUrl->getHost();

				if ($currentHost != $canonicalHost) {
					$canonicalParts = $currentUrl->getParts();
					$canonicalParts['host'] = $canonicalHost;

					$redirectUrl = Types\TUrl::build($canonicalParts);

					return header('Location: ' . (string) $redirectUrl, 301); die;
				}
			}
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nothing to do.
		}

		$catchAll = function() {
			$app = self::get();

			// Map URL to controller method.
			$parts = array_filter(explode('/', $app->request->getResourceUri()));
			if ($parts) {
				$ns       = '\App\Controllers\\' . implode('\\', array_map('ucfirst', count($parts) > 1 ? array_slice($parts, 0, -1) : $parts));
				$method   = count($parts) > 1 ? array_slice($parts, -1) : 'index';
				$callable = $ns . '::' . (is_array($method) ? $method[0] : $method);

				if (is_callable($callable)) {
					return call_user_func_array($callable, array());
				} else {
					throw new Exceptions\ControllerMethodNotFoundException("Invalid controller method.");
				}
			}
		};

		try {

			$app = self::get();

			try {

				// Set up routes.
				foreach ((array) Config::get('routes') as $name => $route) {
					try {

						$_route = $app->map($route->getPattern(), $route->getCallable())->via('GET', 'POST');
						if (is_string($name) && trim($name)) {
							$_route->name($name);
						} elseif ($route->name) {
							$_route->name($route->name);
						}

					} catch (\Exception $e) { throw new Exceptions\ErrorException("A route error occured."); }
				}

			} catch (\Exception $e) { /* Nothing to do, no custom routes defined. */ }

			// Catch-all.
			$app->map('.+', $catchAll)->via('GET', 'POST');

			// Run the app.
			$app->run();

		} catch (\Exception $e) { throw $e; }

	}

	static function getExtendedClass($appClassName, $fallbackName) {
		if (class_exists($appClassName)) {
			return $appClassName;
		} else {
			return $fallbackName;
		}
	}

	static function getControllerClass() {
		return static::getExtendedClass('\App\Extensions\Controller', '\Katu\Controller');
	}

	static function getViewClass() {
		return static::getExtendedClass('\App\Extensions\View', '\Katu\View');
	}

	static function getErrorHandlerClass() {
		return static::getExtendedClass('\App\Extensions\ErrorHandler', '\Katu\ErrorHandler');
	}

}
