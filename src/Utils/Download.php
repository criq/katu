<?php

namespace Katu\Utils;

class Download {

	static function respond($filename, $saveAs = null, $disposition = 'attachment') {
		$app = \Katu\App::get();

		if (!$saveAs) {
			$saveAs = basename($filename);
		}

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: ' . $disposition . '; filename=' . basename($saveAs));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . FS::getSize($filename));
		readfile($filename);

		die;
	}

}
