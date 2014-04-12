<?php

namespace Katu\Models;

use \Katu\Exception;

class UserService extends \Katu\Model {

	const TABLE = 'user_services';

	static function create($user, $service_name, $service_user_id) {
		return self::insert(array(
			'user_id'         => (int)    ($user->id),
			'service_name'    => (string) ($service_name),
			'service_user_id' => (string) ($service_user_id),
		));
	}

	static function getByServiceAndID($service_name, $service_user_id) {
		return self::getByProperties(array(
			'service_name'    => (string) ($service_name),
			'service_user_id' => (string) ($service_user_id),
		));
	}

}
