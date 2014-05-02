<?php

namespace Katu\Models;

use \Katu\Exception;

class User extends \Katu\Model {

	const TABLE = 'users';

	static function create() {
		return self::insert(array(
			'timeCreated' => (string) (\Katu\Utils\DateTime::get()->getDBDatetimeFormat()),
		));
	}

	static function getLoggedIn() {
		return self::get(\Katu\Session::get('katu.user.id'));
	}

	public function addUserService($serviceName, $serviceUserId) {
		return \App\Models\UserService::create($this, $serviceName, $serviceUserId);
	}

	public function login() {
		return \Katu\Session::set('katu.user.id', (int) $this->id);
	}

	public function hasAC($ac) {
		return (bool) \App\Models\UserAC::getByProperties(array(
			'userId' => (int)    ($this->id),
			'ac'     => (string) (trim($ac)),
		))->getOne();
	}

	public function addAC($ac) {
		return \App\Models\UserAC::make($this, $ac);
	}

}
