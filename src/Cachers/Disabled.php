<?php

namespace pinpie\pinpie\Cachers;

use pinpie\pinpie\Tags\Tag;


class Disabled extends Cacher {
  public function get(Tag $tag) {
    return false;
  }

  public function set(Tag $tag, $data, $time = 0) {
    return true;
  }
}
