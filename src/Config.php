<?php

namespace pinpie\pinpie;

use mageekguy\atoum\asserters\variable;
use mageekguy\atoum\php\tokenizer\iterator\value;

class Config {

	// descriptions are in ReadConf()
	public
		$cache = null,
		$other = null,
		$databases = null,
		/** @var \pinpie\pinpie\PP|null */
		$root = null,
		$pinpie = null,
		$debug = null,
		$tags = [],
		$file = false;

	public function __construct($settings = []) {
		if (empty($settings['root'])) {
			$this->root = rtrim(str_replace('\\', '/', dirname($_SERVER["SCRIPT_FILENAME"])), DIRECTORY_SEPARATOR);
		} else {
			$this->root = $settings['root'];
		}

		$defaults = $this->getDefaults();

		if (!isset($settings['file'])) {
			$settings['file'] = true;
		}
		if ($settings['file'] === false) {
		} else {
			if ($settings['file'] === true) {
				$settings['file'] = $this->root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . basename($_SERVER['SERVER_NAME']) . '.php';
			}
			$settings = $this->settingsMerge($this->readConfFile($settings['file']), $settings);
		}
		$settings = $this->settingsMerge($defaults, $settings);
		$this->cache = $settings['cache'];
		$this->other = $settings['other'];
		$this->databases = $settings['databases'];
		$this->pinpie = $settings['pinpie'];
		$this->debug = $settings['debug'];
		$this->tags = $settings['tags'];
	}

	/**
	 * Merges settings arrays. Minor is overwritten by major.
	 * @param array $minor settings array to be overwritten by $major
	 * @param array $major settings that overwrites thous in $minor
	 * @return array
	 */
	protected function settingsMerge($minor, $major) {
		$settings = $minor;
		foreach ($major as $k => $mj) {

			if (is_array($mj) AND $this->is_assoc($mj) AND isset($settings[$k]) AND is_array($settings[$k])) {
				$settings[$k] = $this->settingsMerge($settings[$k], $mj);
			} else {
				$settings[$k] = $mj;
			}
		}
		return $settings;
	}

	/**
	 * Checks if array is associative
	 * @param $arr array
	 * @return bool
	 */
	public function is_assoc($arr) {
		if (!is_array($arr)) return false;
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public function getDefaults() {
		$cache = []; // Settings for current cacher
		$other = []; // You can put some custom setting here
		$databases = []; // To store database settings
		$debug = false; // Enables PinPIE::report() output. You can use it to enable your own debug mode. Globally available through PinPIE::$conf->debug.

		//Loading defaults
		$pinpie = [
			'cache class' => '\pinpie\pinpie\Cachers\Files',
			'cache forever time' => PHP_INT_MAX,
			'cache rules' => [
				'default' => ['ignore url' => false, 'ignore query params' => []],
				200 => ['ignore url' => false, 'ignore query params' => []],
				404 => ['ignore url' => true, 'ignore query params' => []]
			],
			'codepage' => 'utf-8',
			'index file name' => 'index.php',
			'log' => [
				'path' => false,
				'show' => false,
			],
			'page not found' => 'index.php',
			'postinclude' => $this->root . DIRECTORY_SEPARATOR . 'postinclude.php',
			'preinclude' => $this->root . DIRECTORY_SEPARATOR . 'preinclude.php',
			'route to parent' => 1, //read doc. if exact file not found, instead of 404, PinPIE will try to route request to nearest existing parent entry in url. Default is 1, it means PinPIE will handle "site.com/url" and "site.com/url/" as same page.
			'site url' => $_SERVER['SERVER_NAME'],
			'templates clear vars after use' => false,
			'templates folder' => $this->root . DIRECTORY_SEPARATOR . 'templates',
			'templates function' => false,
			'templates realpath check' => true,
		];

		$tags = [
			'' => [
				'class' => '\pinpie\pinpie\Tags\Chunk',
				'folder' => $this->root . DIRECTORY_SEPARATOR . 'chunks',
				'realpath check' => true,
			],
			'$' => [
				'class' => '\pinpie\pinpie\Tags\Snippet',
				'folder' => $this->root . DIRECTORY_SEPARATOR . 'snippets',
				'realpath check' => true,
			],
			'PAGE' => [
				'class' => '\pinpie\pinpie\Tags\Page',
				'folder' => $this->root . DIRECTORY_SEPARATOR . 'pages',
				'realpath check' => true,
			],
			'%' => [
				'class' => '\pinpie\pinpie\Tags\Staticon',
				'folder' => $this->root,
				'realpath check' => true,
				'gzip level' => 5,
				'gzip types' => ['js', 'css'],
				'minify types' => ['js', 'css'],
				'minify function' => false,
				'dimensions types' => ['img'],
				'dimensions function' => false,
				'draw function' => false,
				'servers' => [], //list here static content servers addresses if you want to use them
			],
			'=' => ['class' => '\pinpie\pinpie\Tags\Constant'],
			'@' => ['class' => '\pinpie\pinpie\Tags\Command'],
		];
		$arr = [];
		$arr['cache'] = $cache;
		$arr['other'] = $other; //you can use that array to store settings for your own scripts
		$arr['databases'] = $databases;
		$arr['pinpie'] = $pinpie;
		$arr['debug'] = $debug;
		$arr['tags'] = $tags;
		return $arr;
	}

	/**
	 * Internal method to read configuration file.
	 */
	protected function readConfFile($path) {
		$arr = $this->getDefaults();
		$cache = $arr['cache'];
		$other = $arr['other'];
		$databases = $arr['databases'];
		$pinpie = $arr['pinpie'];
		$debug = $arr['debug'];
		$tags = $arr['tags'];
		unset($arr);
		//Reading file and overwriting defaults
		if (file_exists($path)) {
			$this->file = $path;
			include($path);
		}
		$arr = [];
		$arr['cache'] = $cache;
		$arr['other'] = $other;
		$arr['databases'] = $databases;
		$arr['pinpie'] = $pinpie;
		$arr['debug'] = $debug;
		$arr['tags'] = $tags;
		return $arr;
	}
}



