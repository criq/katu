<?php

namespace Katu\Middleware;

class ErrorMiddleware extends \Slim\Middleware {

	public function call() {
		$app = $this->app;
		$app->error(function (\Exception $e) {
			throw $e;
		});

		try {
			$this->next->call();
		} catch (\Exception $e) {
			\Katu\ErrorHandler::handle($e);
		}
	}

}
