<?php

namespace PinPIE;

use CFG;

class CacherAPC implements Cacher {

  public function get($hash) {
    return apc_fetch($hash);
  }

  public function set($hash, $content, $time) {
    return apc_store($hash, $content);
  }

}