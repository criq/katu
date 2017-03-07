<?php

namespace Katu\Exceptions\Facebook;

class MissingScopeException extends \Katu\Exceptions\ErrorException {

	public function setScope($scope) {
		$this->scope = $scope;

		return $this;
	}

}
