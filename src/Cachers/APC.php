<?php

namespace pinpie\pinpie\Cachers;

use pinpie\pinpie\Tags\Tag;

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