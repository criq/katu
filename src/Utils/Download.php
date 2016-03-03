<?php

namespace Katu\Utils;

class Download {

	private $content;
	private $contentTypeMime;
	private $contentTypeCharset;
	private $contentDisposition;

	public function __construct($content) {
		$this->content = $content;
	}

	public function setMime($mime) {
		$this->contentTypeMime = $mime;

		return $this;
	}

	public function setCharset($charset) {
		$this->contentTypeCharset = $charset;

		return $this;
	}

	public function respond($fileName = null, $disposition = 'attachment') {
		$app = \Katu\App::get();

		/**********************************************************************
		 * Content-Type.
		 */

		$mime = null;
		$charset = null;

		if ($this->contentTypeMime) {
			$mime = $this->contentTypeMime;
		} elseif ($this->content instanceof File) {
			$mime = $this->content->getMime();
		} else {
			$mime = 'text/plain';
		}

		if ($this->contentTypeCharset) {
			$charset = $this->contentTypeCharset;
		} elseif ($this->content instanceof File) {
			$charset = mb_detect_encoding($this->content->get());
		} else {
			$charset = mb_detect_encoding($this->content);
		}

		$app->response->headers->set('Content-Type', $mime . '; charset=' . $charset);

		/**********************************************************************
		 * Content-Disposition.
		 */

		if (is_null($this->contentDisposition) && is_null($fileName) && $this->content instanceof File) {
			$fileName = basename($this->content);
		}
		$app->response->headers->set('Content-Disposition', $disposition . '; filename=' . $fileName);

		/**********************************************************************
		 * Content-Length.
		 */

		if ($this->content instanceof File) {
			$length = $this->content->getSize();
		} else {
			$length = strlen($this->content);
		}
		$app->response->headers->set('Content-Length', $length);

		/**********************************************************************
		 * Content-Description.
		 */

		$app->response->headers->set('Content-Description', 'File Transfer');

		/**********************************************************************
		 * Content-Transfer-Encoding.
		 */

		$app->response->headers->set('Content-Transfer-Encoding', 'Binary');

		/**********************************************************************
		 * Cache.
		 */

		$app->response->headers->set('Expires', '0');
		$app->response->headers->set('Cache-Control', 'must-revalidate');
		$app->response->headers->set('Pragma', 'public');

		if ($this->content instanceof File) {
			$body = $this->content->get();
		} else {
			$body = $this->content;
		}
		$app->response->setBody($body);

		return true;
	}

}
