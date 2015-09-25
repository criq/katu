<?php

namespace Katu\Utils;

class Download {

	static function respond($file, $saveAs = null, $disposition = 'attachment') {
		$app = \Katu\App::get();

		if (!$saveAs) {
			$saveAs = basename($file);
		}

		if (is_string($file)) {
			$file = new File($file);
		}

		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: Binary');
		header('Content-Type: ' . $file->getMime());
		header('Content-Disposition: ' . $disposition . '; filename=' . basename($saveAs));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . $file->getSize());
		readfile((string) $file);

		die;
	}

}
