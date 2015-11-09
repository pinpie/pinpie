<?php

namespace PinPIE;
use CFG;

class Cache
{
  private static $path = '';
  private static $ok = false;

  public static function Init() {
    if (self::$ok === true) {
      return true;
    }
    self::$path = CFG::$pinpie['working folder'] . DS . 'filecache' . DS;
    if (!is_dir(self::$path)) {
      mkdir(self::$path, 0664, true);
    }
    if (touch(self::$path . 'test')) {
      self::$ok = true;
    }
    return self::$ok;
  }

  public static function Get($hash) {
    if (!self::Init()) {
      return false;
    }
    $hash = bin2hex($hash);
    $fp = self::$path . $hash;
    if (!file_exists($fp)) {
      return false;
    }
    $content = file_get_contents($fp);
    if ($content === false) {
      return false;
    }
    return unserialize($content);
  }

  public static function Set($hash, $content) {
    if (!self::Init()) {
      return false;
    }
    $hash = bin2hex($hash);
    $fp = self::$path . $hash;
    if (!touch($fp)) {
      return false;
    }
    $content = serialize($content);
    return file_put_contents($fp, $content);
  }

}

Cache::Init();