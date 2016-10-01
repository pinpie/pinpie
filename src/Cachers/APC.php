<?php

namespace pinpie\pinpie\Cachers;

use \pinpie\pinpie\PP;
use \pinpie\pinpie\Tags\Tag;

class APC extends Cacher {
  protected $bc = false;

  public function __construct(PP $pinpie, array $settings) {
    if (function_exists('apc_fetch')) {
      $this->bc = true;
    }
    parent::__construct($pinpie, $settings);
  }

  public function get(Tag $tag) {
    $hash = $this->getHash($tag);
    if ($this->bc) {
      return apc_fetch($hash);
    } else {
      return apcu_fetch($hash);
    }
  }

  public function set(Tag $tag, $data, $time = 0) {
    $hash = $this->getHash($tag);
    if ($this->bc) {
      return apc_store($hash, $data, $time);
    } else {
      return apcu_store($hash, $data, $time);
    }

  }

}