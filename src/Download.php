<?php

namespace Jabli\Aids;

class Download {

	static function set($filename) {
		header('Content-Length: ' . filesize($filename));
		header('Content-Transfer-Encoding: Binary');
		header('Content-Disposition: attachment; filename=' . basename($filename));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		@ob_clean();
		@flush();

		return readfile($filename);
	}

}
