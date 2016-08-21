<?php

namespace PinPIE;


class CacherMemcached implements Cacher {

  private $mc = null;

  private function init() {
    $this->mc = new \Memcache();
    foreach (PP::$c->pinpie['cache servers'] as $server) {
      $this->mc->addServer($server['host'], $server['port']);
    }
    return true;
  }

  public function get($hash) {
    if (is_null($this->mc)) {
      if ($this->init() == false) {
        return false;
      }
    }
    return $this->mc->get(bin2hex($hash));
  }

  public function set($hash, $content, $time) {
    if (is_null($this->mc)) {
      if ($this->init() == false) {
        return false;
      }
    }
    return $this->mc->set(bin2hex($hash), $content, 0, $time);
  }

}