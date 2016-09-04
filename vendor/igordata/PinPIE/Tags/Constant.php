<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-27
 * Time: 13:59
 */

namespace igordata\PinPIE\Tags;


class Constant extends Tag {
  protected function getContent() {
    return $this->fullname;
  }

  public function getOutput() {
    return $this->render();
  }
}