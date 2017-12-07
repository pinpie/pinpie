<?php
namespace pinpie\pinpie\Cachers;

use \pinpie\pinpie\PP;
use \pinpie\pinpie\Tags\Tag;

class Cacher {

  protected
    $pinpie = null,
    $settings = [];

  public function __construct(PP $pinpie, $settings = []) {
    $this->pinpie = $pinpie;
    $defaults = [];
    $defaults['algo'] = 'sha1';
    $defaults['random stuff'] = '';
    $defaults['raw hash'] = false;
    $this->settings = array_merge($defaults, $settings);
  }

  public function get(Tag $tag) {
    return false;
  }

  public function set(Tag $tag, $data, $time = 0) {
    return true;
  }

  public function getHash(Tag $tag) {
    if (!empty($tag->hash)) {
      return $tag->hash;
    }
    $tag->hashBase = $this->hashBase($tag);
    $tag->hash = hash($this->settings['algo'], implode("\n", $tag->hashBase) . $this->settings['random stuff'], $this->settings['raw hash']);
    return $tag->hash;
  }

  public function hashBase(Tag $tag) {
    $base = [];
    $base[] = $_SERVER['SERVER_NAME'];
    $url = $this->pinpie->getHashURL();
    $base[] = $url['url path'];
    $base[] = $url['url query'];
    $base[] = $tag->tagpath;
    $base[] = $tag->fulltag;
    $base[] = $tag->childIndex;
    $base[] = $tag->filetime;
    return $base;
  }

}