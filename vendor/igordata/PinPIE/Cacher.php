<?php
namespace igordata\PinPIE;

interface Cacher {
  public function get($hash);

  public function set($hash, $content, $time);
}