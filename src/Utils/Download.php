<?php

namespace Katu\Utils;

class Download {

	static function dump($filename, $save_as = NULL, $disposition = 'attachment') {
		if (!$save_as) {
			$save_as = $filename;
		}

		header('Content-Length: ' . filesize($filename));
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: ' . $disposition . '; filename=' . basename($save_as));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		@ob_clean();
		@flush();

		return readfile($filename);
	}

}
