<?php

namespace Katu\Middleware;

class ProfilerMiddleware extends \Slim\Middleware {

	public function call() {
		$app = $this->app;

		$this->next->call();

		\Katu\Utils\Profiler::dump();
	}

}
