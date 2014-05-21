<?php

namespace Katu\Middleware;

class ErrorMiddleware extends \Slim\Middleware {

	public function call() {
		try {

			$this->next->call();

		} catch (\Katu\Exceptions\UnauthorizedException $e) {

			return \Katu\View::renderUnauthorized($e);

		} catch (\Katu\Exceptions\NotFoundException $e) {

			return \Katu\View::renderNotFound($e);

		} catch (\Exception $e) {

			\Katu\ErrorHandler::errorLog($e->getMessage());

			return \Katu\View::renderError($e);

		}
	}

}
