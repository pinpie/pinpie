<?php

namespace pinpie\pinpie\Tags;


class Page extends Snippet {

  protected function getFilePath() {
    $folder = '';
    if (!empty($this->settings['folder'])) {
      $folder = $this->settings['folder'];
    }
    if (empty($this->pinpie->document)) {
      return false;
    }
    $this->filename = $this->pinpie->document;
    return $folder . DIRECTORY_SEPARATOR . trim($this->filename, '\\/');
  }

  public function getOutput() {
    $time_start = microtime(true);
    $this->pinpie->totaltagsprocessed++;
    $this->pinpie->times[]=[microtime(true), 'Tag #' . $this->index . ' ' . $this->tagpath . ' started processing'];
    $this->filename = $this->getFilePath();
    if (!$this->doChecks()) {
      return '';
    }
    $this->content = $this->render();
    $this->time['processing'] = microtime(true) - $time_start;
    $this->pinpie->times[]= [microtime(true),'Tag #' . $this->index . ' ' . $this->tagpath . ' finished processjing'];
    return $this->content;
  }


}