<?php

namespace Katu\Utils\Payments;

class PayU {

	public $posId;
	public $posAuthKey;
	public $keyOne;
	public $keyTwo;

	const BASE_URL = 'https://secure.payu.com/paygw';

	public function __construct($posId, $posAuthKey, $keyOne, $keyTwo) {
		$this->posId      = $posId;
		$this->posAuthKey = $posAuthKey;
		$this->keyOne     = $keyOne;
		$this->keyTwo     = $keyTwo;
	}

	static function createWithDefaultConfig() {
		return new static(
			\Katu\Config::get('payu', 'posId'),
			\Katu\Config::get('payu', 'posAuthKey'),
			\Katu\Config::get('payu', 'keyOne'),
			\Katu\Config::get('payu', 'keyTwo')
		);
	}

	public function getEndpoinTURL($endpoint, $encoding = 'UTF') {
		return static::BASE_URL . '/' . $encoding . '/' . $endpoint;
	}

	public function getNewPaymenTURL($params) {
		$url = $this->getEndpoinTURL('NewPayment');

		$params = array(
			'pos_id'       => $this->posId,
			'pay_type'     => isset($params['pay_type'])   ? $params['pay_type']   : null,
			'session_id'   => isset($params['session_id']) ? $params['session_id'] : null,
			'pos_auth_key' => $this->posAuthKey,
			'amount'       => isset($params['amount'])     ? $params['amount']     : null,
			'desc'         => isset($params['desc'])       ? $params['desc']       : null,
			'desc2'        => isset($params['desc2'])      ? $params['desc2']      : null,
			'order_id'     => isset($params['order_id'])   ? $params['order_id']   : null,
			'first_name'   => isset($params['first_name']) ? $params['first_name'] : null,
			'last_name'    => isset($params['last_name'])  ? $params['last_name']  : null,
			'street'       => isset($params['street'])     ? $params['street']     : null,
			'street_hn'    => isset($params['street_hn'])  ? $params['street_hn']  : null,
			'street_an'    => isset($params['street_an'])  ? $params['street_an']  : null,
			'city'         => isset($params['city'])       ? $params['city']       : null,
			'post_code'    => isset($params['post_code'])  ? $params['post_code']  : null,
			'country'      => isset($params['country'])    ? $params['country']    : null,
			'email'        => isset($params['email'])      ? $params['email']      : null,
			'phone'        => isset($params['phone'])      ? $params['phone']      : null,
			'language'     => isset($params['language'])   ? $params['language']   : null,
			'client_ip'    => isset($params['client_ip'])  ? $params['client_ip']  : null,
			'ts'           => time(),
			'key1'         => $this->keyOne,
		);

		$params['sig'] = md5(implode(array_values($params)));

		return \Katu\Types\TURL::make($url, $params);
	}

	public function getPaymentStatus($sessionId, $encoding = 'UTF') {
		$url = static::BASE_URL . '/' . $encoding . '/Payment/get/xml';
		$params = array(
			'pos_id'     => $this->posId,
			'session_id' => $sessionId,
			'ts'         => time(),
			'key1'       => $this->keyOne,
		);

		$params['sig'] = md5(implode(array_values($params)));

		$curl = new \Curl\Curl();
		$response = $curl->post($url, $params);

		if ($curl->error) {
			throw new \Exception();
		}

		if (!isset($response->status)) {
			throw new \Exception();
		}

		if ((string) $response->status == 'ERROR') {
			throw new \Exception(null, (int) $response->error->nr);
		}

		if ((string) $response->status != 'OK') {
			throw new \Exception();
		}

		return \Katu\Utils\JSON::decodeAsObjects(\Katu\Utils\JSON::encode($response))->trans;
	}

}
