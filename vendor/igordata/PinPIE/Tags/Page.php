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


  protected function getFilePath() {
    $folder = '';
    if (!empty($this->settings['folder'])) {
      $folder = $this->settings['folder'];
    }
    return $folder . DIRECTORY_SEPARATOR . trim($this->filename, '\\/');
  }

  protected function getContent() {
    return $this->fileExecute($this->filename, $this->params);
  }

  public function getOutput() {
    $time_start = microtime(true);
    $this->pinpie->totaltagsprocessed++;
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' started processing'] = microtime(true);
    $this->filename = $this->getFilePath();
    $this->content = $this->render();
    $this->time['processing'] = microtime(true) - $time_start;
    $this->pinpie->times['Tag #' . $this->index . ' ' . $this->tagpath . ' finished processing'] = microtime(true);
    return $this->content;
  }


}