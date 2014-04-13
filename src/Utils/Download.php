<?php

namespace Katu\Utils;

class Download {

	static function respond($filename, $save_as = NULL, $disposition = 'attachment') {
		$app = \Katu\App::get();

		if (!$save_as) {
			$save_as = $filename;
		}

		$app->response->headers->set('Content-Length', filesize($filename));
		$app->response->headers->set('Content-Transfer-Encoding', 'Binary');
		$app->response->headers->set('Content-Disposition', $disposition . '; filename=' . basename($save_as));
		$app->response->headers->set('Expires', '0');
		$app->response->headers->set('Cache-Control', 'must-revalidate');
		$app->response->headers->set('Pragma', 'public');

		$app->response->setBody(readfile($filename));

		return TRUE;
	}

}
