<?php

namespace pinpie\pinpie;


use pinpie\pinpie\Cachers\Cacher;

class PinPIE {
  /** @var null|PP */
  public static $pinpie = null;
  public static $template = null;
  public static $conf = null;
  public static $document = null;

  public static function newInstance($page = false) {
    try {
      $pinpie = new \pinpie\pinpie\PP();
      static::$pinpie = &$pinpie;
      static::$template = &$pinpie->template;
      static::$conf = &$pinpie->conf;
      static::$document = &$pinpie->document;
      if ($page) {
        $pinpie->document = $page;
      }
      echo $pinpie->render();
    } catch (NewPageException $np) {
      ob_clean();
      static::newInstance($np->page);
    }
  }

  public static function newPage($page) {
    throw new NewPageException($page);
  }


  public static function parseString($string) {
    static::$pinpie->parseTags($string);
  }

  public static function report() {
    static::$pinpie->report();
  }

  public static function reportTags() {
    static::$pinpie->reportTags();
  }

  public static function varPut($name, $content) {
    static::$pinpie->vars[$name][100000][] = $content;
  }

  public static function templateGet() {
    return static::$pinpie->template;
  }

  public static function templateSet($template) {
    static::$pinpie->template = $template;
  }

  public static function cacherGet() {
    return static::$pinpie->cacher;
  }

  public static function cacherSet(Cacher $cacher) {
    static::$pinpie->cacher = $cacher;
  }

  public static function checkPathIsInFolder($path, $folder) {
    return static::$pinpie->checkPathIsInFolder($path, $folder);
  }

  private function __constructor() {

  }
}