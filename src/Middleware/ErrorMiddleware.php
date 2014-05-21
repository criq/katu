<?php

namespace Katu\Middleware;

class ErrorMiddleware extends \Slim\Middleware {

	public function call() {
		try {

			$this->next->call();

		} catch (\Katu\Exceptions\UnauthorizedException $e) {

			throw $e;

			return \Katu\View::renderUnauthorized($e);

		} catch (\Katu\Exceptions\NotFoundException $e) {

			throw $e;

			return \Katu\View::renderNotFound($e);

		} catch (\Exception $e) {

			throw $e;

			\Katu\ErrorHandler::log($e->getMessage());

			return \Katu\View::renderError($e);

		}
	}

}
