<?php

namespace Katu\Middleware;

class Profiler extends \Slim\Middleware {

	public function call() {
		\Katu\Utils\Profiler::initGlobal();

		$this->next->call();

		\Katu\Utils\Profiler::dumpGlobal();
	}

}
