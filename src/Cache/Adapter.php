<?php

namespace Katu\Cache;

use Katu\Tools\DateTime\Timeout;
use Katu\Types\TIdentifier;

interface Adapter
{
	public function delete(TIdentifier $identifier): bool;
	public function exists(TIdentifier $identifier, Timeout $timeout): bool;
	public function flush(): bool;
	public function get(TIdentifier $identifier, Timeout $timeout);
	public function set(TIdentifier $identifier, Timeout $timeout, $value);
	public static function isMemory(): bool;
	public static function isSupported(): bool;
}
