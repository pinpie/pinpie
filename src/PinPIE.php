<?php

namespace pinpie\pinpie;

class PinPIE {
  /** @var null|PP */
  public static $pinpie = null;
  /** @var null|Config */
  public static $conf = null;
  public static $url = null,
    $document = null,
    $template = null;


  public static function newInstance($settings = false) {
    try {
      $pinpie = new PP($settings);
      static::$pinpie = &$pinpie;
      static::$conf = &$pinpie->conf;
      static::$url = &$pinpie->url;
      static::$template = &$pinpie->template;
      echo $pinpie->render();
    } catch (NewPageException $np) {
      ob_end_clean();
      $settings['page'] = $np->page;
      static::newInstance($settings);
    }
  }

  public static function newPage($page) {
    throw new NewPageException($page);
  }


  public static function parseString($string) {
    return static::$pinpie->parseString($string);
  }

  public static function report() {
    return static::$pinpie->report();
  }

  public static function reportTags() {
    return static::$pinpie->reportTags();
  }

  public static function varPut($name, $content) {
    return static::$pinpie->vars[$name][100000][] = $content;
  }

  public static function templateGet() {
    return static::$pinpie->template;
  }

  public static function templateSet($template) {
    return static::$pinpie->template = $template;
  }

  public static function cacherGet() {
    return static::$pinpie->cacher;
  }

  public static function cacherSet(Cachers\Cacher $cacher) {
    return static::$pinpie->cacher = $cacher;
  }

  public static function checkPathIsInFolder($path, $folder) {
    return static::$pinpie->checkPathIsInFolder($path, $folder);
  }

  public function getUrlInfo($url) {
    return static::$pinpie->getUrlInfo($url);
  }

  private function __constructor() {

  }
}