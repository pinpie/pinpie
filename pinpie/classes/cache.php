<?php

namespace PinPIE;
use CFG;
class Cache {
  /**
   * @var null|\PinPIE\Cacher
   */
  private $cacher = null;

  private $cache = [];
  public function get($hash) {
    if (!$this->init()) {
      return false;
    }
    if (!isset($this->cache[$hash])){
      $this->cache[$hash] = $this->cacher->get($hash);
    }
    return $this->cache[$hash];
  }

  public function set($hash, $content, $time) {
    if (!$this->init()) {
      return false;
    }
    $this->cache[$hash] = $content;
    return $this->cacher->set($hash, $content, $time);
  }

  public function injectCacher(\PinPIE\Cacher $cacher) {
    $this->cacher = $cacher;
    return true;
  }

  private function init() {
    if (empty($this->cacher)) {
      // trying to load some cacher
      switch (CFG::$pinpie['cache type']) {
        case 'files':
          include_once ROOT . '/pinpie/classes/cachers/files.php';
          $this->cacher = new CacherFiles();
          break;
        case 'memcached':
          include_once ROOT . '/pinpie/classes/cachers/memcached.php';
          $this->cacher = new CacherMemcached();
          break;
        case 'apc':
          include_once ROOT . '/pinpie/classes/cachers/apc.php';
          $this->cacher = new CacherAPC();
          break;
        case 'custom':
        case 'disabled':
          include_once ROOT . '/pinpie/classes/cachers/disabled.php';
          $this->cacher = new CacherDisabled();
          break;
        default:
          return false;
      }
      return true;
    }
    return true;
  }
}
