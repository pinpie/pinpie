<?php

namespace PinPIE;

use CFG;

class CacherFiles implements Cacher {
  private $path = '';
  private $ok = false;

  private function init() {
    if ($this->ok === true) {
      return true;
    }
    $this->path = CFG::$pinpie['working folder'] . DS . 'filecache' . DS;
    if (!is_dir($this->path)) {
      mkdir($this->path, 0664, true);
    }
    if (touch($this->path . 'test')) {
      $this->ok = true;
    }
    return $this->ok;
  }

  public function get($hash) {
    if (!self::init()) {
      return false;
    }
    $hash = bin2hex($hash);
    $fp = $this->path . $hash;
    if (!file_exists($fp)) {
      return false;
    }
    $content = file_get_contents($fp);
    if ($content === false) {
      return false;
    }
    return unserialize($content);
  }

  public function set($hash, $content, $time = false) {
    if (!self::init()) {
      return false;
    }
    $hash = bin2hex($hash);
    $fp = $this->path . $hash;
    if (!touch($fp)) {
      return false;
    }
    $content = serialize($content);
    return file_put_contents($fp, $content);
  }

}