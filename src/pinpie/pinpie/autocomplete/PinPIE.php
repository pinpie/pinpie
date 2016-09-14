<?php

/** Dummy class in global namespace allows to have IDE autocomplete while using class_alias() */

class PinPIE {
  /** @var null|pinpie\pinpie\PP */
  public static $pinpie = null;
  public static $template = null;
  /** @var null|pinpie\pinpie\CFG */
  public static $conf = null;
  public static $document = null;

  public static function newInstance($page = false) {
  }

  public static function newPage($page) {
    throw new pinpie\pinpie\NewPageException($page);
  }


  public static function parseString($string) {
  }

  public static function report() {
  }

  public static function varPut($name, $content) {
  }

  public static function templateGet() {
  }

  public static function templateSet($template) {
  }

  public static function cacherGet() {
  }

  public static function cacherSet(pinpie\pinpie\Cachers\Cacher $cacher) {
  }

  public static function checkPathIsInFolder($path, $folder) {
  }
}