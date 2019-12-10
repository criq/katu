<?php

namespace Katu;

class App {

	static $app = null;

	static function getExtendedClass($appClassName, $fallbackName) {
		return class_exists($appClassName) ? $appClassName : $fallbackName;
	}

	static function getControllerClass() {
		return static::getExtendedClass('\\App\\Extensions\\Controllers\\Controller', '\\Katu\\Controllers\\Controller');
	}

	static function getViewClass() {
		return static::getExtendedClass('\\App\\Extensions\\Views\\View', '\\Katu\\Views\\View');
	}

	static function getErrorHandlerClass() {
		return static::getExtendedClass('\\App\\Extensions\\Errors\\Handler', '\\Katu\\Errors\\Handler');
	}

	static function getBaseDir() {
		return new \Katu\Files\File(realpath(__DIR__ . '/../../../../'));
	}

	static function getFileDir() {
		return new \Katu\Files\File(static::getBaseDir(), 'files');
	}

	static function getTmpDir() {
		return new \Katu\Files\File(static::getBaseDir(), 'tmp');
	}

	static function init() {
		if (!defined('LOG_PATH')) {
			define('LOG_PATH', \Katu\Files\File::joinPaths(static::getBaseDir(), Errors\Handler::LOG_DIR));
		}
		if (!defined('ERROR_LOG')) {
			define('ERROR_LOG', \Katu\Files\File::joinPaths(LOG_PATH, Errors\Handler::ERROR_LOG));
		}

		// Timezone.
		try {
			date_default_timezone_set(\Katu\Config\Config::get('app', 'timezone'));
		} catch (\Exception $e) {
			// Just use default timezone.
		}

		// Session.
		\Katu\Tools\Session\Session::setCookieParams();

		return true;
	}

	static function get() {
		if (!static::$app) {

			static::init();

			try {
				$config = \Katu\Config\Config::get('app', 'slim');
			} catch (\Exception $e) {
				$config = [];
			}

			$config['errorHandler'] = function($c) {
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

	static function run() {
		$app = static::get();

		// TODO - obnovit
		/*
		// Redirect to canonical host.
		try {
			if ($app->request->getMethod() == 'GET' && \Katu\Config\Config::get('app', 'redirectToCanonicalHost')) {
				$currenTURL = \Katu\Tools\Routing\URL::getCurrent();
				$currentHost = $currenTURL->getHost();
				$canonicalUrl = new Types\TURL(\Katu\Config\Config::get('app', 'baseUrl'));
				$canonicalHost = $canonicalUrl->getHost();

				if ($currentHost != $canonicalHost) {
					$canonicalParts = $currenTURL->getParts();
					$canonicalParts['host'] = $canonicalHost;

					$redirecTURL = Types\TURL::build($canonicalParts);

					return header('Location: ' . (string) $redirecTURL, 301); die;
				}
			}
		} catch (\Katu\Exceptions\MissingConfigException $e) {
			// Nothing to do.
		}

		// Catch all.
		$catchAll = function() {

			$app = static::get();

			// Map URL to controller method.
			$parts = array_filter(explode('/', $app->request->getResourceUri()));
			if ($parts) {
				$controller = '\App\Controllers\\' . implode('\\', array_map('ucfirst', count($parts) > 1 ? array_slice($parts, 0, -1) : $parts));
				$method     = count($parts) > 1 ? array_slice($parts, -1) : 'index';
				$callable   = $controller . '::' . (is_array($method) ? $method[0] : $method);

				if (is_callable($callable)) {
					return call_user_func_array($callable, array());
				} else {
					throw new Exceptions\ControllerMethodNotFoundException("Invalid controller method.");
				}
			}

		};
		*/

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
			} catch (\Exception $e) {
				// Nothing to do, no custom routes defined.
			}

			// Catch-all.
			// TODO - obnovit
			/*
			$app->map('.+', $catchAll)->via('GET', 'POST');
			*/

			// Autoload.
			if (class_exists('\\App\\Extensions\\Autoload')) {
				foreach ((array)\App\Extensions\Autoload::getRegisterFunctions() as $registerFunction) {
					spl_autoload_register($registerFunction);
				}
			}

			// Run the app.
			$app->run();

		} catch (\Exception $e) { throw $e; }

	}

	static function isProfilerOn() {
		return \Katu\Cache\Runtime::get('profiler.on', function() {
			try {
				return \Katu\Config\Config::get('app', 'profiler');
			} catch (\Katu\Exceptions\MissingConfigException $e) {
				return false;
			}
		});
	}

	static function getConnection($name = null) {
		$names = array_keys(Config::getDb());

		if ($name) {
			if (!in_array($name, $names)) {
				throw new Exceptions\DatabaseConnectionException("Invalid database connection name.");
			}

			return PDO\Connection::getInstance($name);
		} else {
			if (count($names) > 1) {
				throw new Exceptions\DatabaseConnectionException("Ambiguous database connection name.");
			}
		}

		return PDO\Connection::getInstance($names[0]);
	}

}
