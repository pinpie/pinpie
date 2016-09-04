<?php
/**
 * Created by PhpStorm.
 * User: igors
 * Date: 2016-08-25
 * Time: 23:45
 */

namespace igordata\PinPIE\Tags;
use \igordata\PinPIE\PP as PP;


class Page extends Snippet {

  public function __construct(PP $pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, Tag $parentTag=null, $priority, $depth) {
    parent::__construct($pinpie, $fulltag, $type, $placeholder, $template, $cachetime, $fullname, $parentTag, $priority, $depth);
    $this->folder = $this->pinpie->conf->pinpie['pages folder'];
    $this->folderCheck = $this->pinpie->conf->pinpie['pages realpath check'];
  }

  protected function getContent() {
    return $this->fileExecute($this->filename, $this->params);
  }

  public function getOutput() {
    $time_start = microtime(true);
    $this->pinpie->totaltagsprocessed++;
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' started processing'] = microtime(true);
    $this->content = $this->render();
    $this->time['processing'] = microtime(true) - $time_start;
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' finished processing'] = microtime(true);
    return $this->content;
  }


}