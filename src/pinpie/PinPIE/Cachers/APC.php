<?php

namespace igordata\PinPIE\Cachers;


use igordata\PinPIE\PP;
use igordata\PinPIE\Tags\Tag;

class APC extends Cacher {

  public function get(Tag $tag) {
    $hash = $this->getHash($tag);
    return apc_fetch($hash);
  }

  public function set(Tag $tag, $data, $time = 0) {
    $hash = $this->getHash($tag);
    return apc_store($hash, $data, $time);
  }
  
}