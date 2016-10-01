<?php

namespace pinpie\pinpie\Tags;

use \pinpie\pinpie\PP as PP;


class Staticon extends Tag {
	private
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

	private $c = [];

	public function __construct(PP $pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag = null, $priority = 10000, $depth = 0) {
		parent::__construct($pinpie, $settings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth);
		$this->pinpie->times[] = [microtime(true), 'static ' . $fulltag . ' construct started'];
		if (!isset($this->pinpie->inCa['static'])) {
			$this->pinpie->inCa['static'] = [];
		}
		$this->c = &$this->pinpie->inCa['static'];


		$this->staticType = $this->name;
		$this->staticPath = $this->value . (!empty($this->params) ? '?' . implode('&', $this->params) : '');

		if (empty($this->staticPath)) {
			$this->error($fulltag . ' static file path is empty');
		} else {
			if ($this->staticPath{0} !== '/') {
				$this->staticPath = rtrim($this->pinpie->url['path'], '/') . '/' . $this->staticPath;
			}
		}

		$this->minifie = in_array($this->staticType, $this->settings['minify types']);
		$this->gzip = in_array($this->staticType, $this->settings['gzip types']);
		$this->pinpie->times[] = [microtime(true), '$this->filename = $this->getStaticPath();'];
		$this->filename = $this->getStaticPath();
		$this->pinpie->times[] = [microtime(true), '$this->filename = $this->getStaticPath(); done'];

		if (empty($this->filename)) {
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
			$this->staticHash = md5($this->filename . '*' . $this->filetime);
		}
		$this->pinpie->times[] = [microtime(true), 'getStaticUrl'];
		$this->url = $this->getStaticUrl();
		$this->pinpie->times[] = [microtime(true), 'getStaticUrl done'];
	}

	public function getStaticUrl() {
		if (!isset($this->c['getStaticPath'])) {
			$this->c['getStaticPath'] = [];
		}
		if (!isset($this->c['getStaticPath'][$this->filename])) {
			if ($this->minifie AND $this->minifiedURL) {
				$file = $this->minifiedURL;
			} else {
				$file = $this->staticPath;
			}
			$this->c['getStaticPath'][$this->filename] = $this->getServer() . ($file[0] == '/' ? '' : '/') . $file;
		}
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
		if ($this->settings['realpath check']) {
			$this->pinpie->times[] = [microtime(true), 'realpath check required'];
			$path = $this->pinpie->checkPathIsInFolder($path, $this->settings['folder']);
			$this->pinpie->times[] = [microtime(true), 'realpath check done'];
		} else {
			$this->pinpie->times[] = [microtime(true), 'realpath check skipped'];
		}
		if (empty($path) OR !file_exists($path)) {
			$this->pinpie->times[] = [microtime(true), 'file not found'];
			// no such file
			return false;
		}
		$this->pinpie->times[] = [microtime(true), 'getStaticPathReal done'];
		return $path;
	}

	public function getServer() {
		if (!$this->filename) {
			return false;
		}
		if (!isset($this->c['getServer'])) {
			$this->c['getServer'] = [];
		}
		if (isset($this->c['getServer'][$this->filename])) {
			return $this->c['getServer'][$this->filename];
		}
		if (empty($this->pinpie->conf->static_servers)) {
			$this->url = '//' . $this->pinpie->conf->pinpie['site url'];
		} else {
			$a = abs(crc32($this->filename)) % count($this->pinpie->conf->static_servers);
			$this->url = '//' . $this->pinpie->conf->static_servers[$a];
		}
		$this->c['getServer'][$this->filename] = $this->url;
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

		/*
	 * We can't lock file for writing, external minifiers like Yahoo YUI Compressor or Google Closure Compiler will have no access in that case.
	 * Locking file for reading will prevent file from any modifications.
	 * So if we will attempt to lock it for writing, we will success if file is not locked for reading in *another* process.
	 */
		if (flock($fp, LOCK_SH) === false) {
			return false;
		}
		if (flock($fp, LOCK_EX | LOCK_NB) === false) {
			return false;
		}
		// Switching back to reading lock to make file readable by any external processes
		if (flock($fp, LOCK_SH) === false) {
			return false;
		}
		// Calling user function, where minification is made
		$func = $this->settings['minify function'];
		$ufuncr = $func($this);
		// Releasing lock
		flock($fp, LOCK_UN);
		fclose($fp);
		if (!$ufuncr) {
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
		if ($this->pinpie->filemtime($older) !== false AND $this->pinpie->filemtime($newer) !== false AND $this->pinpie->filemtime($older) <= $this->pinpie->filemtime($newer)) {
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
		}
		$pi = pathinfo('/' . trim($this->staticPath, '/\\'));
		$this->minifiedURL = trim($pi['dirname'], '/\\') . DIRECTORY_SEPARATOR . 'min.' . $pi['basename'];
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
			$this->varsLocal['content'][0][] = $this->content;
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