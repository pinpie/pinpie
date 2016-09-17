<?php

namespace PinPIE;

use \pinpie\pinpie\Cachers\Cacher;
use \pinpie\pinpie\PP;
use \pinpie\pinpie\Tags\Tag;


class Memcache extends Cacher {

  protected
    /** \Memcache|null */
    $mc = null;

  public function __construct(PP $pinpie, array $settings) {
    parent::__construct($pinpie, $settings);
    $defaults['servers'] = [];
    $this->settings = array_merge($defaults, $this->settings);
    $this->mc = new \Memcache();
    foreach ($this->settings['servers'] as $server) {
      $this->mc->addServer($server['host'], $server['port']);
    }
  }

  public function get(Tag $tag) {
    $hash = $this->getHash($tag);
    return $this->mc->get($hash);
  }

  public function set(Tag $tag, $data, $time) {
    $hash = $this->getHash($tag);
    return $this->mc->set($hash, $data, 0, $time);
  }

}