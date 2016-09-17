<?php

namespace pinpie\pinpie;


use pinpie\pinpie\Cachers\Cacher;

class PinPIE {
  /** @var null|PP */
  public static $pinpie = null;
  /** @var null|pinpie\pinpie\CFG */
  public static $conf = null;
  public static $url = null,
    $document = null,
    $template = null;


  public static function newInstance($page = false) {
    try {
      $pinpie = new \pinpie\pinpie\PP();
      static::$pinpie = &$pinpie;
      static::$conf = &$pinpie->conf;
      static::$url = &$pinpie->url;
      static::$document = &$pinpie->document;
      static::$template = &$pinpie->template;
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

  public function findPageFile($url) {
    return static::$pinpie->findPageFile($url);
  }

  private function __constructor() {

  }
}