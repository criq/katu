<?php

namespace Katu\Middleware;

class Profiler extends \Slim\Middleware {

	public function call() {
		$app = $this->app;

		$this->next->call();

		\Katu\Utils\Profiler::dump();
	}

}
