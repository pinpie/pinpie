<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-09-07
 * Time: 22:21
 */

namespace igordata\PinPIE;


class PinPIE {
  /** @var null|PP */
  public static $pinpie = null;
  public static $tempate = null;
  public static $conf = null;

  public static function newInstance($page = false) {
    try {
      $pinpie = new \igordata\PinPIE\PP();
      static::$pinpie = $pinpie;
      static::$tempate = &static::$pinpie->template;
      static::$conf = &static::$pinpie->conf;
      if ($page) {
        $pinpie->document = $page;
      }
      echo $pinpie->render();
    } catch (NewPageException $np) {
      echo 'New page';
      ob_clean();
      var_dump($np->page);
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