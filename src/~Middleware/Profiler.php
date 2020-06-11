<?php

namespace Katu\Middleware;

class Profiler extends \Slim\Middleware
{
	public function call() {
		\Katu\Tools\Profiler\Profiler::init('global');
		$this->next->call();
		\Katu\Tools\Profiler\Profiler::dump('global');
	}
}
