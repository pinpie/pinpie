<?php

namespace pinpie\pinpie\Cachers;

use \pinpie\pinpie\PP;
use \pinpie\pinpie\Tags\Tag;

class APCu extends Cacher {
	/**
	 * @var bool Will be true if APC or APCu functions are available. If APC or APCu is not detected - a message will be logged to default PinPIE log.
	 */
	protected $ok = false;
	/**
	 * @var bool Will be set to true to enable backward compatibility with PHP < 7
	 */
	protected $bc = false;


	public function __construct(PP $pinpie, array $settings = []) {
		parent::__construct($pinpie, $settings);

		if (\function_exists('apcu_fetch')) {
			$this->ok = true;
			$this->bc = false;
		}
		if (\function_exists('apc_fetch')) {
			$this->ok = true;
			$this->bc = true;
		}

		if (!$this->ok) {
			// APS is not installed !
			$pinpie->logit('APC cache error: APC not installed. Check APC cacher class at pinpie/Cachers/APCu.php for more info.');
		}
	}

	public function get(Tag $tag) {
		if (!$this->ok) {
			return false;
		}
		$hash = $this->getHash($tag);
		if ($this->bc) {
			return \apc_fetch($hash);
		} else {
			return \apcu_fetch($hash);
		}
	}

	public function set(Tag $tag, $data, $time = 0) {
		if (!$this->ok) {
			return false;
		}
		$hash = $this->getHash($tag);
		if ($this->bc) {
			return \apc_store($hash, $data, $time);
		} else {
			return \apcu_store($hash, $data, $time);
		}
	}

}