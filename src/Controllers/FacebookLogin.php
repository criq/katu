<?php

namespace Jabli\Controllers;

class FacebookLogin extends \Jabli\Controller {

	static function index() {
		$app = \Jabli\FW::getApp();
		$facebook = new \Jabli\Utils\Facebook();

		$access_token = $facebook->getAccessToken();

		// There's no access token, login the user.
		if (!$access_token) {

			// We have some errors.
			if ($app->request->params('error')) {

				return self::redirect(\Jabli\Utils\URL::getSite($app->urlFor('login')));

			// We have callback params.
			} elseif ($app->request->params('state') && $app->request->params('code')) {

				// Exchange callback params for an access token.
				$access_token = $facebook->getToken($app->request->params('code'));

				// No token received, an error occured, start over.
				if (!$access_token) {

					$facebook->resetAccessToken();
					return self::redirect(\Jabli\Utils\URL::getSite($app->urlFor('login')));
				}

				// Save the access token.
				$facebook->setAccessToken($access_token);

				return self::redirect(\Jabli\Utils\URL::getSite($app->urlFor('login')));

			// No callback params - this is the initial call.
			} else {

				// Get the login session ID.
				$state = \Jabli\Utils\Random::getString();

				// Save the login session ID into a cookie.
				\Jabli\Cookie::set($facebook->getVariableName('state'), $state);

				return self::redirect($facebook->getLoginURL());

			}

		} else {

			// Set user ID to session.
			$api = $facebook->facebook->api('me');
			$facebook->setUser($api['id']);

			// Login the user.
			$user_service = \App\Models\UserService::getByServiceAndID('facebook', $api['id'])->getOne();
			if (!$user_service) {

				// Create new user.
				$user = \App\Models\User::create();
				$user_service = $user->addUserService('facebook', $api['id']);

			}

			$user = $user_service->getUser();
			$user->setName($api['name']);
			$user->save();

			$user->login();

			return self::redirect($user->hasAC('bo') ? \Jabli\Utils\URL::getSite('bo') : \Jabli\Utils\URL::getBase());

		}
	}

}
