<?php

namespace igordata\PinPIE\Tags;


class Constant extends Tag {
  protected function getContent() {
    return $this->fullname;
  }

  public function getOutput() {
    return $this->render();
  }
}