<?php

namespace PinPIE;

class CacherDisabled implements Cacher {

  public function get($hash) {
    return false;
  }

  public function set($hash, $content, $time) {
    return true;
  }

}
