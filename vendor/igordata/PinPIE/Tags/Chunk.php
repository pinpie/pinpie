<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-27
 * Time: 21:32
 */

namespace igordata\PinPIE\Tags;

use \igordata\PinPIE\PP as PP;


class Chunk extends Snippet {
  
  protected function getContent() {
    return file_get_contents($this->filename);
  }
}