<?php

/**
 * Static content methods
 *
 * Created by PhpStorm.
 * User: Igor
 * Date: 18.09.14
 * Time: 23:05
 */
class StatiCon
{

  private static $static_server_cache = [];
  private static $static_minified_cache = [];
  /** Contains filemtime(file)
   * @var array
   */
  private static $filemtimes = [];

  /**
   * @param $file Путь к файлу на сервере.
   * @param $type Тип содержимого: css, js, img или любая строка.
   * @return bool|string False on failure or url on success.
   */
  public static function getStaticPath($file, $type) {
    if (in_array($type, CFG::$pinpie['minify static filetypes'])) {
      $file = self::getMinified($file, $type);
      if ($file === false) {
        return false;
      }
    } else {
      if (!file_exists(CFG::$pinpie['static folder'] . DS . $file)) {
        //no such file
        return false;
      }
    }

    $filepath = CFG::$pinpie['static folder'] . DS . trim($file, '/\\ ');
    self::checkAndRunGzip($filepath, $type);
    return self::getServer($file) . ($file[0] == '/' ? '' : '/') . $file . '?time=' . md5(CFG::$random_stuff . $file . self::filemtime($filepath));
  }

  public static function getServer($file) {
    if (!$file) {
      return false;
    }
    if (isset(self::$static_server_cache[$file])) {
      return self::$static_server_cache[$file];
    }
    if (empty(CFG::$static_servers)) {
      $url = 'http://' . CFG::$pinpie['site url'];
    } else {
      if (!isset($_COOKIE['pinssshift'])) {
        $_COOKIE['pinssshift'] = mt_rand(0, count(CFG::$static_servers));
        setcookie("pinssshift", $_COOKIE['pinssshift'], time() + 1000000);
      }
      $a = (abs(crc32($file)) % count(CFG::$static_servers) + $_COOKIE['pinssshift']) % count(CFG::$static_servers);
      $url = 'http://' . CFG::$static_servers[$a];
    }
    self::$static_server_cache[$file] = $url;
    return $url;
  }


  private static function checkAndRunGzip($filepath, $type) {
    if (!CFG::$pinpie['gzip static'] OR !in_array($type, CFG::$pinpie['gzip static filetypes'])) {
      return false;
    }
    if (!self::checkMTime($filepath, $filepath . '.gz')) {
      //shell_exec("gzip -" . (int)CFG::$pinpie['gzip static level'] . " -c \"$filepath\" > \"$filepath.gz\" 2>/dev/null");
      gzwrite(gzopen($filepath . '.gz', 'w' . (int)CFG::$pinpie['gzip static level']), file_get_contents($filepath));
    }
    return true;
  }

  private static function checkAndRunMinifier($filepath, $minfilepath, $type) {
    if (CFG::$pinpie['minify static files'] AND CFG::$pinpie['minify static files function'] AND in_array($type, CFG::$pinpie['minify static filetypes'])) {
      $func = CFG::$pinpie['minify static files function'];
      $func($filepath, $minfilepath, $type);
      if (self::checkMTime($filepath, $minfilepath)) {
        return true;
      }
    }
    return false;
  }

  /** Return true if $older is older or equal than $newer.
   * @param $older
   * @param $newer
   * @return bool
   */
  private static function checkMTime($older, $newer) {
    if (self::filemtime($older) !== false AND self::filemtime($newer) !== false AND self::filemtime($older) <= self::filemtime($newer)) {
      return true;
    }
    return false;
  }

  private static function filemtime($file) {
    if (!isset(self::$filemtimes[$file]) OR self::$filemtimes[$file] === false) {
      if (file_exists($file)) {
        self::$filemtimes[$file] = filemtime($file);
      } else {
        self::$filemtimes[$file] = false;
      }
    }
    return self::$filemtimes[$file];
  }

  /**
   * Looks for minified version of the file in the static folder.
   * @param $file string Path to file inside the static folder.
   * @param $type string Type of the file, not extention. Argument is passed to custom CFG::$pinpie['minify static files function'] function, if defined and alowed by CFG::$pinpie['minify static files'].
   * @return bool|string
   */
  private static function getMinified($file, $type) {
    $file = ltrim($file, '/');
    if (!$file) {
      return false;
    }
    if (isset(self::$static_minified_cache[$file])) {
      return self::$static_minified_cache[$file];
    }
    //from here
    $filepath = CFG::$pinpie['static folder'] . DS . $file;
    $pi = pathinfo($file);
    $minfile = $pi['dirname'] . DS . 'min.' . $pi['basename'];
    $minfilepath = CFG::$pinpie['static folder'] . DS . $minfile;
    //to here it takes about 0.000001s - 0.000010s running on weak PC
    if (!file_exists($filepath)) {
      //no such file
      return false;
    }
    //saving original name to cache it
    $f = $file;
    if (CFG::$pinpie['minify static files'] AND self::checkMTime($filepath, $minfilepath)) {
      $useminify = true;
    } else {
      $useminify = self::checkAndRunMinifier($filepath, $minfilepath, $type);
    }
    if ($useminify) {
      $file = $minfile;
    }
    self::$static_minified_cache[$f] = $file;
    return $file;
  }
}