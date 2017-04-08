<?php

namespace pinpie\pinpie;

class PinPIE {
	/** @var null|PP */
	public static $pp = null;
	/** @var null|Config */
	public static $conf = null;
	/** @var null|URL */
	public static $url = null,
		$document = null,
		$template = null;

	/**
	 * Return current cacher object. It have get() and set() methods available.
	 * @return null|Cachers\Cacher
	 */
	public static function cacherGet() {
		return static::$pp->cacher;
	}

	/**
	 * Sets cacher to be used by PinPIE.
	 * @param Cachers\Cacher $cacher
	 * @return Cachers\Cacher
	 */
	public static function cacherSet(Cachers\Cacher $cacher) {
		return static::$pp->cacher = $cacher;
	}

	/**
	 * Check that path is really inside that folder, and return path if yes, and false if not.
	 * @param String $path Path to check
	 * @param String $folder Path to folder, where $path have to be in
	 * @return bool|string False on fail, or $path on success
	 */
	public static function checkPathIsInFolder($path, $folder) {
		return static::$pp->checkPathIsInFolder($path, $folder);
	}

	/**
	 * Looks for corresponding page file by provided url path. Returns URL class instance or false on fail.
	 * @param String $url
	 * @return bool|URL
	 */
	public function getUrlInfo($url) {
		return static::$pp->getUrlInfo($url);
	}

	/**
	 * Creates new instance of PP class - the main class of PinPIE and do all the work to draw the page.
	 * @param bool|array $settings PinPIE settings could be passed as array. If false or empty array - PinPIE will look for file in /config folder.
	 */
	public static function renderPage($settings = false) {
		try {
			$pp = new PP($settings);
			static::$pp = &$pp;
			static::$conf = &$pp->conf;
			static::$url = &$pp->url;
			static::$template = &$pp->template;
			echo $pp->render();
		} catch (NewPageException $np) {
			ob_end_clean();
			$settings['page'] = $np->page;
			static::renderPage($settings);
		}
	}

	/**
	 * Makes PinPIE to stop the work, recreate main instance and load $page file.
	 * Throws exception, which is handled in PinPIE::renderPage() method.
	 * @param String $page
	 * @throws NewPageException
	 */
	public static function newPage($page) {
		throw new NewPageException($page);
	}

	/**
	 * Parses any string and executes found tags. Returns resulting content as string.
	 * @param string $string
	 * @return String
	 */
	public static function parseString($string) {
		return static::$pp->parseString($string);
	}

	/**
	 * Outputs some debug info.
	 * To be used for debug purposed. Set $debug = true in config to enable debug output. By default will do nothing and return false.
	 * @return string
	 */
	public static function report() {
		return static::$pp->report();
	}

	/**
	 * Dumps tags and their params.
	 * To be used for debug purposed. Set $debug = true in config to enable debug output. By default will do nothing and return false.
	 * @return string
	 */
	public static function reportTags() {
		return static::$pp->reportTags();
	}

	/**
	 * Returns current template.
	 * @return string
	 */
	public static function templateGet() {
		return static::$pp->template;
	}

	/**
	 * Sets current template.
	 * @param String $template
	 * @return mixed
	 */
	public static function templateSet($template) {
		return static::$pp->template = $template;
	}

	/**
	 * Puts string into the placeholder.
	 * @param string $name
	 * @param string $content
	 */
	public static function varPut($name, $content) {
		static::$pp->vars[$name][100000][] = $content;
	}

	/**
	 * Replaces placeholder content.
	 * @param string $name
	 * @param string $content
	 */
	public static function varReplace($name, $content) {
		static::$pp->vars[$name] = [
			100000 => [$content]
		];
	}


}