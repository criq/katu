<?php

namespace Katu\Utils;

class Download {

	static function respond($path, $saveAs = null, $disposition = 'attachment') {
		$app = \Katu\App::get();

		if (!$saveAs) {
			$saveAs = basename($path);
		}

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Type: ' . FileSystem::getMime($path));
		header('Content-Disposition: ' . $disposition . '; filename=' . basename($saveAs));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . FileSystem::getSize($path));
		readfile($path);

		die;
	}

}
