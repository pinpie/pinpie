<?php

namespace PinPIE;

use CFG;
use Exception;
use PinPIE;

/**
 * Static content methods
 *
 * Created by PhpStorm.
 * User: Igor
 * Date: 18.09.14
 * Time: 23:05
 */
//namespace PinPIE;

class StatiCon {

  private static $static_server_cache = [];
  private static $static_minified_cache = [];


  public static function createStatic(&$tag) {
    if (!is_array($tag)) {
      return false;
    }
    $static = [
      'type' => false,
      'tag path' => false,
      'url' => false,
      'file path' => false,
      'gzip' => false,
      'gzip level' => CFG::$pinpie['static gzip level'],
      'minifie' => false,
      'minified path' => false,
      'minified url' => false,
      'fulltag' => $tag['fulltag'],
      'time' => false,
      'time hash' => false,
    ];
    $static['type'] = $tag['name'];
    $static['tag path'] = $tag['value'] . (!empty($tag['query']) ? '?' . $tag['query'] : '');
    $static['tag path'] = trim($static['tag path'], '/\\');
    if (empty($static['tag path'])) {
      PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' tag path is empty ' . $tag['value'] . $tag['name']] = microtime(true);
      return false;
    }
    $static['minifie'] = in_array($static['type'], CFG::$pinpie['static minify types']);
    $static['gzip'] = in_array($static['type'], CFG::$pinpie['static gzip types']);
    $static['file path'] = static::getStaticPath($static);
    PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' found static path ' . $static['file path'] . ' for ' . $tag['value'] . $tag['name']] = microtime(true);

    if (!empty($static['file path'])) {
      if ($static['minifie']) {
        $static = array_merge($static, self::getMinified($static));
      }

      if ($static['gzip']) {
        self::checkAndRunGzip($static);
      }

      if (in_array($static['type'], CFG::$pinpie['static dimensions types'])) {
        $d = PinPIE\StatiCon::getDimensions($static);
        if (!empty($d) AND is_array($d)) {
          if (isset($d['type'])) {
            $static['media type'] = $d['type'];
          }
          if (isset($d['size'])) {
            if (isset($d['size'][0])) {
              $static['width'] = $d['size'][0];
            }
            if (isset($d['size'][1])) {
              $static['height'] = $d['size'][1];
            }
          }
          /*          if (!empty($static['width']) AND !empty($static['height'])) {
                      $static['aspect'] = $static['width'] / $static['height'];
                    }*/
        }
      }
      $static['time'] = PinPIE::filemtime($static['file path']);
      $static['time hash'] = md5(CFG::$random_stuff . '*' . $static['file path'] . '*' . $static['time']);
      $static['url'] = PinPIE\StatiCon::getStaticUrl($static);
    }
    return $static;
  }


  /**
   * @param array $static
   * @return bool|string False on failure or url on success.
   */
  public static function getStaticUrl(&$static) {
    if (empty($static) OR !is_array($static)) {
      return false;
    }
    if (!isset(static::$cacheGetStaticPath[$static['file path']])) {
      if ($static['minifie'] AND $static['minified url']) {
        $file = $static['minified url'];
      } else {
        $file = $static['tag path'];
      }
      static::$cacheGetStaticPath[$static['file path']] = self::getServer($static['file path']) . ($file[0] == '/' ? '' : '/') . $file;
    }
    return static::$cacheGetStaticPath[$static['file path']];
  }

  private static $cacheGetStaticPath = [];

  private static function getStaticPath(&$static) {
    if (isset(static::$cacheGetStaticPath[$static['tag path']])) {
      return static::$cacheGetStaticPath[$static['tag path']];
    }
    PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' getStaticPathReal start ' . $static['tag path']] = microtime(true);
    static::$cacheGetStaticPath[$static['tag path']] = static::getStaticPathReal($static);
    PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' getStaticPathReal end ' . $static['tag path']] = microtime(true);
    return static::$cacheGetStaticPath[$static['tag path']];
  }

  private static function getStaticPathReal($static) {
    $path = CFG::$pinpie['static folder'] . DS . $static['tag path'];
    if (CFG::$pinpie['static realpath check']) {
      $path = PinPIE::checkPathIsInFolder($path, CFG::$pinpie['static folder']);
    }
    PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' checkPathIsInFolder start ' . $static['tag path']] = microtime(true);
    if (!file_exists($path)) {
      // no such file
      PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' !file_exists start ' . $static['tag path']] = microtime(true);
      return false;
    }
    PinPIE::$times['#' . PinPIE::$parseStaticCallCounter . ' file_exists start ' . $static['tag path']] = microtime(true);
    return $path;
  }

  public static function getServer($file) {
    if (!$file) {
      return false;
    }
    if (isset(self::$static_server_cache[$file])) {
      return self::$static_server_cache[$file];
    }
    if (empty(CFG::$static_servers)) {
      $url = '//' . CFG::$pinpie['site url'];
    } else {
      $a = abs(crc32($file)) % count(CFG::$static_servers);
      $url = '//' . CFG::$static_servers[$a];
    }
    self::$static_server_cache[$file] = $url;
    return $url;
  }

  private static function checkAndRunGzip($static) {
    $r = false;
    if (!self::checkMTime($static['file path'], $static['file path'] . '.gz')) {
      PinPIE::$times['#gzipping start ' . $static['file path']] = microtime(true);
      if (is_file($static['file path'])) {
        $fp = fopen($static['file path'], 'r');
        if ($fp !== false AND flock($fp, LOCK_EX | LOCK_NB)) {
          PinPIE::$times['gzip start ' . $static['file path']] = microtime(true);
          $gz = gzopen($static['file path'] . '.gz', 'w' . (int)$static['gzip level']);
          if ($gz !== false) {
            gzwrite($gz, fread($fp, filesize($static['file path'])));
            $r = true;
          }
          PinPIE::$times['gzip end ' . $static['file path']] = microtime(true);
          flock($fp, LOCK_UN);
          fclose($fp);
        }
      }
      PinPIE::$times['#gzipping done ' . $static['file path']] = microtime(true);
    }
    return $r;
  }

  private static function checkAndRunMinifier($static) {
    try {
      throwOnFalse($static['minifie']);
      throwOnFalse(CFG::$pinpie['static minify function']);
      $fp = fopen($static['file path'], 'r');
      throwOnFalse($fp);
      /*
       * We can't lock file for writing, external minifiers like Yahoo YUI Compressor or Google Closure Compiler will have no access in that case.
       * Locking file for reading will prevent file from any modifications.
       * So if we will attempt to lock it for writing, we will success if file is not locked for reading in *another* process.
       */
      throwOnFalse(flock($fp, LOCK_SH));
      throwOnFalse(flock($fp, LOCK_EX | LOCK_NB));
      // Switching back to reading lock to make file readable by any external processes
      throwOnFalse(flock($fp, LOCK_SH));
      // Calling user function, where minification is made
      $func = CFG::$pinpie['static minify function'];
      PinPIE::$times['#calling minify func start ' . $static['file path']] = microtime(true);
      $ufuncr = $func($static);
      PinPIE::$times['#calling minify func done ' . $static['file path']] = microtime(true);
      // Releasing lock
      flock($fp, LOCK_UN);
      fclose($fp);
      if (!$ufuncr) {
        PinPIE::$times['#minify func cancels use of min path by returning false ' . $static['file path']] = microtime(true);
        return false;
      }
    } catch (Exception $e) {
      return false;
    }
    return self::checkMTime($static['file path'], $static['minified path']);
  }

  /** Return true if $older is older or equal than $newer.
   * @param $older
   * @param $newer
   * @return bool
   */
  private static function checkMTime($older, $newer) {
    if (PinPIE::filemtime($older) !== false AND PinPIE::filemtime($newer) !== false AND PinPIE::filemtime($older) <= PinPIE::filemtime($newer)) {
      return true;
    }
    return false;
  }


  /**
   * Looks for minified version of the file in the static folder.
   * @param $file string Path to file inside the static folder.
   * @param $type string Type of the file, not extention. Argument is passed to custom CFG::$pinpie['static minify function'] function, if defined and alowed by CFG::$pinpie['static minify'].
   * @return bool|string
   */
  private static function getMinified($static) {
    if (isset(self::$static_minified_cache[$static['file path']])) {
      return self::$static_minified_cache[$static['file path']];
    }
    $pi = pathinfo('/' . trim($static['tag path'], '/\\'));
    $r['minified url'] = trim($pi['dirname'], '/\\') . DS . 'min.' . $pi['basename'];
    $r['minified path'] = CFG::$pinpie['static folder'] . DS . trim($r['minified url'], '/\\');
    if (self::checkMTime($static['file path'], $r['minified path'])) {
      $useminify = true;
    } else {
      $useminify = self::checkAndRunMinifier(array_merge($static, $r));
    }
    if (!$useminify) {
      $r['minified url'] = false;
    }
    self::$static_minified_cache[$static['file path']] = $r;
    return self::$static_minified_cache[$static['file path']];
  }

  private static $cacheGetDimentions = [];

  public static function getDimensions($static) {
    if (!isset(static::$cacheGetDimentions[$static['file path']])) {
      static::$cacheGetDimentions[$static['file path']] = static::measureDimensions($static['file path']);
    }
    return static::$cacheGetDimentions[$static['file path']];
  }

  /**
   * @var PinPIE\FastImage
   */
  private static $dmeter = null;

  /**
   * @param $path
   * @return array|bool
   */
  public static function measureDimensions($path) {
    if (empty($path)) return false;
    $hash = static::getStaticFileHash($path);
    $cached = \PinPIE::cacheGet($hash);
    if ($cached) {
      return $cached;
    }
    if (empty(static::$dmeter)) {
      static::$dmeter = new \PinPIE\FastImage();
    }
    static::$dmeter->load($path);
    $r = [];
    $r['type'] = static::$dmeter->getType();
    $r['size'] = static::$dmeter->getSize();
    \PinPIE::cacheSet($hash, $r);
    return $r;
  }

  public static function getStaticFileHash($path) {
    $base = [];
    $base[] = 'static';
    $base[] = PinPIE::filemtime($path);
    $base[] = $path;
    return hash(CFG::$pinpie['cache hash algo'], implode(':', $base) . CFG::$random_stuff, true);
  }

}