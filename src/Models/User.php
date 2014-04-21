<?php

namespace Katu\Models;

use \Katu\Exception;

class User extends \Katu\Model {

	const TABLE = 'users';

	static function create() {
		return self::insert();
	}

	static function getLoggedIn() {
		return self::getByPK(\Katu\Session::get('katu.user.id'));
	}

	public function addUserService($service_name, $service_user_id) {
		return \App\Models\UserService::create($this, $service_name, $service_user_id);
	}

	public function login() {
		return \Katu\Session::set('katu.user.id', (int) $this->id);
	}

	public function hasAC($ac) {
		return (bool) \App\Models\UserAC::getByProperties(array(
			'user_id' => (int)    ($this->id),
			'ac'      => (string) (trim($ac)),
		))->getOne();
	}

	public function addAC($ac) {
		return \App\Models\UserAC::make($this, $ac);
	}

}
