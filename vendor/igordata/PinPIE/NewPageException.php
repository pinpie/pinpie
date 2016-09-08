<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-09-07
 * Time: 22:39
 */

namespace igordata\PinPIE;


class NewPageException extends \Exception {
  public $page = '';
  public function __construct($page, $message='', $code = 0, \Exception $previous = null) {
    $this->page = $page;

    parent::__construct($message, $code, $previous);
  }

}
