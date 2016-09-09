<?php

namespace pinpie\pinpie;


class PinPIE {
  /** @var null|PP */
  public static $pinpie = null;
  public static $tempate = null;
  public static $conf = null;

  public static function newInstance($page = false) {
    try {
      $pinpie = new \pinpie\pinpie\PP();
      static::$pinpie = $pinpie;
      static::$tempate = &static::$pinpie->template;
      static::$conf = &static::$pinpie->conf;
      if ($page) {
        $pinpie->document = $page;
      }
      echo $pinpie->render();
    } catch (NewPageException $np) {
      ob_clean();
      static::newInstance($np->page);
    } catch (\Throwable $thr) {
      var_dump($thr);
      echo '<table>' . $thr->xdebug_message . '</table>';
    }
  }

  public static function newPage($page){
    throw new NewPageException($page);
  }
}