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

  public function __construct(PP $pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag, $priority, $depth) {
    parent::__construct($pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth);

    $this->folder = $this->pinpie->conf->pinpie['chunks folder'];
    $this->folderCheck = $this->pinpie->conf->pinpie['chunks realpath check'];
  }

  protected function getContent() {
    return file_get_contents($this->filename);
  }
}