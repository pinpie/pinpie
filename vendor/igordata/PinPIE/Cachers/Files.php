<?php
namespace igordata\PinPIE\Cachers;


use \igordata\PinPIE\Cachers\Cacher;
use \igordata\PinPIE\PP;
use \igordata\PinPIE\Tags\Tag;


class Files extends Cacher {

  protected
    $path = '',
    $ok = false;

  public function __construct(PP $pinpie, array $settings = []) {
    parent::__construct($pinpie, $settings);
    
    $defaults = [];
    $defaults['path'] = $this->pinpie->root . DIRECTORY_SEPARATOR . 'filecache';
    $this->settings = array_merge($defaults, $this->settings);
    $this->path = rtrim($this->settings['path'], '\\/') . DIRECTORY_SEPARATOR;
    if (!is_dir($this->path)) {
      mkdir($this->path, 0600, true);
    }
    if (touch($this->path . 'test')) {
      $this->ok = true;
    }
  }

  public function get(Tag $tag) {
    if (!$this->ok) {
      return false;
    }
    $hash = $this->getHash($tag);
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

  public function set(Tag $tag, $data, $time = false) {
    if (!$this->ok) {
      return false;
    }
    $hash = $this->getHash($tag);
    $fp = $this->path . $hash;
    if (!touch($fp)) {
      return false;
    }
    $data = serialize($data);
    return file_put_contents($fp, $data);
  }

}