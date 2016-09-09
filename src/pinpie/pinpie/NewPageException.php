<?php

namespace pinpie\pinpie;


class NewPageException extends \Exception {
  public $page = '';
  public function __construct($page, $message='', $code = 0, \Exception $previous = null) {
    $this->page = $page;

    parent::__construct($message, $code, $previous);
  }

}
