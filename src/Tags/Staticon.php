<?php

namespace pinpie\pinpie\Tags;

use \pinpie\pinpie\PP as PP;


class Staticon extends Tag {
	public
		$dimensions = [],
		$gzip = false,
		$gzipLevel = 1,
		$minifie = false,
		$minifiedPath = false,
		$minifiedURL = false,
		$staticHash = false,
		$staticPath = false,
		$staticType = false,
		$url = false;

	/**
	 * Internal cache for any computed values like url
	 * @var array
	 */
	private $c = [];

	public function __construct(PP $pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag = null, $priority = 10000, $depth = 0) {
				var_dump('__construct 1 $this->c[getStaticPath]', $this->c['getStaticPath']);

		parent::__construct($pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth);
		$this->pinpie->times[] = [microtime(true), 'static ' . $fulltag . ' construct started'];
		if (!isset($this->pinpie->inCa['static'])) {
			$this->pinpie->inCa['static'] = [];
		}
		$this->c = &$this->pinpie->inCa['static'];

				var_dump('__construct 2 $this->c[getStaticPath]', $this->c['getStaticPath']);

		$this->staticType = $this->name;
		$this->staticPath = $this->value . (!empty($this->params) ? '?' . implode('&', $this->params) : '');

		if (empty($this->staticPath)) {
			$this->error($fulltag . ' static file path is empty');
		} else {
			if ($this->staticPath{0} !== '/') {
				$this->staticPath = rtrim($this->pinpie->url->path, '/') . '/' . $this->staticPath;
			}
		}
				var_dump('__construct 3 $this->c[getStaticPath]', $this->c['getStaticPath']);

		if (empty($this->settings['minify types'])) {
			$this->settings['minify types'] = [];
		}
		if (empty($this->settings['gzip types'])) {
			$this->settings['gzip types'] = [];
		}
		if (empty($this->settings['dimensions types'])) {
			$this->settings['dimensions types'] = [];
		}
		$this->minifie = in_array($this->staticType, $this->settings['minify types']);
		$this->gzip = in_array($this->staticType, $this->settings['gzip types']);
		$this->pinpie->times[] = [microtime(true), '$this->filename = $this->getStaticPath();'];
						var_dump('__construct 4 $this->c[getStaticPath]', $this->c['getStaticPath']);

		$this->filename = $this->getStaticPath();
						var_dump('__construct 5 $this->c[getStaticPath]', $this->c['getStaticPath']);

		$this->pinpie->times[] = [microtime(true), '$this->filename = $this->getStaticPath(); done, path is ' . $this->filename];

		if (empty($this->filename)) {
			$this->error('file not found');
			$this->filename = $this->value;
		} else {
			if ($this->minifie) {
				$this->pinpie->times[] = [microtime(true), 'minification'];
				$this->getMinified();
				$this->pinpie->times[] = [microtime(true), 'minification done'];
			}

			if ($this->gzip) {
				$this->pinpie->times[] = [microtime(true), 'gzip'];
				$this->checkAndRunGzip();
				$this->pinpie->times[] = [microtime(true), 'gzip done'];
			}

			if (in_array($this->staticType, $this->settings['dimensions types'])) {
				$this->pinpie->times[] = [microtime(true), 'getDimensions'];
				$this->dimensions = $this->getDimensions();
				$this->pinpie->times[] = [microtime(true), 'getDimensions done'];
			}

			$this->filetime = $this->pinpie->filemtime($this->filename);
			$this->staticHash = $this->getStaticHash();
		}
		$this->pinpie->times[] = [microtime(true), 'getStaticUrl'];
		$this->url = $this->getStaticUrl();
		$this->pinpie->times[] = [microtime(true), 'getStaticUrl done'];
	}

	public function getStaticHash() {
		return md5($this->filename . '*' . $this->filetime);
	}

	public function getStaticUrl() {
		var_dump('[FLOW] getStaticUrl() start $this->filename = ' . $this->filename);
		var_dump('$this->c[getStaticPath]', $this->c['getStaticPath']);
		if (!isset($this->c['getStaticPath'])) {
			$this->c['getStaticPath'] = [];
		}
		if (!isset($this->c['getStaticPath'][$this->filename])) {
			var_dump('[FLOW] getStaticUrl() no cached filename');
			if ($this->minifie AND $this->minifiedURL) {
				var_dump('[FLOW] getStaticUrl() minified');
				$file = $this->minifiedURL;
			} else {
				var_dump('[FLOW] getStaticUrl() not minified');

				$file = $this->staticPath;
			}
			$this->c['getStaticPath'][$this->filename] = $this->getServer() . ($file[0] == '/' ? '' : '/') . $file;
		}
		var_dump('[FLOW] getStaticUrl() end $this->filename = ' . $this->filename);
		return $this->c['getStaticPath'][$this->filename];
	}


	private function getStaticPath() {
		if (!isset($this->c['getStaticPath'])) {
			$this->c['getStaticPath'] = [];
		}
		if (isset($this->c['getStaticPath'][$this->staticPath])) {
			return $this->c['getStaticPath'][$this->staticPath];
		}
		$this->c['getStaticPath'][$this->staticPath] = $this->getStaticPathReal();
		return $this->c['getStaticPath'][$this->staticPath];
	}

	private function getStaticPathReal() {
		$path = rtrim($this->settings['folder'], '/\\') . DIRECTORY_SEPARATOR . ltrim($this->staticPath, '/\\');
		$this->pinpie->times[] = [microtime(true), 'processing static path ' . $path];
		if ($this->settings['realpath check']) {
			$this->pinpie->times[] = [microtime(true), 'realpath check required'];
			$path = $this->pinpie->checkPathIsInFolder($path, $this->settings['folder']);
			if ($path === false) {
				$this->pinpie->times[] = [microtime(true), 'realpath check failed'];
			} else {
				$this->pinpie->times[] = [microtime(true), 'realpath check successful, realpath is ' . $path];
			}
		} else {
			$this->pinpie->times[] = [microtime(true), 'realpath check skipped'];
		}
		if ($path === false OR $path === null OR $path === '') {
			$this->pinpie->times[] = [microtime(true), 'path is empty'];
			return false;
		}
		if (!file_exists($path)) {
			// no such file
			$this->pinpie->times[] = [microtime(true), 'file not found at path ' . $path];
			return false;
		}
		$this->pinpie->times[] = [microtime(true), 'getStaticPathReal done'];
		return $path;
	}

	public function getServer() {
		var_dump('[FLOW] getServer() started');

		if ($this->filename === false OR $this->filename === null OR $this->filename === '') {
			var_dump('[FLOW] getServer() empty filename');
			return false;
		}
		if (!isset($this->c['getServer'])) {
			$this->c['getServer'] = [];
		}
		if (isset($this->c['getServer'][$this->filename])) {
			return $this->c['getServer'][$this->filename];
		}
		if (empty($this->pinpie->conf->tags['%']['servers'])) {
			$this->url = '//' . $this->pinpie->conf->pinpie['site url'];
		} else {
			$a = abs(crc32($this->filename)) % count($this->pinpie->conf->tags['%']['servers']);
			$this->url = '//' . $this->pinpie->conf->tags['%']['servers'][$a];
		}
		$this->c['getServer'][$this->filename] = $this->url;
		var_dump('[FLOW] getServer() end $this->url = ' . $this->url);

		return $this->url;
	}

	private function checkAndRunGzip() {
		$r = false;
		if (!$this->checkMTime($this->filename, $this->filename . '.gz')) {
			$this->pinpie->times[] = [microtime(true), '#gzipping start ' . $this->filename];
			if (is_file($this->filename)) {
				$fp = fopen($this->filename, 'r');
				if ($fp !== false AND flock($fp, LOCK_EX | LOCK_NB)) {
					$gz = gzopen($this->filename . '.gz', 'w' . (int)$this->gzipLevel);
					if ($gz !== false) {
						$size = filesize($this->filename);
						if ($size) {
							/* prevents warning about zero size */
							$r = gzwrite($gz, fread($fp, $size));
						} else {
							/* dunno what to do: create empty .gz or do nothing? */
						}
					}
					flock($fp, LOCK_UN);
					fclose($fp);
				}
			}
			$this->pinpie->times[] = [microtime(true), '#gzipping done ' . $this->filename];
		}
		return $r;
	}

	private function checkAndRunMinifier() {
		if (!$this->minifie) {
			return false;
		}
		if (empty($this->settings['minify function'])) {
			return false;
		}
		$fp = fopen($this->filename, 'r');
		if (empty($fp)) {
			return false;
		}

		/* *
	   * We can't lock file for writing, external minifiers like Yahoo YUI Compressor
		 * or Google Closure Compiler will have no access to file in that case.
	   * Locking file for reading will prevent file from any modifications.
	   * We have only to attempt to lock it for writing, and switch back.
		 * If the file is already locked for reading - writing lock will fail.
		 * Success means the file is not locked for reading in another process.
		 * */
		if (
			flock($fp, LOCK_EX) === false
			// Switching back to reading lock to make file readable by any external processes
			OR flock($fp, LOCK_SH) === false
		) {
			return false;
		}
		// Calling user function, where minification is made
		$func = $this->settings['minify function'];
		$this->minifiedPath = $func($this);
		// Releasing lock
		flock($fp, LOCK_UN);
		fclose($fp);
		if (!$this->minifiedPath) {
			$this->pinpie->times[] = [microtime(true), '#minify func cancels use of min path by returning false ' . $this->filename];
			return false;
		}
		return $this->checkMTime($this->filename, $this->minifiedPath);
	}

	/** Return true if $older is older or equal than $newer.
	 * @param $older
	 * @param $newer
	 * @return bool
	 */
	private function checkMTime($older, $newer) {
		$molder = $this->pinpie->filemtime($older);
		$mnewer = $this->pinpie->filemtime($newer);
		if ($molder !== false AND $mnewer !== false AND $molder <= $mnewer) {
			return true;
		}
		return false;
	}


	/**
	 * Looks for minified version of the file in the static folder.
	 */
	private function getMinified() {
		$this->pinpie->times[] = [microtime(true), 'getMinified'];
		if (!isset($this->c['getMinified'])) {
			$this->c['getMinified'] = [];
		}
		if (isset($this->c['getMinified'][$this->staticPath])) {
			$this->minifiedURL = $this->c['getMinified'][$this->staticPath]['url'];
			$this->minifiedPath = $this->c['getMinified'][$this->staticPath]['path'];
		} else {
			$pi = pathinfo('/' . trim($this->staticPath, '/\\'));
			$this->minifiedURL = trim($pi['dirname'], '/\\') . '/min.' . $pi['basename'];
			$this->minifiedPath = $this->settings['folder'] . DIRECTORY_SEPARATOR . trim($this->minifiedURL, '/\\');
			if ($this->checkMTime($this->filename, $this->minifiedPath)) {
				$useminify = true;
			} else {
				$useminify = $this->checkAndRunMinifier();
				$this->pinpie->times[] = [microtime(true), 'checkAndRunMinifier done'];
			}
			if (!$useminify) {
				$this->minifiedURL = false;
			}
			$this->c['getMinified'][$this->staticPath]['url'] = $this->minifiedURL;
			$this->c['getMinified'][$this->staticPath]['path'] = $this->minifiedPath;
		}
		$this->pinpie->times[] = [microtime(true), 'getMinified done'];
	}


	public function getDimensions() {
		if (!isset($this->c['getDimensions'])) {
			$this->c['getDimensions'] = [];
		}
		if (!isset($this->c['getDimensions'][$this->filename])) {
			$this->c['getDimensions'][$this->filename] = $this->measureDimensions();
		}
		return $this->c['getDimensions'][$this->filename];
	}

	/**
	 * @param $path
	 * @return array|bool
	 */
	public function measureDimensions() {
		if (empty($this->filename)) return false;
		$imginfo = getimagesize($this->filename);
		$r = [];
		$r['type'] = $imginfo['mime'];
		$r['width'] = $imginfo[0];
		$r['height'] = $imginfo[1];
		return $r;
	}

	public function getOutput() {
		$this->content = $this->getContent();
		//Apply template to tag content
		if (!empty($this->template)) {
			$this->output = $this->applyTemplate();
		} else {
			$this->output = $this->content;
		}
		if ($this->placeholder) {
			$this->varPut();
		}
		return $this->output;
	}

	public function getContent() {
		$this->pinpie->times[] = [microtime(true), 'getContent'];
		if (!empty($this->settings['draw function'])) {
			$this->content = $this->settings['draw function']($this);
			$this->pinpie->times[] = [microtime(true), 'drawn by func'];
			return $this->content;
		}
		if ($this->cachetime) {
			/* exclamation mark AKA cachetime = return path only */
			$this->content = $this->url . '?time=' . $this->staticHash;
		} else {
			$this->content = $this->draw();
		}
		$this->pinpie->times[] = [microtime(true), 'drawn'];
		if (!empty($this->template)) {
			if (isset($this->dimensions['width'])) {
				$this->varsLocal['width'][0][] = $this->dimensions['width'];
			}
			if (isset($this->dimensions['height'])) {
				$this->varsLocal['height'][0][] = $this->dimensions['height'];
			}
			$this->varsLocal['file path'][0][] = $this->filename;
			$this->varsLocal['time'][0][] = $this->filetime;
			$this->varsLocal['time getHash'][0][] = $this->staticHash;
			$this->varsLocal['url'][0][] = $this->url;
		}
		$this->pinpie->times[] = [microtime(true), 'getContent done'];
		return $this->content;
	}

	protected function draw() {
		if ($this->url !== false) {
			switch ($this->staticType) {
				case 'js':
					return '<script type="text/javascript" src="' . $this->url . '?time=' . $this->staticHash . '"></script>';
				case 'css':
					return '<link rel="stylesheet" type="text/css" href="' . $this->url . '?time=' . $this->staticHash . '">';
				case 'img':
					return '<img src="' . $this->url . '?time=' . $this->staticHash . '"' . (isset($this->dimensions['width']) ? ' width="' . $this->dimensions['width'] . '"' : '') . (isset($this->dimensions['height']) ? ' height="' . $this->dimensions['height'] . '"' : '') . '>';
			}
		}
		if ($this->pinpie->conf->debug) {
			return $this->fulltag;
		}
		return '';
	}

}