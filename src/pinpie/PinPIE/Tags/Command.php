<?php

namespace igordata\PinPIE\Tags;


class Command extends Tag {

  public function getOutput() {
    $r = false;
    switch ($this->name) {
      case 'template':
        $this->pinpie->page->template = $this->value;
        $this->pinpie->page->templateParams = $this->params;
        $r = 'Template set to ' . $this->pinpie->page->template;
        break;
      default :
        $err = 'Unknown command skipped. tag:' . $this->fulltag . ' in ' . $this->tagpath;
        $this->error($err);
        $this->pinpie->logit($err);
    }
    $this->content = $r;
    return '';
  }
}