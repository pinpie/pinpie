<?php

namespace pinpie\pinpie;


class URL {
	/** @var null|PP */
	public $pinpie = null;
	public $scheme = '';
	public $host = '';
	public $port = '';
	/**
	 * user is the user part of parse_url() returned result.
	 * @var string
	 */
	public $user = '';
	public $pass = '';
	public $path = '/';
	public $query = '';
	public $fragment = '';
	/**
	 * Equals to the first param in constructor.
	 * @var string
	 */
	public $url = '';
	public $file = false;
	/**
	 * True if page file was found. False if not.
	 * @var bool
	 */
	public $found = false;
	/**
	 * Part of url for which a file was found.
	 * Exploded to array by slash.
	 * @var array
	 */
	public $foundUrl = [];
	/**
	 * Part of url after found part and exploded by slash.
	 * @var array
	 */
	public $params = [];
	public $parsed = [];

	function __construct($url, PP $pinpie) {
		$this->pinpie = $pinpie;
		$this->url = $url;
		$this->parseUrl();
		$this->found = $this->findFile($this->path);
		/* when exploding empty string explode() returns array with one empty string in it - ['']
		to prevent this have to check and do not explode empty strings */
		$tpath = trim($this->path, '/');
		if ($tpath !== '') {
			$this->params = array_slice(explode('/', $tpath), count($this->foundUrl));
		}
	}

	protected function parseUrl() {
		$this->parsed = parse_url($this->url);
		if (isset($this->parsed['scheme']))
			$this->scheme = $this->parsed['scheme'];
		if (isset($this->parsed['host']))
			$this->host = $this->parsed['host'];
		if (isset($this->parsed['port']))
			$this->port = $this->parsed['port'];
		if (isset($this->parsed['user']))
			$this->user = $this->parsed['user'];
		if (isset($this->parsed['pass']))
			$this->pass = $this->parsed['pass'];
		if (isset($this->parsed['path']))
			$this->path = $this->parsed['path'];
		if (isset($this->parsed['query']))
			$this->query = $this->parsed['query'];
		if (isset($this->parsed['fragment']))
			$this->fragment = $this->parsed['fragment'];
	}

	protected $findPageFileRecur = 0;

	protected function findFile($url) {
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
			if ($url === "") {
				$url = [];
			} else {
				$url = explode('/', $url);
			}
		}

		//if $surl is "ololo/ajaja":
		//First step. Look for "/pages/ololo/ajaja.php".
		$filename = $surl . '.php';
		if ($this->checkFile($filename)) {
			$this->file = trim($filename, '\\/');
			$this->foundUrl = $url;
			return true;
		}

		//Second step. If it is directory, look for "/pages/ololo/ajaja/index.php".
		$filename = $surl . DIRECTORY_SEPARATOR . $this->pinpie->conf->pinpie['index file name'];
		if ($this->checkFile($filename)) {
			$this->file = trim($filename, '\\/');
			$this->foundUrl = $url;
			return true;
		}

		//Third step. If $this->pinpie->conf->route_to_parent is set greater than zero, will look for nearest parent. Mean "/pages/ololo/ajaja/index.php" if not exist, goes to"/pages/ololo.php" or "/pages/ololo/index.php". (BUT NOT "/pages/index.php" anyway)
		if ($this->pinpie->conf->pinpie['route to parent'] > 0) {
			unset($url[count($url) - 1]);
			return $this->findFile($url);
		}

		return false;
	}

	protected function checkFile($filename) {
		$path = $this->pinpie->conf->tags['PAGE']['folder'] . DIRECTORY_SEPARATOR . $filename;
		if (!file_exists($path)) {
			return false;
		}
		/* file found */
		if (
			$this->pinpie->conf->tags['PAGE']['realpath check']
			AND !$this->pinpie->checkPathIsInFolder($path, $this->pinpie->conf->tags['PAGE']['folder'])
		) {
			/* if file was found, but had to check realpath and check failed (file is not in dir where it have to be) */
			return false;
		}
		return true;
	}

}