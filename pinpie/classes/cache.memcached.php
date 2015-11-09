<?php

namespace PinPIE;
use CFG;

class Cache
{

  private static $mc = null;

  private static function Init() {
    self::$mc = new Memcache();
    foreach (CFG::$pinpie['cache servers'] as $server) {
      self::$mc->addServer($server['host'], $server['port']);
    }
    return true;
  }

  public static function Get($hash) {
    if (is_null(self::$mc)) {
      if (self::Init() == false) {
        return false;
      }
    }
    return self::$mc->get($hash);
  }

  public static function Set($hash, $content) {
    if (is_null(self::$mc)) {
      if (self::Init() == false) {
        return false;
      }
    }
    return self::$mc->set($hash, $content);
  }

}