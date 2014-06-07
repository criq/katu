<?php

namespace Katu\Utils;

class Download {

	static function respond($filename, $saveAs = NULL, $disposition = 'attachment') {
		$app = \Katu\App::get();

		if (!$saveAs) {
			$saveAs = basename($filename);
		}

		$app->response->headers->set('Content-Length', FS::getSize($filename));
		$app->response->headers->set('Content-Transfer-Encoding', 'Binary');
		$app->response->headers->set('Content-Disposition', $disposition . '; filename=' . basename($saveAs));
		$app->response->headers->set('Expires', '0');
		$app->response->headers->set('Cache-Control', 'must-revalidate');
		$app->response->headers->set('Pragma', 'public');

		$app->response->setBody(readfile($filename));

		return TRUE;
	}

}
