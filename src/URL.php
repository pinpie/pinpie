<?php

namespace pinpie\pinpie;


class URL {
	/** @var null|PP */
	public $pinpie = null;
	public $scheme = '';
	public $host = '';
	public $port = '';
	public $user = '';
	public $pass = '';
	public $path = '/';
	public $query = '';
	public $fragment = '';
	public $url = '';
	public $file = false;
	public $found = false;
	public $foundUrl = [];
	public $params = [];
	public $parsed = [];

	function __construct($url, PP $pinpie) {
		$this->pinpie = $pinpie;
		$this->url = $url;
		$url = parse_url($this->url);
		if (isset($url['scheme']))
			$this->scheme = $url['scheme'];
		if (isset($url['host']))
			$this->host = $url['host'];
		if (isset($url['port']))
			$this->port = $url['port'];
		if (isset($url['user']))
			$this->user = $url['user'];
		if (isset($url['pass']))
			$this->pass = $url['pass'];
		if (isset($url['path']))
			$this->path = $url['path'];
		if (isset($url['query']))
			$this->query = $url['query'];
		if (isset($url['fragment']))
			$this->fragment = $url['fragment'];
		/*
		$originalUrl = explode('/', trim($originalUrl, '/'));
		$this->params = array_slice($originalUrl, count($this->path));
*/
		$this->found = $this->parseUrl($this->path);
		$this->params = array_slice(explode('/', trim($this->path, '/')), count($this->foundUrl));
	}


	protected $findPageFileRecur = 0;

	protected function parseUrl($url) {
		$this->findPageFileRecur++;
		if ($this->findPageFileRecur > $this->pinpie->conf->pinpie['route to parent']) {
			return false;
		}
		if (empty($url)) {
			return false;
		}
		/////////////////////////////////////////////////////////
		/* have to create string from url, if it is already exploded */
		if (is_array($url)) {
			$surl = implode(DIRECTORY_SEPARATOR, $url);
		} else {
			$url = trim((string)$url, '/');
			$surl = $url;
			$url = explode('/', $url);
		}
		//if $surl is "ololo/ajaja":
		//First step. Look for "/pages/ololo/ajaja.php".
		$path = $this->pinpie->conf->tags['PAGE']['folder'] . DIRECTORY_SEPARATOR . $surl . '.php';
		if (file_exists($path)) {
			/* file found */
			if ($this->pinpie->conf->tags['PAGE']['realpath check'] AND !$this->pinpie->checkPathIsInFolder($path, $this->pinpie->conf->tags['PAGE']['folder'])) {
				/* if file was found, but had to check realpath and check failed (file is not in dir where it have to be) */
				return false;
			}
			$this->file = $surl . '.php';
			$this->foundUrl = $url;
			return true;
		} else {
			//Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
			$path = $this->pinpie->conf->tags['PAGE']['folder'] . DIRECTORY_SEPARATOR . $surl;
			if (is_dir($path) AND file_exists($path . DIRECTORY_SEPARATOR . 'index.php')) {
				if ($this->pinpie->conf->tags['PAGE']['realpath check'] AND !$this->pinpie->checkPathIsInFolder($path, $this->pinpie->conf->tags['PAGE']['folder'])) {
					return false;
				}
				$this->file = $surl . DIRECTORY_SEPARATOR . 'index.php';
				$this->foundUrl = $url;
				return true;
			} else {
				//Third step. If $this->pinpie->conf->route_to_parent is set greater than zero, will look for nearest parent. Mean "/pages/ololo/ajaja/index.php" if not exist, goes to"/pages/ololo.php" or "/pages/ololo/index.php". (BUT NOT "/pages/index.php" anyway)
				if ($this->pinpie->conf->pinpie['route to parent'] > 0) {
					unset($url[count($url) - 1]);
					return $this->parseUrl($url);
				}
			}
		}
	}
}