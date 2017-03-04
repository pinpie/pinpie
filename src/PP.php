<?php

namespace pinpie\pinpie;

use mageekguy\atoum\asserters\boolean;
use pinpie\pinpie\Tags\Tag;


class PP {
	/** @var Config | null */
	public $conf = null;
	/** @var null| URL */
	public $url = null;
	public
		$document = null,
		$template = 'default';
	protected $tags = [];
	public $depth = 0, $totalSnippetsProcessed = 0;
	protected $tagPath = [];
	public $times = [],
		$errors = [];

	public $vars = [];
	/** @var Tag | null */
	public $page = null;

	/** @var \pinpie\pinpie\Cachers\Cacher | null */
	public $cacher = null;

	public $startTime = 0,
		$startMemory = 0;

	/* one pinpie instance cache for other classes */
	public $inCa = [];

	public function __construct($settings = false) {
		$this->startTime = microtime(true);
		$this->startMemory = memory_get_peak_usage();
		$this->times[] = [$this->startTime, 'Starting'];
		if (empty($_SERVER['SERVER_NAME'])) {
			$_SERVER['SERVER_NAME'] = '';
		}
		if (empty($_SERVER['REQUEST_URI'])) {
			$_SERVER['REQUEST_URI'] = '';
		}
		$this->conf = new Config($settings);
		$this->url = $this->getDocument($settings);
		$this->Init();
		$this->times[] = [microtime(true), 'PinPIE started'];
	}

	/**
	 * Decides what page should be loaded. In case of false - 404 code is set and "page not found" configuration entry is used.
	 * @param $settings
	 * @return bool|URL
	 */
	protected function getDocument($settings) {
		$url = $this->getUrlInfo($_SERVER['REQUEST_URI']);
		if (!empty($settings['page'])) {
			$url->file = $settings['page'];
		}
		if ($url->file === false) {
			//requested url not found
			http_response_code(404);
			$url->file = trim($this->conf->pinpie['page not found'], DIRECTORY_SEPARATOR);
		}
		return $url;
	}

	protected function Init() {
		if (!empty($this->conf->tags['PAGE']['class'])) {
			$pageclass = $this->conf->tags['PAGE']['class'];
			$page = new $pageclass($this, $this->conf->tags['PAGE'], 'PAGE ' . $this->url->file, 'PAGE', '', $this->template, 0, '', null, 10000, 0);
			$this->template = &$page->template;
			$this->tags[] = $page;
			$this->page = $page;
		}

		if (!empty($this->conf->pinpie['cache class'])) {
			$this->cacher = new $this->conf->pinpie['cache class']($this, $this->conf->cache);
		} else {
			$this->cacher = new \pinpie\pinpie\Cachers\Disabled($this, $this->conf->cache);
		}
	}


	/**
	 * Looks for corresponding page file by provided url path. Returns URL class instance or false on fail.
	 * @param $url
	 * @return bool|\pinpie\pinpie\URL
	 */
	public function getUrlInfo($url) {
		return new URL($url, $this);
	}


	/**
	 * Check that path is really inside that folder, and return path if yes, and false if not.
	 * @param String $path Path to check
	 * @param String $folder Path to folder, where $path have to be in
	 * @return bool|string False on fail, or $path on success
	 *
	 */
	public function checkPathIsInFolder($path, $folder) {
		if ($path === '' OR $path === null OR $path === false OR $folder === '' OR $folder === null OR $folder === false) {
			/* can't use empty() because it can be a string like "0", and it's valid path */
			return false;
		}
		$this->times[] = [microtime(true), 'checking if  "' . $path . '" belongs to "' . $folder . '"'];
		$path = str_replace('\\', '/', $path);
		$folder = str_replace('\\', '/', $folder);
		$this->times[] = [microtime(true), 'realpath checking ' . $path . ' vs ' . $folder];
		$folderRealpath = realpath($folder);
		$pathRealpath = realpath($path);
		if ($pathRealpath === false OR $folderRealpath === false) {
			// Some of paths is empty
			$this->times[] = [microtime(true), 'one of paths is empty'];
			return false;
		}
		if (strlen(rtrim($pathRealpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) < strlen(rtrim($folderRealpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
			// File path is shorter that a folder path. This file can't be inside that folder.
			$this->times[] = [microtime(true), 'File path is shorter that a folder path. This file can\'t be inside that folder.'];
			return false;
		}
		if (substr($pathRealpath, 0, strlen($folderRealpath)) !== $folderRealpath) {
			// Path to a folder of file is not equal to a path to a folder where it have to be located
			$this->times[] = [microtime(true), 'Path to a folder of file is not equal to a path to a folder where it have to be located'];
			return false;
		}
		// OK
		$this->times[] = [microtime(true), 'Check successful'];
		return $pathRealpath;
	}

	/**
	 * Renders page. Functions ob_start() and ob_get_clean() are involved.
	 * @return string
	 */
	public function render() {
		ob_start();
		if (!empty($this->conf->pinpie['preinclude']) AND file_exists($this->conf->pinpie['preinclude'])) {
			include $this->conf->pinpie['preinclude'];
		}
		echo $this->page->getOutput();
		if (!empty($this->conf->pinpie['postinclude']) AND file_exists($this->conf->pinpie['postinclude'])) {
			include $this->conf->pinpie['postinclude'];
		}
		return ob_get_clean();
	}

	/**
	 * Parses any string and executes found tags. Returns resulting content as string.
	 * @param string $content
	 * @param null $parent (optional) Parent tag can be provided. Rarely used outside PP class.
	 * @return String
	 */
	public function parseString($content, $parent = null) {
		if (empty($parent)) {
			$this->tagPath[] = '/';
		} else {
			$this->tagPath[] = $parent->type . $parent->name;
		}
		$content = preg_replace_callback(/** @lang RegExp */
			'/
        \[
        ([^\[\]]*?)
        \[
        ([!\d]*)
        ([@#$%=]?)
        (?!\*)
        ([^\[\]]+?)
        \]
        ([^\[\]]*?)
        \]    
        (\r\n|\n\r|\r|\n)*
      /xsmuS',
			function ($matches) use ($parent) {
				/* defaults =) to prevent warning on last (enter)* detector */
				$matches += ['', '', '', '', '', '', ''];
				/* creating tag from array of matches */
				$tag = $this->createTag($matches, $parent);
				$tag->index = count($this->tags);
				$this->tags[] = $tag;

				/* render output */
				$tag->output = $tag->getOutput();
				if (!empty($tag->output)) {
					/* if tag output is not empty - add line endings to make tags with line endings just after tag
					have its output have new line chars. And tags without new line chars after tags will be
					replaced only with its output. */
					$tag->output .= $matches[6];
				}

				/* set time for debug */
				$tag->time['end'] = microtime(true);
				$tag->time['total'] = $tag->time['end'] - $tag->time['start'];
				/* return tag output so it will replace tag in the text with its output */
				return $tag->output;
			}
			, $content);
		array_pop($this->tagPath);
		return $content;
	}

	protected function createTag($matches, $parent) {
		/* $matches
		 * Tag with new line after tag
			array (size=8)
				0 => string '[header[!$snippet]template]
			' (length=28) <-- New line
				1 => string 'header' (length=6) <-- placeholder to put tag output in
				2 => string '!' (length=1) <-- cache forever
				3 => string '$' (length=1) <-- it is snippet
				4 => string 'snippet' (length=7) <-- snippet name
				5 => string 'template' (length=8) <-- template
				6 => string '
			' (length=1) <-- New line
		*/
		$fulltag = $matches[0];
		$type = $matches[3];
		$placeholder = ($matches[1] == '' ? false : $matches[1]);
		$template = ($matches[5] == '' ? false : $matches[5]);
		if ($matches[2] === '!') {
			$cachetime = $this->conf->pinpie['cache forever time'];
		} else {
			$cachetime = ($matches[2] == '' ? 0 : (int)$matches[2]);
		}
		$fullname = $matches[4];

		$tagClass = '\pinpie\pinpie\Tags\Tag';
		if (isset($this->conf->tags[$type])) {
			$tagClass = $this->conf->tags[$type]['class'];
		}

		$tagSettings = [];
		if (!empty($this->conf->tags[$type])) {
			$tagSettings = $this->conf->tags[$type];
		}

		$priority = 10000;
		$depth = 0;
		if (!empty($parent)) {
			$priority = $parent->priority;
			$depth = $parent->depth + 1;
		}
		$tag = new $tagClass($this, $tagSettings, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parent, $priority, $depth);

		return $tag;
	}


	public function report() {
		if (!$this->conf->debug) {
			return false;
		}
		ob_start();
		echo '<hr>';
		echo '$times (ms):<br>';
		echo 'Total: ' . number_format((microtime(true) - $this->startTime) * 1000, 2) . "ms<br>";
		$prev = $this->startTime;
		foreach ($this->times as $t) {
			echo number_format(($t[0] - $prev) * 1000, 2) . " : " . $t[1] . "<br>";
			$prev = $t[0];
		}
		echo '<br><br>';
		if (empty($this->errors)) {
			echo "\n\n<br><br>NO ERRORS<br><br>\n\n";
		} else {
			echo 'Errors:<br>';
			var_dump($this->errors);
		}
		echo '<br><br>';
		echo '$tags:<br>';
		echo '<pre>';
		foreach ($this->tags as $tag) {
			if (empty($tag->time)) {
				$tag->time = ['total' => 0];
			}
			echo str_pad($tag->index, 4, ' ', STR_PAD_LEFT) . '  ' .

				str_pad(number_format(round($tag->time['total'] * 1000, 2), 2) . 'ms ', 10, ' ', STR_PAD_LEFT)
				. '  '
				. str_repeat('  ', $tag->depth) . trim($tag->fulltag, " \n\r\t") . "\n";
		}
		echo '</pre><br>';
		return ob_get_clean();
	}


	public function reportTags() {
		if (!$this->conf->debug) {
			return false;
		}
		ob_start();
		echo '<hr>';
		$ignore = ['pinpie',];
		foreach ($this->tags as $tag) {
			/**
			 * @var $tag Tag
			 */
			echo '<h3>#' . $tag->index . ' ' . $tag->fulltag . '</h3>';
			echo '<table>';
			foreach ($tag as $name => $value) {
				if (in_array($name, $ignore)) {
					continue;
				}
				echo '<tr>';
				switch ($name) {
					case 'parent':
						echo '<td>' . $name . '</td><td>' . ($value ? $value->index : 'NONE') . '</td>';
						break;
					case 'hash':
						echo '<td>' . $name . '</td><td>' . $value . '</td>';
						break;
					case 'time':
						echo '<td>' . $name . '</td><td>total: ' . number_format(round($tag->time['total'] * 1000, 2), 2) . 'ms, processing: ' . number_format(round($tag->time['processing'] * 1000, 2), 2) . 'ms</td>';
						break;
					case 'children':
					case 'parents':
						$s = [];
						foreach ($value as $child) {
							$s[] = $child->index;
						}
						echo '<td>' . $name . '</td><td>' . implode(',', $s) . '</td>';
						break;
					default:
						if (is_scalar($value)) {
							echo '<td>' . $name . '</td><td>' . htmlspecialchars($value) . '</td>';
							continue;
						}
						echo '<td>' . $name . '</td><td>' . htmlspecialchars(var_export($value, true)) . '</td>';
				}

				echo "</tr>\n";
			}
			echo '</table>';
		}
		return ob_get_clean();
	}

	public function logit($str = '') {
		if (!empty($this->conf->pinpie['log']['show'])) {
			echo htmlspecialchars($str) . "<br>\n";
		}
		if (!empty($this->conf->pinpie['log']['path'])) {
			file_put_contents($this->conf->pinpie['log']['path'], date('Y.m.d H:i:s') . ' - ' . $str . "\n", FILE_APPEND);
		}
	}

	/**
	 * @var bool|array cache for once calculated by $this->getHashURL() value
	 */
	protected $hashURL = false;

	/**
	 * Do not use this method. Not a part of API. Used by tags for caching purposes only. Never called from outside of PP class.
	 * Creates array with specific URL params to be used in hashing tag functions.
	 * @return bool|array
	 */
	public function getHashURL() {
		if ($this->hashURL !== false) {
			return $this->hashURL;
		}
		$code = http_response_code();
		$defaults = ['ignore url' => false, 'ignore query params' => false];
		$rules = [];
		$ruleID = 'default';
		if (isset($this->conf->pinpie['cache rules'][$code])) {
			$ruleID = $code;
		}
		if (is_array($this->conf->pinpie['cache rules'][$ruleID])) {
			$rules = array_merge($defaults, $this->conf->pinpie['cache rules'][$ruleID]);
		}
		$url = ['path' => $this->url->path, 'query' => $this->url->query];
		//Check, if we have to use 'path' part of url, so caching could be done separately for each page
		if ($rules['ignore url']) {
			$url['path'] = '';
		}
		//Should we ignore all (true) or some (array) of get-params of url, or make it separately? E.g. cache of "?page=3" differs from "?page=100".
		if ($rules['ignore query params'] === true) {
			$url['query'] = '';
		} else {
			if ($rules['ignore query params'] === false OR $rules['ignore query params'] === null) {
				/* prevent foreach warning */
				$rules['ignore query params'] = [];
			}
			parse_str($url['query'], $url['query']);
			foreach ($rules['ignore query params'] as $p) {
				if (isset($url['query'][$p])) {
					unset($url['query'][$p]);
				}
			}
			$url['query'] = http_build_query($url['query']);
		}
		$this->hashURL['url path'] = $url['path'];
		$this->hashURL['url query'] = $url['query'];
		return $this->hashURL;
	}

	protected $filemtimes = [];

	/**
	 * Clone of filemtime(), but caches result in PP class instance.
	 * Prevent reading the modification time of same file multiple times while processing a single request.
	 * @param string $file Path to file.
	 * @return int
	 */
	public function filemtime($file) {
		if (!isset($this->filemtimes[$file])) {
			/* file_exists() prevents warning */
			if (file_exists($file)) {
				$this->filemtimes[$file] = filemtime($file);
			} else {
				$this->filemtimes[$file] = false;
			}
		}
		return $this->filemtimes[$file];
	}

	protected $is_file = [];

	/**
	 * Clone of is_file(), but caches result in PP class instance.
	 * Used to prevent multiple existence checks of same file while processing single request.
	 * @param string $file Path to file
	 * @return bool
	 */
	public function is_file($file) {
		if (!isset($this->is_file[$file])) {
			$this->is_file[$file] = is_file($file);
		}
		return $this->is_file[$file];
	}

}