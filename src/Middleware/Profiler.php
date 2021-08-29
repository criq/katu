<?php

namespace Katu\Middleware;

class Profiler extends \Slim\Middleware {

	public function call() {
		\Katu\Utils\Profiler::init('global');

		$this->next->call();

		\Katu\Utils\Profiler::dump('global');
	}

}
